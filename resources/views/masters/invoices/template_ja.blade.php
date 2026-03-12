<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>請求書 (8列网格对齐版)</title>
    <style>
        @page { size: A4; margin: 15mm 15mm; }
        body { font-family: "MS Mincho", "Yu Mincho", "Hiragino Mincho Pro", serif; font-size: 10pt; margin: 0; line-height: 1.3; color: #000; }
        .no-break { page-break-inside: avoid; }
        
        /* 头部与公司 */
        .header-section { display: flex; justify-content: space-between; margin-bottom: 10px; align-items: flex-start; }
        .main-title { font-size: 24pt; font-weight: bold; color: #3b5998; margin: 0; letter-spacing: 0; }
        .meta-info { text-align: right; font-size: 9pt; line-height: 1.4; }
        .top-section { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .client-name { 
            width: 45%; 
            font-size: 11pt; 
            font-weight: bold; 
            color: #333; 
            line-height: 1.4; 
            
            /* 【新增】关键修改开始 */
            min-height: 80px;       /* 设定最小高度，防止内容太少时太矮 (原可能没有或很小) */
            padding: 10px 0;        /* 上下增加 10px 留白，防止文字贴顶贴底 */
            box-sizing: border-box; /* 确保 padding 不会撑大总宽度 */
            
            /* 如果你需要显示红框调试，可以取消下面这行的注释 */
            /* border: 1px solid red; */ 
            /* 【新增】关键修改结束 */
        }
        .company-info { width: 50%; text-align: right; font-size: 8.5pt; line-height: 1.4; }
        .company-name { font-size: 10.5pt; font-weight: bold; margin-bottom: 2px; }
        .greeting { margin: 8px 0; font-size: 10pt; }
        .total-hint {
            text-align: right;
            margin-top: 15px;
            
            /* 1. 字体变大 */
            font-size: 14pt; 
            
            /* 2. 【关键】行高设为 1，消除文字上下的默认空白，让线紧贴字底 */
            line-height: 1; 
            
            /* 3. 下划线设置 */
            border-bottom: 2px solid #333;
            
            /* 4. 【关键】padding-bottom 必须为 0，否则线会下沉 */
            padding-bottom: 0; 
            
            /* 5. 【关键】用 margin-bottom 控制线与下方表格的距离，避免重合 */
            /* 如果还重合，把这个值改大，比如 15px 或 20px */
            margin-bottom: 10px; 
            
            display: inline-block; /* 让线只包裹文字宽度 */
            vertical-align: bottom; /* 确保底部对齐基准一致 */
        }
        .total-hint strong { font-size: 15pt; }

        /* ================= 核心表格样式 (8列系统) ================= */
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; /* 关键：固定布局 */
            font-size: 9pt; 
            margin-bottom: 0; 
        }
        .main-table th, .main-table td { 
            border: 1px solid #333333; 
            padding: 4px 2px; 
            text-align: center; 
            vertical-align: middle;
            overflow: hidden;
            word-wrap: break-word;
        }
        .main-table thead th { 
            background-color: #3b5998; 
            color: #fff; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact; 
            font-size: 9pt;
            padding: 5px 2px;
        }

        /* 【关键】定义8个基础列的宽度 (总和100%) */
        /* 1. No (5%) */
        .c1 { width: 5%; }
        /* 2,3,4. 摘要区域 (共40%, 每格约13.3%) */
        .c2 { width: 13.33%; }
        .c3 { width: 13.33%; }
        .c4 { width: 13.34%; }
        /* 5,6. 数量+单价区域 (共17%, 每格8.5%) */
        .c5 { width: 8.5%; }
        .c6 { width: 8.5%; }
        /* 7,8. 金额+税率区域 (共25%, 每格12.5%) */
        .c7 { width: 12.5%; }
        .c8 { width: 12.5%; }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold; }
        
        /* 统计行背景 */
        .summary-row td {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .total-final-row td {
            background-color: #eef2f7;
            font-size: 10pt;
        }

        /* 底部信息 */
        .footer-section { margin-top: 10px; font-size: 9pt; line-height: 1.4; }
        .payment-deadline { margin-bottom: 8px; font-size: 10pt; font-weight: bold; }
        .bank-info { width: 100%; line-height: 1.4; }
        div.bank-title { font-weight: bold; margin-bottom: 4px; display: block; font-size: 10pt; text-decoration: none !important; border-bottom: none !important; }
        .bank-row { display: flex; margin-bottom: 2px; }
        .bank-label { width: 80px; font-weight: bold; }
    </style>
</head>
<body>

    <!-- 1. 标题 -->
    <div class="header-section no-break">
        <h1 class="main-title">請求書</h1>
        <div class="meta-info">
            <div>請求日：{{ $invoice->invoice_date }}</div>
            <div>請求番号：{{ $invoice->invoice_number }}</div>
        </div>
    </div>

    <!-- 2. 客户与公司 -->
    <div class="top-section no-break">
        <div class="client-name">
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

    <div class="greeting">下記の通りご請求申し上げます。</div>
    <div class="total-hint">
        ご請求金額({{$invoice->currency_code}})：<strong>{{ number_format($invoice->total_amount) }}</strong> 
        @if($invoice->tax_mode == 1)(税込)@else(税別)@endif
    </div>

    <!-- ================= 合并后的主表格 (8列系统) ================= -->
    <table class="main-table">
        <thead>
            <tr>
                <!-- 1. No. (占基础列 1) -->
                <th class="c1" style="width: 10%;">No.</th>
                
                <!-- 2. 摘要 (占基础列 2+3+4 -> colspan=3) -->
                <th class="c2 text-left" colspan="3" style="padding-left: 5px;width: 50%;">摘要</th>
                
                <!-- 3. 数量 (占基础列 5) -->
                <th class="c5">数量</th>
                
                <!-- 4. 单价 (占基础列 6) -->
                <th class="c6">単価</th>
                
                <!-- 5. 金额 (占基础列 7) -->
                <th class="c7">金額</th>
                
                <!-- 6. 税率 (占基础列 8) -->
                <th class="c8">税率</th>
            </tr>
        </thead>
        <tbody>
            {{-- A. 商品明细 --}}
            @foreach($items as $index => $item)
            <tr>
                <!-- 1. No. -->
                <td class="c1">{{ $index + 1 }}</td>
                
                <!-- 2. 摘要 (跨3列) -->
                <td class="c2 text-left" colspan="3" style="padding-left: 5px;">
                    {{ $item->description }}
                    @if(isset($item->period) && $item->period)
                        <br><span style="font-size:8pt;color:#666;">{{ $item->period }}</span>
                    @endif
                </td>
                
                <!-- 3. 数量 -->
                <td class="c5">{{ $item->quantity }}</td>
                
                <!-- 4. 单价 -->
                <td class="c6 text-right">{{ number_format($item->unit_price) }}</td>
                
                <!-- 5. 金额 -->
                <td class="c7 text-right">{{ number_format($item->amount) }}</td>
                
                <!-- 6. 税率 -->
                <td class="c8">
                    @if ($item->tax_rate == 0)&nbsp;@else{{ number_format($item->tax_rate) }}%@endif
                </td>
            </tr>
            @endforeach
            
            {{-- B. 补全空行 (保持总行数 15) --}}
            @php 
                $detailCount = count($items);
                $summaryRows = 3; 
                $targetTotalRows = 15; 
                $remaining = max(0, $targetTotalRows - $detailCount - $summaryRows); 
            @endphp
            
            @for($i = 0; $i < $remaining; $i++)
            <tr>
                <td class="c1">&nbsp;</td>
                <td class="c2" colspan="3"></td>
                <td class="c5"></td>
                <td class="c6"></td>
                <td class="c7"></td>
                <td class="c8"></td>
            </tr>
            @endfor

            {{-- C. 底部统计行 (严格按照你的列对应关系) --}}
            {{-- 对应关系: --}}
            {{-- 下表1 (1列) <- 上表1 --}}
            {{-- 下表2,3,4 (3列) <- 上表2 (摘要) --}}
            {{-- 下表5 (2列) <- 上表3+4 (数量+单价) --}}
            {{-- 下表6 (2列) <- 上表5+6 (金额+税率) --}}

            {{-- 行 1: 10% 统计 --}}
            <tr class="summary-row">
                <!-- 1. 标签 (占1列: c1) -->
                <td class="c1 text-left" style="padding-left: 5px;">10％対象</td>
                
                <!-- 2. 金额数值 (占1列: c2) -->
                <td class="c2 text-right">{{ number_format($summary_10->total_with_tax ?? 0) }}</td>
                
                <!-- 3. 内税文字 (占1列: c3) -->
                <td class="c3 text-right" style="font-weight: normal; font-size: 8.5pt;">消費税</td>
                
                <!-- 4. 税额数值 (占1列: c4) -->
                <td class="c4 text-right">{{ number_format($summary_10->tax_amount ?? 0) }}</td>
                
                <!-- 5. 小计标签 (占2列: c5+c6) -->
                <td class="c5 text-center" colspan="2">小計</td>
                
                <!-- 6. 总计数值 (占2列: c7+c8) -->
                <td class="c7 text-right font-bold" colspan="2">
                    @if($invoice->tax_mode == 1){{ number_format($invoice->total_amount) }}@else{{ number_format($invoice->subtotal_amount) }}@endif
                </td>
            </tr>

            {{-- 行 2: 8% 统计 --}}
            <tr class="summary-row">
                <!-- 1. 标签 -->
                <td class="c1 text-left" style="padding-left: 5px;">8％対象</td>
                
                <!-- 2. 金额数值 -->
                <td class="c2 text-right">{{ number_format($summary_8->total_with_tax ?? 0) }}</td>
                
                <!-- 3. 内税文字 -->
                <td class="c3 text-right" style="font-weight: normal; font-size: 8.5pt;">消費税</td>
                
                <!-- 4. 税额数值 -->
                <td class="c4 text-right">{{ number_format($summary_8->tax_amount ?? 0) }}</td>
                
                <!-- 5. 消费税标签 (占2列) -->
                <td class="c5 text-center" colspan="2">消費税額</td>
                
                <!-- 6. 总税额 (占2列) -->
                <td class="c7 text-right font-bold" colspan="2">{{ number_format($invoice->tax_amount) }}</td>
            </tr>

            {{-- 行 3: 合计 --}}
            <tr class="summary-row total-final-row">
                <!-- 1. 标签 -->
                <td class="c1 text-left" style="padding-left: 5px;">非課税/免税</td>
                
                <!-- 2. 非课税金额 -->
                <td class="c2 text-right">{{ number_format($invoice->non_taxable) }}</td>
                
                <!-- 3,4. 空白 (占2列) -->
                <td class="c3" colspan="2"></td>
                
                <!-- 5. 请求合计标签 (占2列) -->
                <td class="c5 text-center" colspan="2">請求合計</td>
                
                <!-- 6. 总金额 (占2列) -->
                <td class="c7 text-right font-bold" colspan="2">{{ number_format($invoice->total_amount) }}</td>
            </tr>

        </tbody>
    </table>

    <!-- 5. 支付信息与备注 -->
    <div class="footer-section no-break">
        <div class="payment-deadline">
            お支払いは :{{ $invoice->due_date }} までに下記指定口座へお振込お願いします。
        </div>

        @if(!empty($invoice->notes))
        <div class="bank-title">【備考】</div>
        <div class="bank-info">{{ $invoice->notes }}</div>
        @endif

        <div class="bank-info">
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