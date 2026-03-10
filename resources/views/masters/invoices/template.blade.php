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
            letter-spacing: 2px; 
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
        
        .w-rate { width: 12%; }
        .w-amount { width: 13%; }
        .w-tax-label { width: 10%; }
        .w-tax-val { width: 13%; }
        .w-total-label { width: 22%; }
        .w-total-val { width: 30%; }

        /* 5. 支付信息与备注 */
        .footer-section { 
            margin-top: 5px; 
            font-size: 9pt; 
            line-height: 1.4;
        }
        .payment-deadline { 
            margin-bottom: 8px; 
            font-size: 10pt;
        }

        /* 【修改】备注样式：黑色字体，正常行高 */
        .note-section {
            margin-bottom: 8px;
            font-size: 9pt;
            color: #000; /* 默认黑色 */
            line-height: 1.4;
            white-space: pre-wrap; /* 支持换行符 */
        }

        .bank-info {
            width: 100%;
            line-height: 1.4;
        }
        .bank-title {
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
            font-size: 10pt;
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
        <h1 class="main-title">請 求 書</h1>
        <div class="meta-info">
            <div>請求日：{{ $invoice->invoice_date }}</div>
            <div>請求番号：{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <!-- 2. 客户与公司 -->
    <div class="top-section no-break">
        <div class="client-name">
            {{ $customer->name }}
        </div>

        <div class="company-info">
            <div class="company-name">{{ $company->name }}</div>
            <div>〒{{ $company->postal_code }}</div>
            <div>{{ $company->address }}</div>
            <div>Tel: {{ $company->phone }} / Fax: {{ $company->fax }}</div>
            <div>担当：{{ $company->contact }}</div>
        </div>
    </div>

    <div class="greeting">下記のとおり、ご請求申し上げます。</div>

    <div class="total-hint">
        合計請求金額 <strong>{{ number_format($invoice->total_amount) }}</strong> 
        @if($invoice->tax_mode == 1)
            (税込)
        @else
            (税別)
        @endif
    </div>

    <!-- 3. 主表格 (15行) -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%">No.</th>
                <th style="width: 35%" class="text-left">内容</th>
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
            <td class="w-rate text-left">10% 対象</td>
            <td class="w-amount text-right">{{ number_format($summary_10->total_with_tax ?? 0) }}</td>
            <td class="w-tax-label text-right">消費税</td>
            <td class="w-tax-val text-right">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
            <td class="w-total-label text-center font-bold">小計</td>
            <td class="w-total-val text-right font-bold">
                @if($invoice->tax_mode == 1)
                    {{ $invoice->total_amount }}
                @else
                    {{ $invoice->subtotal_amount }}
                @endif
            
            </td>
        </tr>
        <tr>
            <td class="w-rate text-left">8% 対象</td>
            <td class="w-amount text-right">{{ number_format($summary_8->total_with_tax ?? 0) }}</td>
            <td class="w-tax-label text-right">消費税</td>
            <td class="w-tax-val text-right">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
            <td class="w-total-label text-center font-bold">消費税額</td>
            <td class="w-total-val text-right font-bold">{{ $invoice->tax_amount }}</td>
        </tr>
        <tr>
            <td class="w-rate text-left">非課税</td>
            <td class="w-amount text-right">{{ number_format($totals['non_taxable'] ?? 0) }}</td>
            <td colspan="2"></td>
            <td class="w-total-label text-center font-bold">請求合計</td>
            <td class="w-total-val text-right font-bold">{{ $invoice->total_amount }}</td>
        </tr>
    </table>

    <!-- 5. 支付信息与备注 -->
    <div class="footer-section no-break">
        <div class="payment-deadline">お支払期限：<strong>{{ $invoice->due_date }}</strong></div>
        
        {{-- 【新增】条件显示备注 --}}
        @if(!empty($invoice->notes))
        <span class="bank-title">【備考】</span>
        <div class="bank-info">{{ $invoice->notes }}</div>
        @endif

        <div class="bank-info">
            <span class="bank-title">【振込先】</span>
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