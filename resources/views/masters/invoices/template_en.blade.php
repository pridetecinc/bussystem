<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>INVOICE</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm;
        }
        body {
            /* 英文优先使用无衬线字体，同时保留 Mincho 以防特殊字符 */
            font-family: "Helvetica", "Arial", "MS Mincho", sans-serif;
            font-size: 10pt;
            color: #000;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }

        .no-break {
            page-break-inside: avoid;
        }

        /* 1. Header */
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
            letter-spacing: 1px; 
            text-transform: uppercase; 
        }
        .meta-info { 
            text-align: right; 
            font-size: 9pt; 
            line-height: 1.4; 
        }

        /* 2. Client & Company */
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
            font-style: italic;
            color: #555;
        }
        
        .total-hint { 
            font-size: 11pt; 
            margin-bottom: 8px; 
            font-weight: bold;
        }
        .total-hint strong { 
            font-size: 13pt; 
        }

        /* 3. Main Table */
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
            text-transform: uppercase;
        }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }

        /* 4. Summary Table */
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
        
        /* Adjust widths for English labels to fit better */
        .w-rate { width: 20%; } 
        .w-amount { width: 18%; }
        .w-tax-label { width: 17%; }
        .w-tax-val { width: 15%; }
        .w-total-label { width: 15%; }
        .w-total-val { width: 15%; }

        /* 5. Footer */
        .footer-section { 
            margin-top: 5px; 
            font-size: 9pt; 
            line-height: 1.4;
        }
        .payment-deadline { 
            margin-bottom: 8px; 
            font-size: 10pt;
            font-weight: bold;
            color: #3b5998;
            text-transform: uppercase;
        }
        .payment-note {
            font-size: 9pt;
            margin-bottom: 8px;
            font-style: italic;
            color: #555;
        }

        .bank-info {
            width: 100%;
            line-height: 1.4;
        }
        div.bank-title {
            font-weight: bold;
            margin-bottom: 4px;
            display: block;
            font-size: 10pt;
            text-decoration: none !important;
            border-bottom: none !important;
            color: #3b5998;
            text-transform: uppercase;
        }
        .bank-row {
            display: flex;
            margin-bottom: 2px;
        }
        .bank-label {
            width: 100px; /* Wider for English labels like "Account Name" */
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- 1. Header -->
    <div class="header-section no-break">
        <h1 class="main-title">INVOICE</h1>
        <div class="meta-info">
            <div><strong>Date:</strong> {{ $invoice->invoice_date }}</div>
            <div><strong>Invoice#:</strong> {{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <!-- 2. Client & Company -->
    <div class="top-section no-break">
        <div class="client-name">
            <div style="font-size: 9pt; font-weight: normal; margin-bottom: 2px; color:#555;">BILL TO:</div>
            <!-- Removed Japanese '様' -->
            <div style="font-size: 11pt;">{{ $customer->name }}</div>
            @if(isset($customer->attention))
                <div style="font-size: 9pt;">Attn: {{ $customer->attention }}</div>
            @endif
        </div>

        <div class="company-info">
            <div class="company-name">{{ $company->name }}</div>
            <div>{{ $company->address }}</div>
            @if($company->postal_code)
                <div>{{ $company->postal_code }}</div>
            @endif
            <div>Tel: {{ $company->phone }} | Fax: {{ $company->fax }}</div>
            @if($company->contact)
                <div>Attn: {{ $company->contact }}</div>
            @endif
        </div>
    </div>

    <div class="total-hint">
        Total Amount ({{ $invoice->currency_code }}): <strong>{{ number_format($invoice->total_amount) }}</strong> 
    </div>

    <!-- 3. Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th style="width: 5%">No.</th>
                <th style="width: 35%" class="text-left">Description</th>
                <th style="width: 8%">QTY</th>
                <th style="width: 12%">Unit Price</th>
                <th style="width: 12%">Amount</th>
                <th style="width: 8%">Tax Rate</th>
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
                <td>
                    @if ($item->tax_rate > 0)
                        {{ $item->tax_rate }}%
                    @endif
                </td>
            </tr>
            @endforeach
            
            {{-- Fill empty rows --}}
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

    <!-- 4. Summary Table (Retaining 10%/8% Breakdown) -->
    <table class="summary-table no-break">
        <!-- Row 1: 10% -->
        <tr>
            <td class="w-rate text-left"><strong>10% Taxable Sales</strong></td>
            <td class="w-amount text-right">{{ number_format($summary_10->total_with_tax ?? 0) }}</td>
            <td class="w-tax-label text-right">Consumption Tax</td>
            <td class="w-tax-val text-right">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
            <td class="w-total-label text-center font-bold">Subtotal (10%)</td>
            <td class="w-total-val text-right font-bold">
                 @if($invoice->tax_mode == 1)
                    {{ number_format($summary_10->total_with_tax ?? 0) }}
                @else
                    {{ number_format(($summary_10->total_with_tax ?? 0) - ($summary_10->tax_amount ?? 0)) }}
                @endif
            </td>
        </tr>
        <!-- Row 2: 8% -->
        <tr>
            <td class="w-rate text-left"><strong>8% Taxable Sales</strong></td>
            <td class="w-amount text-right">{{ number_format($summary_8->total_with_tax ?? 0) }}</td>
            <td class="w-tax-label text-right">Consumption Tax</td>
            <td class="w-tax-val text-right">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
            <td class="w-total-label text-center font-bold">Subtotal (8%)</td>
            <td class="w-total-val text-right font-bold">
                @if($invoice->tax_mode == 1)
                    {{ number_format($summary_8->total_with_tax ?? 0) }}
                @else
                    {{ number_format(($summary_8->total_with_tax ?? 0) - ($summary_8->tax_amount ?? 0)) }}
                @endif
            </td>
        </tr>
        <!-- Row 3: Non-Taxable & Grand Totals -->
        <tr>
            <td class="w-rate text-left"><strong>Non-Taxable / Exempt</strong></td>
            <td class="w-amount text-right">{{ number_format($totals['non_taxable'] ?? 0) }}</td>
            <td colspan="2"></td>
            <td class="w-total-label text-center font-bold">Total Tax</td>
            <td class="w-total-val text-right font-bold">{{ number_format($invoice->tax_amount) }}</td>
        </tr>
        <!-- Grand Total Row (Merged for emphasis) -->
        <tr style="background-color: #f9f9f9;">
            <td colspan="4"></td>
            <td class="w-total-label text-center font-bold" style="font-size: 10pt; color: #3b5998;">GRAND TOTAL</td>
            <td class="w-total-val text-right font-bold" style="font-size: 11pt; color: #3b5998;">{{ number_format($invoice->total_amount) }}</td>
        </tr>
    </table>

    <!-- 5. Payment Info -->
    <div class="footer-section no-break">
        <div class="payment-deadline">
            Payment Due By: {{ $invoice->due_date }}
        </div>

        @if(!empty($invoice->notes))
        <div class="bank-title">[Remarks]</div>
        <div class="bank-info" style="margin-bottom: 8px;">{{ $invoice->notes }}</div>
        @endif

        <div class="bank-info">
            <div class="bank-title">Bank Details</div>
            <div class="bank-row">
                <span class="bank-label">Bank Name:</span>
                <span>{{ $bank->bank_name }} {{ $bank->branch_name }}</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Account Type:</span>
                <span>Ordinary</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Account No:</span>
                <span>{{ $bank->account_number }}</span>
            </div>
            <div class="bank-row">
                <span class="bank-label">Account Name:</span>
                <span>{{ $bank->account_holder }}</span>
            </div>
        </div>
        
    </div>

</body>
</html>