<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>請求書</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm;
        }
        body {
            font-family: "MS Mincho", "Yu Mincho", "Hiragino Mincho Pro", serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* 1. 头部 */
        .header-section { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 10px;
            align-items: flex-start;
        }
        .main-title { 
            font-size: 24pt; 
            font-weight: bold; 
            color: #3b5998; 
            margin: 0; 
            letter-spacing: 0; /* 去掉空格 */
        }
        .meta-info { 
            text-align: right; 
            font-size: 9pt; 
            line-height: 1.4; 
        }

        /* 2. 客户与公司 */
        .top-section { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 10px; 
        }
        .client-name {
            width: 45%;
            font-size: 11pt;
            font-weight: bold;
            color: #333;
            line-height: 1.4;
        }
        .company-info { 
            width: 50%; 
            text-align: right; 
            font-size: 8.5pt; 
            line-height: 1.4; 
        }
        .company-name { 
            font-size: 10.5pt; 
            font-weight: bold; 
            margin-bottom: 2px; 
        }

        .greeting { 
            margin: 8px 0; 
            font-size: 10pt; 
        }
        
        .total-hint { 
            font-size: 11pt; 
            margin-bottom: 8px; 
        }
        .total-hint strong { 
            font-size: 13pt; 
        }

        /* 3. 主表格 */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px; 
            table-layout: fixed; 
            font-size: 9pt; 
        }
        .main-table th, .main-table td { 
            border: 1px solid #ccc; 
            padding: 3px 4px; 
            text-align: center; 
            vertical-align: middle;
        }
        .main-table thead th { 
            background-color: #3b5998; 
            color: #fff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
            padding: 4px 2px; 
            font-size: 9pt;
        }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        /* 4. 底部合计 */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            margin-bottom: 10px;
            table-layout: fixed;
            font-size: 9pt;
        }
        .summary-table td {
            border: 1px solid #ccc;
            padding: 3px 6px;
            vertical-align: middle;
        }
        
        .w-rate { width: 15%; } /* 调整宽度以适应长文本 */
        .w-amount { width: 15%; }
        .w-tax-label { width: 10%; }
        .w-tax-val { width: 15%; }
        .w-total-label { width: 20%; }
        .w-total-val { width: 25%; }

        /* 5. 支付信息与备注 */
        .footer-section { 
            margin-top: 5px; 
            font-size: 9pt; 
            line-height: 1.4;
        }
        .payment-deadline { 
            margin-bottom: 8px; 
            font-size: 10pt;
            font-weight: bold;
        }
        .payment-note {
            font-size: 9pt;
            margin-bottom: 8px;
            font-style: italic;
        }

        .bank-info {
            width: 100%;
            line-height: 1.4;
        }
        /* 强制去除下划线并优化显示 */
        div.bank-title {
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
            font-size: 10pt;
            text-decoration: none !important;
            border-bottom: none !important;
        }
        .bank-row {
            display: flex;
            margin-bottom: 2px;
        }
        .bank-label {
            width: 80px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- 1. 标题 -->
    <div class="header-section no-break">
        <!-- 修改：去掉空格 -->
        <h1 class="main-title">請求書</h1>
        <div class="meta-info">
            <div>請求日：{{ $invoice->invoice_date }}</div>
            <div>請求番号：{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <!-- 2. 客户与公司 -->
    <div class="top-section no-break">
        <div class="client-name">
            <!-- 修改：增加 Bill To: 和 様 -->
            <div style="font-size: 9pt; font-weight: normal; margin-bottom: 2px;">Bill To:</div>
            {{ $customer->name }} 様
        </div>

        <div class="company-info">
            <div class="company-name">{{ $company->name }}</div>
            <div>〒{{ $company->postal_code }}</div>
            <div>{{ $company->address }}</div>
            <div>Tel: {{ $company->phone }} / Fax: {{ $company->fax }}</div>
            <div>担当：{{ $company->contact }}</div>
        </div>
    </div>

    <!-- 修改：とおり -> 通り -->
    <div class="greeting">下記の通りご請求申し上げます。</div>

    <div class="total-hint">
        ご請求金額({{$invoice->currency_code}})：<strong>{{ number_format($invoice->total_amount) }}</strong> 
        @if($invoice->tax_mode == 1)
            (税込)
        @else
            (税別)
        @endif
    </div>

    <!-- 3. 主表格 -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%">No.</th>
                <!-- 修改：内容 -> 摘要 (完全匹配PDF) 或保持 内容 -->
                <th style="width: 35%" class="text-left">摘要</th>
                <th style="width: 8%">数量</th>
                <th style="width: 12%">単価</th>
                <th style="width: 12%">金額</th>
                <th style="width: 8%">税率</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">
                    {{ $item->description }}
                    @if(isset($item->period) && $item->period)
                        <br><span style="font-size:8pt;color:#666;">{{ $item->period }}</span>
                    @endif
                </td>
                <td>{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->unit_price) }}</td>
                <td class="text-right">{{ number_format($item->amount) }}</td>
                @if ($item->tax_rate == 0)
                <td></td>
                @else
                <td>{{ $item->tax_rate }}%</td>
                @endif
            </tr>
            @endforeach
            
            {{-- 补全至 15 行 --}}
            @php $remaining = 15 - count($items); @endphp
            @if($remaining > 0)
                @for($i = 0; $i < $remaining; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            @endif
        </tbody>
    </table>

    <!-- 4. 底部合计 -->
    <table class="summary-table no-break">
        <tr>
            <!-- 修改：10% 対象 -> 10%項目小計 -->
            <td class="w-rate text-left">10%对象</td>
            <td class="w-amount text-right">{{ number_format($summary_10->total_with_tax ?? 0) }}</td>
            <!-- 修改：消費税 -> 内税 (匹配PDF) -->
            <td class="w-tax-label text-right">内税</td>
            <td class="w-tax-val text-right">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
            <td class="w-total-label text-center font-bold">小計</td>
            <td class="w-total-val text-right font-bold">
                @if($invoice->tax_mode == 1)
                    {{ number_format($invoice->total_amount) }}
                @else
                    {{ number_format($invoice->subtotal_amount) }}
                @endif
            </td>
        </tr>
        <tr>
            <!-- 修改：8% 対象 -> 8%項目小計 -->
            <td class="w-rate text-left">8%对象</td>
            <td class="w-amount text-right">{{ number_format($summary_8->total_with_tax ?? 0) }}</td>
            <!-- 修改：消費税 -> 内税 -->
            <td class="w-tax-label text-right">内税</td>
            <td class="w-tax-val text-right">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
            <td class="w-total-label text-center font-bold">消費税額</td>
            <td class="w-total-val text-right font-bold">{{ number_format($invoice->tax_amount) }}</td>
        </tr>
        <tr>
            <td class="w-rate text-left">非課税</td>
            <td class="w-amount text-right">{{ number_format($invoice->non_taxable) }}</td>
            <td colspan="2"></td>
            <!-- 修改：請求合計 -> 請求合計 -->
            <td class="w-total-label text-center font-bold">請求合計</td>
            <td class="w-total-val text-right font-bold">{{ number_format($invoice->total_amount) }}</td>
        </tr>
    </table>

    <!-- 5. 支付信息与备注 -->
    <div class="footer-section no-break">
        <!-- 修改：完整的支付提示语 -->
        <div class="payment-deadline">
            お支払いは :{{ $invoice->due_date }} までに下記指定口座へお振込お願いします。
        </div>

        {{-- 备注部分 --}}
        @if(!empty($invoice->notes))
        <!-- 修改：span 改为 div 以彻底去除下划线 -->
        <div class="bank-title">【備考】</div>
        <div class="bank-info">{{ $invoice->notes }}</div>
        @endif

        <div class="bank-info">
            <!-- 修改：span 改为 div -->
            <div class="bank-title">【振込先】</div>
            <div class="bank-row">
                <span class="bank-label">銀行名</span>
                <span>{{ $bank->bank_name }} {{ $bank->branch_name }}</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">種別</span>
                <span>普通預金</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">口座番号</span>
                <span>{{ $bank->account_number }}</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">口座名義</span>
                <span>{{ $bank->account_holder }}</span>
            </div>
        </div>
    </div>

</body>
</html>