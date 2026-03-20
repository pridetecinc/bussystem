@extends('layouts.app')

@section('title', '請求書詳細')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.invoices.index', ['group_id' => $invoice->group_id]) }}">請求書管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">請求書詳細</li>
                </ol>
            </nav>

            <!-- Header Actions -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0 text-primary"><i class="bi bi-file-earmark-text"></i> 請求書詳細</h4>
                <div>
                    <a href="{{ route('masters.invoices.edit', [$invoice->id, 'group_id' => $invoice->group_id]) }}" class="btn btn-primary">
                        <i class="bi bi-pencil-square"></i> 編集
                    </a>
                    <a href="{{ route('masters.invoices.index', ['group_id' => $invoice->group_id]) }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> 一覧に戻る
                    </a>
                </div>
            </div>

            <!-- ================= 第一部分：基本情報 (只读模式) ================= -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> 請求書基本情報</h5>
                </div>
                <div class="card-body p-3">
                    
                    <!-- Row 1: 左侧代理店 + 右侧 8 字段 -->
                    <div class="row g-3 mb-3 align-items-start">
                        
                        <!-- 左侧：代理店 & Textarea -->
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center mb-2">
                                <label class="fw-bold me-2 mb-0 text-muted flex-shrink-0 text-nowrap" style="min-width: 80px;">
                                    <i class="bi bi-building"></i> 代理店
                                </label>
                                <span class="form-control-plaintext fw-bold">{{ $invoice->agency->agency_name ?? '—' }}</span>
                            </div>
                            
                            <textarea class="form-control bg-light" rows="3" readonly>{{ $invoice->agency_detail ?? '—' }}</textarea>
                        </div>

                        <!-- 右侧：8 个字段 (2 行 x 4 列) -->
                        <div class="col-lg-6">
                            <div class="row g-2">
                                <!-- 第一行 -->
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">請求担当</label>
                                    <div class="form-control-plaintext fw-normal">{{ $invoice->staff->name ?? '—' }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">税込/税別</label>
                                    <div>
                                        <span class="badge {{ $invoice->tax_mode == 1 ? 'bg-primary' : 'bg-outline-secondary text-dark border' }}">
                                            {{ $invoice->tax_mode == 1 ? '税込' : '税別' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">言語</label>
                                    <div>
                                        <span class="badge {{ $invoice->language == 1 ? 'bg-info text-dark' : 'bg-outline-secondary text-dark border' }}">
                                            {{ $invoice->language == 1 ? '日本語' : '英語' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">タイプ</label>
                                    <div>
                                        <span class="badge {{ $invoice->type == 1 ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $invoice->type == 1 ? '正式' : '見積' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 第二行 -->
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">通貨</label>
                                    <div class="form-control-plaintext fw-bold">{{ $invoice->currency_code ?? '—' }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">請求日</label>
                                    <div class="form-control-plaintext">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">運行日</label>
                                    <div class="form-control-plaintext">{{ $invoice->operation_date ? \Carbon\Carbon::parse($invoice->operation_date)->format('Y/m/d') : '—' }}</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label small mb-1 text-muted">予約 ID</label>
                                    <div class="form-control-plaintext">{{ $invoice->reservation_id ?? '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3 text-muted">

                    <!-- Row 2: 标题、支付日、银行、锁 -->
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted">タイトル</label>
                            <div class="form-control-plaintext fs-6">{{ $invoice->billing_title ?? '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted">支払指定日</label>
                            <div class="form-control-plaintext">{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') : '—' }}</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold text-muted">入金銀行</label>
                            <div class="form-control-plaintext">{{ $invoice->bank->bank_name ?? '—' }}</div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold d-block text-center text-muted">ロック</label>
                            <div class="text-center mt-2" style="font-size: 2rem;">
                                @if($invoice->is_locked)
                                    <i class="bi bi-lock-fill text-danger" title="ロック中"></i>
                                @else
                                    <i class="bi bi-unlock-fill text-success" title="ロック解除"></i>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Row 3: 備考 -->
                    <div class="row">
                        <div class="col-12">
                            <label class="form-label fw-bold text-muted">備考</label>
                            <textarea class="form-control bg-light" rows="2" readonly>{{ $invoice->notes ?? '—' }}</textarea>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ================= 第二部分：請求明細 (只读模式 + 统计) ================= -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-task"></i> 請求明細</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;" class="text-center">No.</th>
                                    <th>内容</th>
                                    <th style="width: 100px;" class="text-end">数量</th>
                                    <th style="width: 120px;" class="text-end">単価</th>
                                    <th style="width: 100px;" class="text-end">税率 (%)</th>
                                    <th style="width: 130px;" class="text-end">小計</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                        <td class="align-middle">{{ $item->description }}</td>
                                        <td class="text-end align-middle">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-end align-middle">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end align-middle">
                                            @if($item->tax_rate == -1)
                                                <span class="badge bg-secondary">免税</span>
                                            @elseif($item->tax_rate == -2)
                                                <span class="badge bg-secondary">非課税</span>
                                            @else
                                                {{ number_format($item->tax_rate) }}%
                                            @endif
                                        </td>
                                        <td class="text-end align-middle fw-bold">{{ number_format($item->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">明細がありません。</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end py-2">小計:</td>
                                    <td class="text-end py-2">
                                        {{-- 逻辑与原来保持一致 --}}
                                        {{ number_format($invoice->tax_mode == 1 ? $invoice->total_amount : $invoice->subtotal_amount, 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-end py-2">消費税合計:</td>
                                    <td class="text-end py-2 text-primary">{{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                <tr class="table-primary">
                                    <td colspan="5" class="text-end py-3 fs-6">合計（税込）:</td>
                                    <td class="text-end py-3 fs-6 text-primary">{{ number_format($invoice->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 底部信息 -->
            <div class="row text-muted small mb-5">
                <div class="col-md-6">
                    <p class="mb-1"><strong>登録日時:</strong> {{ $invoice->created_at ? $invoice->created_at->format('Y/m/d H:i') : '—' }}</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><strong>最終更新日時:</strong> {{ $invoice->updated_at ? $invoice->updated_at->format('Y/m/d H:i') : '—' }}</p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
/* 只读表单的样式微调 */
.form-control-plaintext {
    padding-top: 0.25rem;
    padding-bottom: 0.25rem;
    margin-bottom: 0; 
    line-height: 1.5;
}
.bg-light {
    background-color: #f8f9fa !important;
}
/* 表格行高优化 */
#itemsTable tbody tr td {
    vertical-align: middle;
    padding: 0.5rem;
}
</style>
@endsection