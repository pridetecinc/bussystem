<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\AccountJournalEntry;
use App\Models\Masters\AccountJournalLine;
use App\Models\Masters\Account; 
use App\Models\Masters\AccountSub; 
use App\Models\Masters\AccountDepartment;
use App\Models\Masters\AccountPartner; 
use App\Models\Masters\AccountTax; 
use App\Models\Masters\Staff; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class AccountJournalEntryController extends Controller
{
    public function index(Request $request)
    {

        $query = AccountJournalEntry::query();
        
        // 如果有部门隔离需求，可以在这里加 where('department_id', ...)
        // 这里暂时只按日期范围或搜索过滤

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('lines', function($ql) use ($search) {
                      // 关联查询明细中的备注
                      $ql->where('remark', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('posting_date')) {
            $query->where('posting_date', '=', $request->posting_date);
        }

        $perPage = 20;
        $allowedPerPages = [20, 30, 50];
        if ($request->filled('per_page') && in_array((int)$request->per_page, $allowedPerPages)) {
            $perPage = (int)$request->per_page;
        }

        $entries = $query->orderBy('id', 'desc')->paginate($perPage);
        $entries->appends($request->only(['search', 'posting_date',   'per_page']));

        // 预加载一些基础数据用于筛选下拉框 (可选)
        $departments = AccountDepartment::get();
        $staffs = Staff::where("is_active", 1)->get();

        $accounts = Account::where('is_active', 1)->orderBy('code')->get();
        $partners = AccountPartner::get(); // 交易伙伴
        $taxes = AccountTax::get();       // 税区分

        return view('masters.journal_entries.index', compact('entries',  'departments', 'staffs','accounts','partners','taxes'));
    }

    public function create(Request $request)
    {
        $groupId = $request->query('group_id');
        
        $accounts = Account::where('is_active', 1)->orderBy('code')->get();
        $departments = AccountDepartment::where('is_active', 1)->get();
        $partners = AccountPartner::where('is_active', 1)->get(); // 交易伙伴
        $taxes = AccountTax::where('is_active', 1)->get();       // 税区分
        $staffs = Staff::where('is_active', 1)->get();

        return view('masters.journal_entries.create', compact('groupId', 'accounts', 'departments', 'partners', 'taxes', 'staffs'));
    }

    public function getAccountSubs($accountId)
    {
        // 查询 account_subs 表，where account_id = $accountId
        $subs = AccountSub::where('account_id', $accountId)
            ->where("is_active",1)
            ->select('id', 'name') // 根据需要选择字段
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'display' => $item->name,
                ];
            });

        return response()->json($subs);
    }

    public function store(Request $request)
    {
        // 1. 验证数据
        // 注意：side 必须严格限制为 '借' 或 '貸'，不能 nullable，否则前端传空也会过
        $validated = $request->validate([
            'posting_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'department_id' => 'nullable|string',
            'source_type' => 'nullable|string|max:50',
            'source_id' => 'nullable|integer',
            
            'lines' => 'required|array|min:1', // 至少有一行
            'lines.*.account_id' => 'required|integer|exists:accounts,id', // 强制要求科目ID
            'lines.*.side' => 'required', // 【关键】强制验证，必须是这两个字
            'lines.*.amount' => 'required|numeric|min:0.01', // 金额必须大于0
            'lines.*.account_sub_id' => 'nullable|exists:account_subs,id', // 允许为空
            'lines.*.account_sub_name' => 'nullable|string|max:255',      // 新增：允许接收文本
            'lines.*.partner_id' => 'nullable|exists:partners,id',        // 允许为空
            'lines.*.partner_name' => 'nullable|string|max:255',          // 新增：允许接收文本
            'lines.*.tax_type_id' => 'nullable|integer|exists:account_taxs,id',
            'lines.*.remark' => 'nullable|string|max:255',
        ], [
            // 自定义错误消息 (可选，Laravel 默认也有)
            //'lines.*.side.in' => '借貸方向は「借」または「貸」にしてください。',
            'lines.*.amount.min' => '金額は 0 より大きい値にしてください。',
            'lines.*.account_id.exists' => '無効な科目が選択されています。',
        ]);

        // 2. 借贷平衡校验 (核心逻辑)
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($validated['lines'] as $line) {
            $amount = (float)$line['amount'];
            if ($line['side'] === '借') {
                $debitTotal += $amount;
            } elseif ($line['side'] === '貸') {
                $creditTotal += $amount;
            }
        }

        // 严格平衡检查 (允许 0.01 的浮点数误差，或者严格为 0)
        if (abs($debitTotal - $creditTotal) > 0.01) {
            // 如果是 AJAX 请求，返回 JSON 错误
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '借貸合計が一致しません。',
                    'errors' => [
                        'lines' => [
                            "借貸合計が一致しません。<br>借方合計: " . number_format($debitTotal, 2) . "<br>貸方合計: " . number_format($creditTotal, 2)
                        ]
                    ]
                ], 422);
            }
            // 如果是普通表单提交
            return back()->withErrors([
                'lines' => "借貸合計が一致しません。<br>借方合計: " . number_format($debitTotal, 2) . "<br>貸方合計: " . number_format($creditTotal, 2)
            ])->withInput();
        }

        DB::beginTransaction();
        try {

            $department_id = 0;
            if($validated['department_id']){
                $department = AccountDepartment::where("name",$validated['department_id'])->first();
                if($department){
                    $department_id = $department->id;
                }else{
                    $newDepartment = AccountDepartment::create([
                        'name' => $validated['department_id'],
                    ]);
                    $department_id = $newDepartment->id;

                }
            }

 
            // A. 创建主表
            $entry = AccountJournalEntry::create([
                'posting_date'  => $validated['posting_date'],
                'department_id' => $department_id,
                'source_type'   => $validated['source_type'],
                'source_id'     => $this->generateAccountNumber(),
                'created_by'    => Auth::id(),
                'updated_by'    => Auth::id(),
            ]);

            // B. 批量插入明细
            $linesData = [];
            foreach ($validated['lines'] as $line) {

                $account_sub_id = 0;
                $partner_id = 0;

                if($line['account_sub_name']){
                    $account_sub = AccountSub::where("account_id",$line['account_id'])->where("name",$line['account_sub_name'])->first();
                    if($account_sub){
                        $account_sub_id = $account_sub->id;
                    }else{
                        $newSub = AccountSub::create([
                            'name' => $validated['department_id'],
                            'is_active'=>1,
                            'account_id'=>$line['account_id']
                        ]);
                        $account_sub_id = $newSub->id;

                    }
                }

                if($line['partner_name']){
                    $partner = AccountPartner::where("name",$line['partner_name'])->first();
                    if($partner){
                        $partner_id = $partner->id;
                    }else{
                        $newPartner = AccountPartner::create([
                            'name' => $line['partner_name']
                        ]);
                        $partner_id = $newPartner->id;

                    }
                }


                $linesData[] = [
                    'journal_entry_id' => $entry->id,
                    'side'             => $line['side'],
                    'account_id'       => (int)$line['account_id'],
                    'sub_account_id'   => $account_sub_id,
                    'partner_id'       => $partner_id,
                    'amount'           => (float)$line['amount'],
                    'tax_type_id'      => $line['tax_type_id'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            
            AccountJournalLine::insert($linesData);

            DB::commit();

            // 【关键修改】根据请求类型返回不同格式
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '仕訳伝票を登録しました。',
                    'data' => [
                        'id' => $entry->id,
                        'redirect_url' => route('masters.journal_entries.index')
                    ]
                ]);
            }

            return redirect()->route('masters.journal_entries.index')
                ->with('success', '仕訳伝票を登録しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('仕訳伝票作成エラー: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'システムエラーが発生しました。',
                    'errors' => ['general' => ['管理者にお問い合わせください。']]
                ], 500);
            }

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'システムエラーが発生しました。管理者にお問い合わせください。']);
        }
    }

    public function show(Request $request, $id)
    {
        $groupId = $request->query('group_id');
        $entry = AccountJournalEntry::with('lines.account', 'lines.partner', 'lines.taxType')->findOrFail($id);
        
        // 计算合计用于显示
        $debitSum = $entry->lines->where('side', '借')->sum('amount');
        $creditSum = $entry->lines->where('side', '貸')->sum('amount');

        $departments = AccountDepartment::where('is_active', 1)->get();
        $staffs = Staff::where('is_active', 1)->get();

        return view('masters.journal_entries.show', compact('entry', 'groupId', 'debitSum', 'creditSum', 'departments', 'staffs'));
    }

    public function edit(Request $request, $id)
    {
        $groupId = $request->query('group_id');
        $entry = AccountJournalEntry::with('lines')->findOrFail($id);

        // 如果已过账/锁定，禁止编辑 (假设有个 is_posted 字段，或者你可以自定义逻辑)
        // if ($entry->is_posted) { ... }

        $accounts = Account::where('is_active', 1)->orderBy('code')->get();
        $departments = AccountDepartment::where('is_active', 1)->get();
        $partners = AccountPartner::where('is_active', 1)->get();
        $taxes = AccountTax::where('is_active', 1)->get();
        $staffs = Staff::where('is_active', 1)->get();

        return view('masters.journal_entries.edit', compact('entry', 'groupId', 'accounts', 'departments', 'partners', 'taxes', 'staffs'));
    }

    public function update(Request $request, int $id)
    {
        $entry = AccountJournalEntry::findOrFail($id);

        // 简单的锁定检查 (可根据实际需求扩展)
        // if ($entry->is_locked) { ... }

        $validated = $request->validate([
            'group_id' => 'nullable|integer',
            'posting_date' => 'required|date',
            'description' => 'nullable|string|max:255',
            'department_id' => 'nullable|integer',
            'source_type' => 'nullable|string|max:50',
            'source_id' => 'nullable|integer',
            
            'lines' => 'required|array',
            'lines.*.id' => 'nullable|integer|exists:account_journal_lines,id', // 用于判断是更新还是新增
            'lines.*.account_id' => 'nullable|integer',
            'lines.*.side' => 'nullable|in:借，貸',
            'lines.*.amount' => 'nullable|numeric|min:0',
            'lines.*.partner_id' => 'nullable|integer',
            'lines.*.tax_type_id' => 'nullable|integer',
            'lines.*.remark' => 'nullable|string|max:255',
            'lines.*._delete' => 'nullable|boolean', // 前端标记删除的行
        ], [
            'lines.*.side.in' => '借貸方向は「借」または「貸」にしてください。',
        ]);

        // 1. 过滤空行和被标记删除的行
        $validLines = [];
        foreach ($validated['lines'] as $line) {
            // 如果标记了删除，跳过
            if (!empty($line['_delete'])) {
                continue;
            }
            // 如果科目和金额都为空，跳过
            if (empty($line['account_id']) && empty($line['amount'])) {
                continue;
            }
            // 完整性检查
            if (!empty($line['account_id'])) {
                if (empty($line['amount']) || $line['amount'] <= 0 || empty($line['side'])) {
                    return back()->withErrors(['lines' => '科目が入力されている場合、金額と借貸方向は必須です。'])->withInput();
                }
                $validLines[] = $line;
            }
        }

        if (count($validLines) === 0) {
            return back()->withErrors(['lines' => '伝票明細は最低1行必要です。'])->withInput();
        }

        // 2. 借贷平衡校验
        $debitTotal = 0;
        $creditTotal = 0;
        foreach ($validLines as $line) {
            $amount = (float)$line['amount'];
            if ($line['side'] === '借') $debitTotal += $amount;
            elseif ($line['side'] === '貸') $creditTotal += $amount;
        }

        if (abs($debitTotal - $creditTotal) > 0) {
            return back()->withErrors([
                'lines' => "借貸合計が一致しません。<br>借方: " . number_format($debitTotal) . ", 貸方: " . number_format($creditTotal)
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // A. 更新主表
            $entry->update([
                'posting_date'  => $validated['posting_date'],
                'description'   => $validated['description'] ?? null,
                'department_id' => $validated['department_id'] ?? null,
                'source_type'   => $validated['source_type'] ?? 'manual',
                'source_id'     => $validated['source_id'] ?? null,
                'updated_by'    => Auth::id(),
            ]);

            // B. 处理明细 (简单策略：删除旧的所有明细，重新插入新的)
            // 对于高频大交易量系统，可以优化为对比差异更新，但此策略逻辑最稳健且不易出错
            AccountJournalLine::where('journal_entry_id', $entry->id)->delete();

            $linesData = [];
            foreach ($validLines as $index => $line) {
                $linesData[] = [
                    'journal_entry_id' => $entry->id,
                    'side'             => $line['side'],
                    'account_id'       => (int)$line['account_id'],
                    'sub_account_id'   => $line['sub_account_id'] ?? null,
                    'partner_id'       => $line['partner_id'] ?? null,
                    'amount'           => (float)$line['amount'],
                    'tax_type_id'      => $line['tax_type_id'] ?? null,
                    'remark'           => $line['remark'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            AccountJournalLine::insert($linesData);

            DB::commit();

            return redirect()->route('masters.journal-entries.edit', [
                'entry' => $entry->id,
                'group_id' => $validated['group_id'] ?? null
            ])->with('success', '仕訳伝票を更新しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('仕訳伝票更新エラー (ID: ' . $id . '): ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => '更新中にエラーが発生しました。']);
        }
    }

    public function destroy(Request $request, $id)
    {
        $entry = AccountJournalEntry::findOrFail($id);
        $groupId = $request->query('group_id');

        // 检查是否允许删除 (例如：已过账的凭证不能删除)
        // if ($entry->is_posted) { ... }

        try {
            // 关联的明细会因为外键约束 (ON DELETE CASCADE) 自动删除，或者手动删除
            // 如果没设级联删除，需手动：$entry->lines()->delete();
            
            $entry->delete();

            return redirect()
                ->route('masters.journal-entries.index', ['group_id' => $groupId])
                ->with([
                    'success' => '仕訳伝票を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            Log::error('Journal Entry delete error: ' . $e->getMessage());
            return redirect()
                ->route('masters.journal-entries.index', ['group_id' => $groupId])
                ->with([
                    'error' => '削除に失敗しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function duplicate(Request $request, $id)
    {
        $newEntry = DB::transaction(function () use ($id) {
            $original = AccountJournalEntry::with('lines')->findOrFail($id);
            
            $newEntry = $original->replicate();
            // 重置某些字段
            $newEntry->description = $original->description . ' (コピー)';
            $newEntry->source_type = 'copy';
            $newEntry->source_id = null; 
            // 不复制 created_by/updated_by，让新记录记录当前用户
            $newEntry->created_by = Auth::id();
            $newEntry->updated_by = Auth::id();
            
            $newEntry->save();

            foreach ($original->lines as $line) {
                $newLine = $line->replicate();
                $newLine->journal_entry_id = $newEntry->id;
                $newLine->save();
            }

            return $newEntry;
        });

        return redirect()->route('masters.journal-entries.edit', [
            'entry' => $newEntry->id,
            'group_id' => $request->query('group_id')
        ])->with([
            'success' => '仕訳伝票のコピーが完了しました。',
            'alert-type' => 'success'
        ]);
    }

    private function generateAccountNumber(): string
    {
        $dateStr = now()->format('Ymd');
        $lastaccount = DB::table('account_journal_entries')
            ->where('source_id', 'like', "ACC-{$dateStr}-%")
            ->orderBy('source_id', 'desc')
            ->first();

        if ($lastaccount) {
            $lastNum = (int)substr($lastaccount->source_id, -3);
            $newNum = str_pad($lastNum + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNum = '001';
        }

        return "ACC-{$dateStr}-{$newNum}";
    }
}