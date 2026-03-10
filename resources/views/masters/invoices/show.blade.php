@extends('layouts.app') {{-- 假设你有主布局 --}}

@section('title', '請求書詳細')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>請求書詳細</h2>
        <div>
            <a href="{{ route('masters.invoices.edit', [ $invoice->id, 'group_id' => $invoice->group_id]) }}" class="btn btn-primary btn-sm">編集</a>
            <a href="{{ route('masters.invoices.index', ['group_id' => $invoice->group_id]) }}" class="btn btn-secondary btn-sm">一覧に戻る</a>
        </div>
    </div>

    <!-- 発注者・請求情報 -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>請求情報</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>請求書番号:</strong> {{ $invoice->invoice_number }}</p>
                    <p><strong>請求日:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}</p>
                    <p><strong>支払期日:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') }}</p>
                    <p><strong>语言:</strong> {{ $invoice->language == 1 ? '日文' : '英文' }}</p>
                    @if($invoice->notes)
                        <p><strong>備考:</strong><br>{{ nl2br(e($invoice->notes)) }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    <p><strong>請求先名:</strong> {{ $invoice->billing_title ?: '未設定' }}</p>
                    <p><strong>通貨:</strong> {{ $invoice->currency_code ?? '—' }}</p>
                    <p><strong>税計算モード:</strong>
                        {{ $invoice->tax_mode == 1 ? '税込価格' : '税別価格' }}
                    </p>
                    <p><strong>汇率:</strong> {{ $invoice-> exchange_rate}}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>登録日時:</strong> {{ $invoice->created_at }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>最終更新日時:</strong> {{ $invoice->updated_at }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 明細テーブル -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <strong>明細</strong>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th>内容</th>
                        <th width="10%">数量</th>
                        <th width="10%">単位</th>
                        <th width="12%">単価</th>
                        <th width="10%">税率 (%)</th>
                        <th width="15%">金額（税抜）</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->description }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }}</td>
                            <td>{{ $item->unit ?? '—' }}</td>
                            <td class="text-end">
                            {{$item->unit_price }}
                            </td>
                            <td class="text-end">{{ number_format($item->tax_rate, 2) }}%</td>
                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="6" class="text-end">小計（税抜）:</td>
                        <td class="text-end">
                            @if ($invoice->tax_mode==1)
                                {{ number_format($invoice->total_amount, 2) }}
                            @else
                                {{ number_format($invoice->subtotal_amount, 2) }}
                            @endif
                            
                        </td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">消費税合計:</td>
                        <td class="text-end">{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="6" class="text-end">合計（税込）:</td>
                        <td class="text-end">{{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>


</div>
@endsection