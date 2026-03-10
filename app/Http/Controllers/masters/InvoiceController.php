<?php

namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceRequest;
use App\Models\masters\Invoice;
use App\Models\masters\InvoiceItem;
use App\Models\masters\Currency;
use App\Models\masters\InvoiceTaxSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rules\In;
use Spatie\Browsershot\Browsershot;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->query('group_id');

        if (! $groupId) {
            return redirect()->back()->with([
                'error' => 'グループIDが指定されていません。',
                'alert-type' => 'danger'
            ]);
        }

        // if (! auth()->user()->canAccessGroup($groupId)) {
        //     abort(403, 'アクセス権限がありません。');
        // }

        $query = Invoice::where('group_id', $groupId);

        if ($request->filled('search')){
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%");
        }

        if ($request->filled('billing_title')) {
            $query->where('billing_title', 'like', "%{$request->billing_title}%");
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20);
        $invoices->appends(array_filter($request->only(['search', 'billing_title', 'group_id'])));

        return view('masters.invoices.index', compact('invoices', 'groupId'));
    }

    public function create(Request $request)
    {
        $groupId = $request->query('group_id');

        // if (! $groupId || ! auth()->user()->canAccessGroup($groupId)) {
        //     abort(403);
        // }
        $currencies = Currency::select('currency_code', 'id')->distinct()->orderBy('currency_code')->get(); 
        return view('masters.invoices.create', compact('groupId','currencies'));
    }

public function store(Request $request)
{
    $validated = $request->validate([
        'group_id' => 'nullable|integer',
        'billing_title' => 'nullable|string|max:200',
        'tax_mode' => 'required|in:1,2', // 1=税込, 2=税別
        'language' => 'required|in:1,2', // 1=日语, 2=英语
        'currency_code' => 'required|string|max:50',
        'invoice_date' => 'required|date',
        'due_date' => 'required|date|after_or_equal:invoice_date',
        'notes' => 'nullable|string|max:65535',
        'items' => 'required|array|min:1',
        'items.*.description' => 'required|string|max:300',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.tax_rate' => 'required|numeric|in:0,8,10',
        'items.*.display_order' => 'required|integer|min:1',
    ], [
        'items.required' => '明細は最低1行必要です。',
        'due_date.after_or_equal' => '支払期日は請求日以降にしてください。',
        'items.*.tax_rate.in' => '税率は 0（非課税）、8、10 のいずれかにしてください。',
    ]);

    // 重置索引，避免跳号
    $validated['items'] = array_values($validated['items']);

    //查询$validated['invoice_date']在currencies表中rate_valid_from之后，在rate_valid_to之前的数据
    $currency = Currency::where('currency_code', $validated['currency_code']) // 通常还需要匹配币种代码
    ->where('rate_valid_from', '<=', $validated['invoice_date'])
    ->where('rate_valid_to', '>=', $validated['invoice_date'])
    ->orderBy('rate_valid_from', 'desc') // 如果有重叠，取最新生效的
    ->first();
    if (!$currency) {
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => '指定の通貨に対する指定の日付の為替レートが見つかりません。']);
    }

    DB::beginTransaction();
    try {
        // === 第一步：按税率分组原始输入金额（不分舍入）===
        $groups = [];
        foreach ($validated['items'] as $item) {
            $quantity = (float)$item['quantity'];
            $unitPrice = (float)$item['unit_price'];
            $taxRate = (float)$item['tax_rate'];
            $rateKey = number_format($taxRate, 2, '.', '');

            if (!isset($groups[$rateKey])) {
                $groups[$rateKey] = [
                    'tax_rate' => $taxRate,
                    'total_input_raw' => 0, // 原始 quantity * unit_price 总和（未取整）
                ];
            }
            $groups[$rateKey]['total_input_raw'] += $quantity * $unitPrice;
        }

        // === 第二步：对每组统一计算不含税 & 税额 ===
        $subtotalAmount = 0;
        $totalTaxAmount = 0;
        $taxGroups = []; // 用于 invoice_tax_summary

        foreach ($groups as $rateKey => $group) {
            $taxRate = $group['tax_rate'];
            $totalInputRaw = $group['total_input_raw'];

            if ($validated['tax_mode'] == 1) {
                // ========== 税込モード：totalInputRaw 是含税总额 ==========
                $totalIncl = round($totalInputRaw); // 先取整为整数円
                if ($taxRate > 0) {
                    $baseAmount = $totalIncl / (1 + $taxRate / 100);
                    $totalExcl = (int)ceil($baseAmount); // ✅ 向上取整
                    $taxAmount = $totalIncl - $totalExcl; // 保证 totalIncl = totalExcl + taxAmount
                } else {
                    $totalExcl = $totalIncl;
                    $taxAmount = 0;
                }
            } else {
                // ========== 税別モード：totalInputRaw 是不含税总额 ==========
                $totalExcl = round($totalInputRaw);
                $taxAmount = ($taxRate > 0) ? (int)round($totalExcl * ($taxRate / 100)) : 0;
            }

            // 累加到全局
            $subtotalAmount += $totalExcl;
            $totalTaxAmount += $taxAmount;

            // 保存分组结果（即使 taxRate=0 也暂存，后续过滤）
            $taxGroups[$rateKey] = [
                'subtotal' => $totalExcl,
                'tax_amount' => $taxAmount,
                'total_with_tax' => $totalExcl + $taxAmount,
            ];
        }

        // === 第三步：1円調整（仅税込模式）===
        $totalAmount = $subtotalAmount + $totalTaxAmount;
        if ($validated['tax_mode'] == 1) {
            // 计算用户输入的总含税金额（期望值）
            $expectedTotal = 0;
            foreach ($groups as $group) {
                $expectedTotal += round($group['total_input_raw']);
            }

            $diff = $expectedTotal - $totalAmount;

            // 允许 ±1 円误差，进行调整
            if ($diff !== 0 && abs($diff) <= 1) {
                // 从 taxGroups 中找最后一个应税组（tax_rate > 0）调整
                $adjusted = false;
                foreach (array_reverse($taxGroups, true) as $rateKey => $data) {
                    if ((float)$rateKey > 0) {
                        $taxGroups[$rateKey]['tax_amount'] += $diff;
                        $taxGroups[$rateKey]['total_with_tax'] += $diff;
                        $totalTaxAmount += $diff;
                        $totalAmount += $diff;
                        $adjusted = true;
                        break;
                    }
                }

                // 如果没有应税项目，则调整 subtotal（极少见）
                if (!$adjusted) {
                    $subtotalAmount -= $diff;
                    $totalAmount -= $diff;
                }
            }
        }

        // === 第四步：插入 invoices 主表 ===
        $invoiceId = DB::table('invoices')->insertGetId([
            'group_id' => $validated['group_id'],
            'invoice_number' => $this->generateInvoiceNumber(), // 确保该方法存在
            'customer_id' => 1, // ⚠️ 请根据实际业务替换
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'billing_title' => $validated['billing_title'],
            'subtotal_amount' => $subtotalAmount,
            'tax_amount' => $totalTaxAmount,
            'total_amount' => $totalAmount,
            'tax_mode' => $validated['tax_mode'],
            'language' => $validated['language'],
            'currency_code' => $validated['currency_code'],
            'exchange_rate' => $currency->rate_to_jpy,
            'pdf_template_id' => null,
            'pdf_file_path' => null,
            'is_locked' => 0,
            'notes' => $validated['notes'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // === 第五步：插入 invoice_items（仅存原始输入，不参与税务计算）===
        $itemsToInsert = [];
        foreach ($validated['items'] as $index => $item) {
            $itemsToInsert[] = [
                'invoice_id' => $invoiceId,
                'line_number' => $index + 1,
                'description' => $item['description'],
                'quantity' => (float)$item['quantity'],
                'unit_price' => (float)$item['unit_price'],
                'amount' => round((float)$item['quantity'] * (float)$item['unit_price'], 2),
                'tax_rate' => (float)$item['tax_rate'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('invoice_items')->insert($itemsToInsert);

        // === 第六步：插入 invoice_tax_summary（只存 8% 和 10%，过滤 0%）===
        $summaryToInsert = [];
        foreach ($taxGroups as $taxRate => $data) {
            if ((float)$taxRate > 0) { // ✅ 只保留应税项目
                $summaryToInsert[] = [
                    'invoice_id' => $invoiceId,
                    'tax_rate' => $taxRate,
                    'subtotal' => (int)round($data['subtotal']),
                    'tax_amount' => (int)round($data['tax_amount']),
                    'total_with_tax' => (int)round($data['total_with_tax']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        if (!empty($summaryToInsert)) {
            DB::table('invoice_tax_summary')->insert($summaryToInsert);
        }

        DB::commit();

        return redirect()->route('masters.invoices.index', ['group_id' => $validated['group_id']])
            ->with('success', '請求書を登録しました。');

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('請求書作成エラー: ' . $e->getMessage());
        return redirect()->back()
            ->withInput()
            ->withErrors(['error' => 'システムエラーが発生しました。管理者にお問い合わせください。']);
    }
}

    /**
     * 生成请求书编号（示例：INV-20260309-001）
     */
    private function generateInvoiceNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $lastInvoice = DB::table('invoices')
            ->where('invoice_number', 'like', "INV-{$dateStr}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNum = (int)substr($lastInvoice->invoice_number, -3);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return "INV-{$dateStr}-{$newNum}";
    }

    public function show(Request $request, Invoice $invoice)
    {
        $groupId = $request->query('group_id');

        // if (! $groupId || $invoice->group_id != $groupId || ! auth()->user()->canAccessGroup($groupId)) {
        //     abort(403);
        // }
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $taxSummary = DB::table('invoice_tax_summary')->where('invoice_id', $invoice->id)->get();
        return view('masters.invoices.show', compact('invoice', 'groupId','items'));
    }

    public function edit(Request $request, Invoice $invoice)
    {
        $groupId = $request->query('group_id');

        // if (! $groupId || $invoice->group_id != $groupId || ! auth()->user()->canAccessGroup($groupId)) {
        //     abort(403);
        // }
        if($invoice->is_locked){
            return redirect()->route('masters.invoices.index', ['group_id' => $groupId])
                ->with('error', 'この請求書は編集できません。');
        }
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();
        $currencies = Currency::select('currency_code', 'id')->distinct()->orderBy('currency_code')->get();
        return view('masters.invoices.edit', compact('invoice', 'groupId','items','currencies'));
    }

    public function update(Request $request, int $id)
    {
        $invoice = DB::table('invoices')->where('id', $id)->first();
        if($invoice->is_locked){
            return redirect()->route('masters.invoices.index', ['group_id' => $invoice->group_id])
                ->with('error', 'この請求書は編集できません。');
        }

        
        // === 1. 验证输入（与 store 一致）===
        $validated = $request->validate([
            'group_id' => 'nullable|integer',
            'billing_title' => 'nullable|string|max:200',
            'tax_mode' => 'required|in:1,2', // 1=税込, 2=税別
            'language' => 'required|in:1,2', // 1=日语, 2=英语
            'currency_code' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string|max:65535',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:300',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'required|numeric|in:0,8,10',
            'items.*.display_order' => 'required|integer|min:1',
        ], [
            'items.required' => '明細は最低1行必要です。',
            'due_date.after_or_equal' => '支払期日は請求日以降にしてください。',
            'items.*.tax_rate.in' => '税率は 0（非課税）、8、10 のいずれかにしてください。',
        ]);

        $validated['items'] = array_values($validated['items']); // 重置索引

        //查询$validated['invoice_date']在currencies表中rate_valid_from之后，在rate_valid_to之前的数据
        $currency = Currency::where('currency_code', $validated['currency_code']) // 通常还需要匹配币种代码
        ->where('rate_valid_from', '<=', $validated['invoice_date'])
        ->where('rate_valid_to', '>=', $validated['invoice_date'])
        ->orderBy('rate_valid_from', 'desc') // 如果有重叠，取最新生效的
        ->first();
        if (!$currency) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '指定の通貨に対する指定の日付の為替レートが見つかりません。']);
        }

        DB::beginTransaction();
        try {
            // === 2. 校验发票是否存在 ===
            if (!$invoice) {
                throw new \Exception('請求書が見つかりません。');
            }

            // === 3. 按税率分组原始输入金额（不分舍入）===
            $groups = [];
            foreach ($validated['items'] as $item) {
                $quantity = (float)$item['quantity'];
                $unitPrice = (float)$item['unit_price'];
                $taxRate = (float)$item['tax_rate'];
                $rateKey = number_format($taxRate, 2, '.', '');

                if (!isset($groups[$rateKey])) {
                    $groups[$rateKey] = [
                        'tax_rate' => $taxRate,
                        'total_input_raw' => 0,
                    ];
                }
                $groups[$rateKey]['total_input_raw'] += $quantity * $unitPrice;
            }

            // === 4. 对每组统一计算不含税 & 税额 ===
            $subtotalAmount = 0;
            $totalTaxAmount = 0;
            $taxGroups = [];

            foreach ($groups as $rateKey => $group) {
                $taxRate = $group['tax_rate'];
                $totalInputRaw = $group['total_input_raw'];

                if ($validated['tax_mode'] == 1) {
                    // ========== 税込モード ==========
                    $totalIncl = round($totalInputRaw);
                    if ($taxRate > 0) {
                        $baseAmount = $totalIncl / (1 + $taxRate / 100);
                        $totalExcl = (int)ceil($baseAmount); // ✅ 向上取整
                        $taxAmount = $totalIncl - $totalExcl;
                    } else {
                        $totalExcl = $totalIncl;
                        $taxAmount = 0;
                    }
                } else {
                    // ========== 税別モード ==========
                    $totalExcl = round($totalInputRaw);
                    $taxAmount = ($taxRate > 0) ? (int)round($totalExcl * ($taxRate / 100)) : 0;
                }

                $subtotalAmount += $totalExcl;
                $totalTaxAmount += $taxAmount;

                $taxGroups[$rateKey] = [
                    'subtotal' => $totalExcl,
                    'tax_amount' => $taxAmount,
                    'total_with_tax' => $totalExcl + $taxAmount,
                ];
            }

            // === 5. 1円調整（仅税込模式）===
            $totalAmount = $subtotalAmount + $totalTaxAmount;
            if ($validated['tax_mode'] == 1) {
                $expectedTotal = 0;
                foreach ($groups as $group) {
                    $expectedTotal += round($group['total_input_raw']);
                }

                $diff = $expectedTotal - $totalAmount;
                if ($diff !== 0 && abs($diff) <= 1) {
                    $adjusted = false;
                    foreach (array_reverse($taxGroups, true) as $rateKey => $data) {
                        if ((float)$rateKey > 0) {
                            $taxGroups[$rateKey]['tax_amount'] += $diff;
                            $taxGroups[$rateKey]['total_with_tax'] += $diff;
                            $totalTaxAmount += $diff;
                            $totalAmount += $diff;
                            $adjusted = true;
                            break;
                        }
                    }
                    if (!$adjusted) {
                        $subtotalAmount -= $diff;
                        $totalAmount -= $diff;
                    }
                }
            }



            // === 6. 更新 invoices 主表 ===
            DB::table('invoices')->where('id', $id)->update([
                'group_id' => $validated['group_id'],
                'customer_id' => 1, // ⚠️ 请根据实际业务替换
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'billing_title' => $validated['billing_title'],
                'subtotal_amount' => $subtotalAmount,
                'tax_amount' => $totalTaxAmount,
                'total_amount' => $totalAmount,
                'tax_mode' => $validated['tax_mode'],
                'language' => $validated['language'],
                'currency_code' => $validated['currency_code'],
                'exchange_rate' => $currency->rate_to_jpy,
                'pdf_file_path' => null,
                'notes' => $validated['notes'],
                'updated_at' => now(),
            ]);

            // === 7. 删除旧的明细和汇总 ===
            DB::table('invoice_items')->where('invoice_id', $id)->delete();
            DB::table('invoice_tax_summary')->where('invoice_id', $id)->delete();

            // === 8. 插入新的 invoice_items（仅原始输入）===
            $itemsToInsert = [];
            foreach ($validated['items'] as $index => $item) {
                $itemsToInsert[] = [
                    'invoice_id' => $id,
                    'line_number' => $index + 1,
                    'description' => $item['description'],
                    'quantity' => (float)$item['quantity'],
                    // 'unit' => $item['unit'] ?? null,
                    'unit_price' => (float)$item['unit_price'],
                    'amount' => round((float)$item['quantity'] * (float)$item['unit_price'], 2),
                    'tax_rate' => (float)$item['tax_rate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($itemsToInsert)) {
                DB::table('invoice_items')->insert($itemsToInsert);
            }

            // === 9. 插入新的 invoice_tax_summary（仅 8% 和 10%）===
            $summaryToInsert = [];
            foreach ($taxGroups as $taxRate => $data) {
                if ((float)$taxRate > 0) { // ✅ 过滤掉 0%
                    $summaryToInsert[] = [
                        'invoice_id' => $id,
                        'tax_rate' => $taxRate,
                        'subtotal' => (int)round($data['subtotal']),
                        'tax_amount' => (int)round($data['tax_amount']),
                        'total_with_tax' => (int)round($data['total_with_tax']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($summaryToInsert)) {
                DB::table('invoice_tax_summary')->insert($summaryToInsert);
            }

            DB::commit();

            return redirect()->route('masters.invoices.index', ['group_id' => $validated['group_id']])
                ->with('success', '請求書を更新しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('請求書更新エラー (ID: ' . $id . '): ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '更新中にエラーが発生しました。']);
        }
    }

    public function destroy(Request $request, Invoice $invoice)
    {
        $groupId = $request->query('group_id');

        if (! $groupId || $invoice->group_id != $groupId) {
            abort(403);
        }

        try {
            $invoice->delete();

            return redirect()
                ->route('masters.invoices.index', ['group_id' => $groupId])
                ->with([
                    'success' => '請求書を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            Log::error('Invoice delete error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
              //'user_id' => auth()->id()
            ]);

            return redirect()
                ->route('masters.invoices.index', ['group_id' => $groupId])
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    /*
        composer require spatie/browsershot
        npm install puppeteer
    */
    public function generatePdf(Request $request, Invoice $invoice)
    {

        $items = $invoice->items;
        $summary_10 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 10)->first();
        $symmary_8 = InvoiceTaxSummary::where('invoice_id', $invoice->id)->where('tax_rate', 8)->first();
        // 1. 准备数据 (保持你原有的数据结构不变)
        $data = [
            'invoice' => (object)[
                'invoice_date' => $invoice->invoice_date,
                'due_date' => $invoice->due_date,
                'invoice_number' => $invoice->invoice_number,
                'notes'=> $invoice->notes,
                'subtotal_amount'=> $invoice->subtotal_amount,
                'tax_amount'=> $invoice->tax_amount,
                'total_amount'=> $invoice->total_amount,
                'tax_mode'=> $invoice->tax_mode,
                'currency_code'=> $invoice->currency_code
            ],
            'summary_10' => $summary_10,
            'summary_8' => $symmary_8,

            'items' => $items,
            'totals' => [
                'non_taxable' => 100000,
                'subtotal' => 4950000,
                'total_tax' => 470000,
                'grand_total' => 5520000,
            ],
            'bank' => (object)[
                'bank_name' => '●●●●銀行',
                'branch_name' => '●●●●●●支店',
                'account_number' => '●●●●●●●●',
                'account_holder' => '●●●●●●●●',
            ],
            'company' => (object)[
                'name' => '株式会社〇〇〇',
                'postal_code' => '123-4567',
                'address' => '〇〇県〇〇市〇〇町1－2－3',
                'phone' => '03-1234-5678',
                'fax' => '09-1234-5679',
                'contact' => '△△△△',
            ],
            'customer' => (object)[
                'name' => '〇〇〇〇〇〇',   
            ]

        ];

    try {
        // 1. 渲染 HTML
        if($invoice->language == 1){
            $html = View::make('masters.invoices.template_ja', $data)->render();
        }else{
            $html = View::make('masters.invoices.template_en', $data)->render();
        }
        

        // 2. 初始化 Browsershot
        // D:\Google\Chrome\Application
        $browsershot = Browsershot::html($html)
            ->setChromePath('D:\Google\Chrome\Application\chrome.exe')
            ->paperSize(210, 297, 'mm')
            ->setOption('margin.top', 15)
            ->setOption('margin.right', 15)
            ->setOption('margin.bottom', 15)
            ->setOption('margin.left', 15)
            ->setOption('printBackground', true)
            ->waitUntilNetworkIdle()
            ->timeout(30000);

        // [Linux/生产环境] 取消下面这行的注释
        // $browsershot->addChromiumArguments(['--no-sandbox', '--disable-setuid-sandbox']);

        // 3. 【关键修改】获取 PDF 内容
        // 方法 A (推荐): 直接获取二进制字符串 (适用于大多数新版本)
        $pdfContent = $browsershot->getPdf();

        // 防御性检查：如果 getPdf() 返回的不是字符串（比如返回了对象或路径）
        if (!is_string($pdfContent)) {
            // 如果返回的是对象，尝试保存为临时文件再读取
            $tempFile = tempnam(sys_get_temp_dir(), 'invoice_') . '.pdf';
            $browsershot->savePdf($tempFile);
            $pdfContent = file_get_contents($tempFile);
            unlink($tempFile); // 立即删除临时文件
            
            // 如果还是不对，抛出异常以便调试
            if (!is_string($pdfContent)) {
                throw new \Exception('Failed to get PDF content as string. Got: ' . gettype($pdfContent));
            }
        }

        // 4. 生成文件名
        $filename = 'invoice_' . $data['invoice']->invoice_number . '.pdf';

        // 5. 返回响应 (现在 strlen 接收的肯定是字符串了)
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($pdfContent), 
        ]);

    } catch (\Exception $e) {
        Log::error('PDF Generation Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        
        if (app()->environment('local')) {
            return response()->json([
                'message' => 'PDF 生成失败',
                'error' => $e->getMessage(),
                'type' => gettype($e), // 显示错误类型
            ], 500);
        }
        return response()->view('errors.500', [], 500);
    }
    }

    public function toggleLock(Invoice $invoice)
    {
        // 切换状态
        $invoice->is_locked = !$invoice->is_locked;
        $invoice->save();

        return response()->json([
            'success' => true,
            'is_locked' => $invoice->is_locked,
            'message' => $invoice->is_locked ? 'ロックしました' : 'ロックを解除しました'
        ]);
    }
}