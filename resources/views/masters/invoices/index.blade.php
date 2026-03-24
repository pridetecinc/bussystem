@extends('layouts.app')

@section('title', '請求書マスター')

@section('content')
<!-- 修改点：减小容器左右内边距 px-2 -->
<div class="container-fluid px-2">
    <!-- 标题与新建按钮: 减小间距 mb-2，标题变小 -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0 text-primary" style="font-size: 1.1rem !important;">
            <i class="bi bi-file-text me-2"></i>請求書マスター
        </h5>
        <!-- 修改点：btn-sm, 字体变小 -->
        <a href="{{ route('masters.invoices.create', ['group_id' => request('group_id')]) }}" class="btn btn-primary btn-sm" style="font-size: 0.875rem;">
            <i class="bi bi-plus-lg"></i> 新規追加
        </a>
    </div>

    <!-- 成功/错误提示: 减小内边距 py-2，字体变小 -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 mb-2" role="alert" style="font-size: 0.875rem;">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 搜索区域: 减小间距 mb-2 -->
    <div class="mb-2">
        <div class="card shadow-sm">
            <!-- 修改点：p-2 -->
            <div class="card-body p-2">
                <form method="GET" action="{{ route('masters.invoices.index') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="group_id" value="{{ request('group_id') }}">

                    <div class="col-md-3">
                        <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">請求書番号・件名</label>
                        <input type="text" name="search" class="form-control form-control-sm" 
                               placeholder="例: INV-2026-001"
                               value="{{ request('search') }}" style="font-size: 0.875rem;">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">タイトル</label>
                        <input type="text" name="billing_title" class="form-control form-control-sm" 
                               placeholder=""
                               value="{{ request('billing_title') }}" style="font-size: 0.875rem;">
                    </div>

                    <div class="col-md-auto">
                        <!-- 修改点：btn-sm -->
                        <button type="submit" class="btn btn-outline-primary btn-sm" style="font-size: 0.75rem; padding: 0.2rem 0.4rem;">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('masters.invoices.index', ['group_id' => request('group_id')]) }}" class="btn btn-outline-secondary btn-sm ms-1" style="font-size: 0.75rem; padding: 0.2rem 0.4rem;">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 搜索结果提示: 减小间距 mb-2，字体变小 -->
    @if(request()->hasAny(['search', 'status']))
        <div class="alert alert-info mb-2 d-flex align-items-center py-2" style="font-size: 0.875rem;">
            <i class="bi bi-info-circle me-2 fs-6"></i>
            <div>
                @if(request('search'))<strong>「{{ request('search') }}」</strong> を含む @endif
                @if(request('status'))
                    ステータス：<strong>
                        @switch(request('status'))
                            @case('DRAFT') 下書き @break
                            @case('ISSUED') 発行済 @break
                            @case('PAID') 支払済 @break
                            @case('CANCELLED') キャンセル @break
                        @endswitch
                    </strong>
                @endif
                @if($invoices->count() > 0)
                    - {{ $invoices->total() }}件の結果が見つかりました
                @else
                    - 該当する請求書が見つかりませんでした
                @endif
            </div>
        </div>
    @endif

    <!-- 【新增】批量操作工具栏 (默认隐藏) -->
    <!-- 修改点：mb-2, py-1 -->
    <div id="bulk-action-bar" class="card border-primary mb-2 shadow-sm d-none" style="background-color: #f8fbff;">
        <!-- 修改点：py-1 -->
        <div class="card-body py-1 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="selected-count">0</span>
                <span class="text-primary fw-bold small" style="font-size: 0.75rem;">件選択中</span>
            </div>
            <div class="d-flex gap-1 align-items-center"> <!-- gap-2 -> gap-1 -->
                <!-- 【新增】批量销账按钮 -->
                <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btn-bulk-reconcile" title="選択した請求書を一括で消し込み" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                    <i class="bi bi-cash-coin"></i> <span class="d-none d-sm-inline">一括消し込み</span>
                </button>
                <div class="vr mx-1"></div>
                <!-- 批量锁定 -->
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-bulk-lock" title="選択した項目をロック" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                    <i class="bi bi-lock-fill"></i> <span class="d-none d-sm-inline">一括ロック</span>
                </button>
                <!-- 批量解锁 -->
                <button type="button" class="btn btn-sm btn-outline-success" id="btn-bulk-unlock" title="選択した項目のロックを解除" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                    <i class="bi bi-unlock-fill"></i> <span class="d-none d-sm-inline">一括解除</span>
                </button>

                <div class="vr mx-1"></div>
                <!-- 批量下载 PDF -->
                <form action="{{ route('masters.invoices.bulk-pdf') }}" method="POST" target="_blank" id="form-bulk-pdf" class="d-inline">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                    <div id="bulk-pdf-inputs"></div>
                    <button type="submit" class="btn btn-sm btn-outline-dark" title="選択した項目の PDF をダウンロード" style="font-size: 0.75rem; padding: 0.15rem 0.3rem;">
                        <i class="bi bi-file-earmark-pdf"></i> <span class="d-none d-sm-inline">一括 PDF</span>
                    </button>
                </form>
                
                <button type="button" class="btn btn-sm btn-link text-decoration-none p-0" id="btn-clear-selection" style="font-size: 0.75rem;">
                    クリア
                </button>
            </div>
        </div>
    </div>

    <!-- 表格区域 -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <!-- 修改点：font-size 0.875rem -->
            <table class="table table-bordered mb-0 table-striped align-middle table-compact">
                <thead class="table-secondary">
                    <tr>
                        <!-- 【新增】全选复选框 -->
                        <th class="text-center py-1" style="width: 40px; font-size: 0.75rem;">
                            <input type="checkbox" class="form-check-input" id="select-all" title="全選択">
                        </th>
                        <th class="text-center py-1" style="width: 50px; font-size: 0.75rem;">No.</th>
                        <th class="text-center py-1" style="width: 60px; font-size: 0.75rem;">ID</th>
                        <th class="text-center py-1" style="width: 120px; font-size: 0.75rem;">タイトル</th>
                        <th class="text-center py-1" style="width: 130px; font-size: 0.75rem;">請求書番号</th>
                        <th class="text-center py-1" style="width: 120px; font-size: 0.75rem;">請求先</th>
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">請求日</th>
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">支払期日</th>
                        <th class="text-center py-1" style="width: 80px; font-size: 0.75rem;">通貨</th>
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">合計金額</th>
                        <th class="text-center py-1" style="width: 100px; font-size: 0.75rem;">余额</th>
                        <th class="text-center py-1" style="width: 80px; font-size: 0.75rem;">タイプ</th>
                        <th class="text-center py-1" style="width: 60px; font-size: 0.75rem;" title="データロック状態">ロック</th>
                        <th class="text-center py-1" style="width: 140px; font-size: 0.75rem;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <!-- 【新增】行复选框 -->
                        <td class="text-center py-1">
                            <input type="checkbox" class="form-check-input invoice-checkbox" 
                                value="{{ $invoice->id }}"
                                data-locked="{{ $invoice->is_locked ? 1 : 0 }}"
                                data-customer-id="{{ $invoice->agency_id }}"
                                data-invoice-no="{{ $invoice->invoice_number }}"
                                data-currency-code="{{ $invoice->currency_code }}"
                                data-customer-name="{{ $invoice->agency->agency_name ?? ''}}"
                                data-request-amount="{{ number_format($invoice->total_amount, 2, '.', '') }}" 
                                data-balance-amount="{{ number_format($invoice->total_amount - $invoice->paid_amount, 2, '.', '') }}">
                        </td>
                        <td class="text-center fw-bold text-muted py-1" style="font-size: 0.8rem;">
                            {{ ($invoices->currentPage() - 1) * $invoices->perPage() + $loop->iteration }}
                        </td>
                        <td class="text-center text-muted small py-1" style="font-size: 0.75rem;">{{ $invoice->id }}</td>
                        <td class="text-center fw-bold text-primary py-1" style="font-size: 0.875rem;">{{ $invoice->billing_title }}</td>
                        <td class="text-center fw-bold text-primary py-1" style="font-size: 0.875rem;">{{ $invoice->invoice_number }}</td>
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->agency->agency_name ?? ''}}</td>
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}</td>
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') }}</td>
                        <td class="text-center py-1" style="font-size: 0.875rem;">{{ $invoice->currency_code }}</td>
                        <td class="text-center font-monospace py-1" style="font-size: 0.875rem;">
                            {{ number_format($invoice->total_amount, 2) }}
                        </td>
                        <td class="text-center font-monospace py-1" style="font-size: 0.875rem;">
                            {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}
                        </td>
                        <td class="text-center font-monospace py-1" style="font-size: 0.875rem;">
                            {{ $invoice->type == 1 ? '正式' : '臨時' }}
                        </td>
                        
                        <!-- 状态列：锁开关按钮 -->
                        <td class="text-center py-1">
                            <!-- 修改点：宽高 30px (原 36px) -->
                            <button type="button" 
                                    class="btn btn-sm border-0 toggle-lock-btn" 
                                    data-id="{{ $invoice->id }}" 
                                    data-locked="{{ $invoice->is_locked ? 1 : 0 }}"
                                    title="{{ $invoice->is_locked ? 'ロックを解除' : 'ロックを掛ける' }}"
                                    style="width: 30px; height: 30px; border-radius: 50%; transition: all 0.2s; display:inline-flex; align-items:center; justify-content:center; padding: 0;">
                                
                                @if($invoice->is_locked)
                                    <i class="bi bi-lock-fill text-danger" style="font-size: 0.9rem;"></i>
                                @else
                                    <i class="bi bi-unlock-fill text-success" style="font-size: 0.9rem;"></i>
                                @endif
                            </button>
                        </td>

                        <td class="py-1">
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- 【新增】单行销账按钮 -->
                                @if($invoice->total_amount > $invoice->paid_amount)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning btn-single-reconcile" 
                                            data-id="{{ $invoice->id }}"
                                            title="消し込み (入金登録)"
                                            style="padding: 0.1rem 0.3rem;">
                                        <i class="bi bi-cash-coin" style="font-size: 0.8rem;"></i>
                                    </button>
                                @else
                                    <!-- 已付清状态 -->
                                    <button type="button" 
                                            class="btn btn-sm btn-light text-muted" 
                                            disabled
                                            title="全額入金済み"
                                            style="padding: 0.1rem 0.3rem;">
                                        <i class="bi bi-check-circle-fill" style="font-size: 0.8rem;"></i>
                                    </button>
                                @endif


                                <!-- PDF 下载按钮 -->
                                <a href="{{ route('masters.invoices.pdf', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-success" 
                                   title="PDF ダウンロード"
                                   target="_blank"
                                   style="padding: 0.1rem 0.3rem;">
                                    <i class="bi bi-file-earmark-pdf" style="font-size: 0.8rem;"></i>
                                </a>

                                <!-- 详细 -->
                                <a href="{{ route('masters.invoices.show', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細"
                                   style="padding: 0.1rem 0.3rem;">
                                    <i class="bi bi-eye" style="font-size: 0.8rem;"></i>
                                </a>
                                
                                @if(!$invoice->is_locked)
                                <a href="{{ route('masters.invoices.edit', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-primary {{ $invoice->is_locked ? 'disabled' : '' }}" 
                                   title="{{ $invoice->is_locked ? 'ロック中です' : '編集' }}"
                                   style="padding: 0.1rem 0.3rem;">
                                    <i class="bi bi-pencil" style="font-size: 0.8rem;"></i>
                                </a>
                                @endif
                                
                                <!-- 删除 -->
                                 @if( !$invoice->is_locked && $invoice->total_amount == $invoice->paid_amount )
                                <form action="{{ route('masters.invoices.destroy', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                    method="POST" 
                                    class="d-inline" 
                                    onsubmit="return checkAndDelete({{ $invoice->type }}, '{{ $invoice->invoice_number }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="削除" style="padding: 0.1rem 0.3rem;">
                                        <i class="bi bi-trash" style="font-size: 0.8rem;"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="14" class="text-center py-4">
                            @if(request()->hasAny(['search', 'status']))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold" style="font-size: 0.9rem;">検索条件に一致する請求書が見つかりませんでした</p>
                                    <p class="small">検索条件を変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-file-text display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold" style="font-size: 0.9rem;">請求書が登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の請求書を作成してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 分页区域 -->
    @if($invoices->hasPages() || $invoices->total() > 0)
        <div class="mt-3">
            <!-- 使用 flex 容器实现整体居中 -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                
                <!-- 1. 左侧：行数选择器 -->
                <div class="d-flex align-items-center">
                    <label for="per_page_select" class="form-label small text-muted mb-0 me-2" >
                        表示件数:
                    </label>
                    <select id="per_page_select" class="form-select form-select-sm" style="font-size: 0.75rem;min-width: 80px;">
                        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 行</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 行</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 行</option>
                    </select>
                </div>

                <!-- 2. 中间：分页链接 -->
                <nav aria-label="Page navigation">
                    <!-- 关键：添加 pagination-sm -->
                    <ul class="pagination pagination-sm mb-0">
                        <!-- 上一页 -->
                        <li class="page-item {{ $invoices->onFirstPage() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $invoices->previousPageUrl() }}" aria-label="Previous" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $invoices->currentPage();
                            $last = $invoices->lastPage();
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $invoices->url(1) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                        @endif

                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $invoices->url($i) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $i }}</a>
                            </li>
                        @endfor

                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $invoices->url($last) }}" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">{{ $last }}</a>
                            </li>
                        @endif

                        <!-- 下一页 -->
                        <li class="page-item {{ !$invoices->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $invoices->nextPageUrl() }}" aria-label="Next" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 3. 底部：统计信息 -->
            <div class="text-center text-muted mt-2" style="font-size: 0.75rem;">
                表示中：{{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} / 全 {{ $invoices->total() }} 件
            </div>
        </div>
    @endif
</div>

<!-- Toast 容器 -->
<div class="position-fixed bottom-0 end-0 p-2" style="z-index: 11">
    <!-- 修改点：p-2, 字体变小 -->
    <div id="lockToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true" style="font-size: 0.875rem;">
        <div class="d-flex">
            <div class="toast-body" id="lockToastMessage">
                <!-- 消息内容 -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto btn-sm" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- 引入批量销账模态框组件 --}}
@include('masters.invoices.components.bulk-reconcile-modal')

<!-- 【新增/修改】AJAX 脚本处理锁状态切换及批量操作 -->
<script>
function checkAndDelete(type, number) {
    if (type != 2) {
        alert('請求書タイプを臨時にしてから削除してください。');
        return false;
    }
    const message = '本当に請求書「' + number + '」を削除しますか？\nこの操作は元に戻せません。';
    return confirm(message);
}

document.addEventListener('DOMContentLoaded', function () {
    // 1. 分页行数切换逻辑
    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const newPerPage = this.value;
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', newPerPage);
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        });
    }

    // 2. 复选框联动与批量操作栏逻辑
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.invoice-checkbox');
    const bulkActionBar = document.getElementById('bulk-action-bar');
    const selectedCountBadge = document.getElementById('selected-count');
    
    if (!selectAllCheckbox) {
        console.error('Error: "select-all" checkbox not found!');
        return;
    }

    function updateBulkActionBar() {
        const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (selectedCountBadge) selectedCountBadge.textContent = count;

        if (count > 0) {
            if(bulkActionBar) bulkActionBar.classList.remove('d-none');
            
            const pdfContainer = document.getElementById('bulk-pdf-inputs');
            if(pdfContainer) {
                pdfContainer.innerHTML = '';
                checkedBoxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'invoice_ids[]';
                    input.value = cb.value;
                    pdfContainer.appendChild(input);
                });
            }
        } else {
            if(bulkActionBar) bulkActionBar.classList.add('d-none');
        }
    }

    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        rowCheckboxes.forEach(cb => {
            cb.checked = isChecked;
            cb.dispatchEvent(new Event('change')); 
        });
        updateBulkActionBar();
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            if (selectAllCheckbox) {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateBulkActionBar();
        });
    });

    const btnClearSelection = document.getElementById('btn-clear-selection');
    if(btnClearSelection) {
        btnClearSelection.addEventListener('click', function() {
            rowCheckboxes.forEach(cb => cb.checked = false);
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
            updateBulkActionBar();
        });
    }

    // 3. 批量锁定/解锁 AJAX 逻辑
    const btnBulkLock = document.getElementById('btn-bulk-lock');
    const btnBulkUnlock = document.getElementById('btn-bulk-unlock');
    const toastEl = document.getElementById('lockToast');
    let toast = null;
    
    if(toastEl && window.bootstrap) {
        toast = new bootstrap.Toast(toastEl, { delay: 800 });
    }

    async function processBulkAction(ids, lockState) {
        if(btnBulkLock) btnBulkLock.disabled = true;
        if(btnBulkUnlock) btnBulkUnlock.disabled = true;

        try {
            const response = await fetch('/masters/invoices/bulk-toggle-lock', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ invoice_ids: ids, locked: lockState ? 1 : 0 })
            });
            const data = await response.json();
            if(data.success) {
                if(toast) {
                    document.getElementById('lockToastMessage').innerHTML = lockState ? 'ロック完了' : 'ロック解除完了';
                    toast.show();
                }
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
                resetButtons();
            }
        } catch(e) {
            console.error(e);
            alert('Network error');
            resetButtons();
        }

        function resetButtons() {
            if(btnBulkLock) btnBulkLock.disabled = false;
            if(btnBulkUnlock) btnBulkUnlock.disabled = false;
        }
    }

    if(btnBulkLock) {
        btnBulkLock.addEventListener('click', async function() {
            const ids = Array.from(document.querySelectorAll('.invoice-checkbox:checked')).map(cb => cb.value);
            if(ids.length === 0) return;
            if(!confirm(`選択した ${ids.length} 件をロックしますか？`)) return;
            await processBulkAction(ids, true);
        });
    }
    
    if(btnBulkUnlock) {
        btnBulkUnlock.addEventListener('click', async function() {
            const ids = Array.from(document.querySelectorAll('.invoice-checkbox:checked')).map(cb => cb.value);
            if(ids.length === 0) return;
            if(!confirm(`選択した ${ids.length} 件のロックを解除しますか？`)) return;
            await processBulkAction(ids, false);
        });
    }
    
    // 4. 单行锁定逻辑
    document.querySelectorAll('.toggle-lock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const current = parseInt(this.dataset.locked);
            if(!confirm('操作しますか？')) return;
            
            fetch(`/masters/invoices/${id}/toggle-lock`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ locked: current ? 0 : 1 })
            }).then(() => window.location.reload());
        });
    });
});
</script>
<style>
/* 紧凑表格样式 */
.table-compact td,
.table-compact th {
    padding-top: 0.2rem !important;    
    padding-bottom: 0.2rem !important; 
    vertical-align: middle;
}

/* 优化操作按钮 */
.table-compact .btn {
    padding: 0.1rem 0.25rem !important;
    font-size: 0.75rem !important;
    line-height: 1.2;
}

/* 批量操作栏动画 */
#bulk-action-bar {
    transition: all 0.3s ease-in-out;
}

/* 表单控件统一变小 */
.form-control, .form-select {
    font-size: 0.875rem !important;
    /* 上下保持 0.2rem，左右增加到 0.6rem 以容纳箭头 */
    padding: 0.2rem 0.6rem !important; 
}

/* 标签统一变小 */
.form-label {
    font-size: 0.75rem !important;
    /* 不再强制 margin-bottom，让 Bootstrap 默认处理或依赖具体上下文 */
}

.d-flex.align-items-center label,
.d-flex.align-items-center .form-label {
    margin-bottom: 0 !important;
    white-space: nowrap;
}
</style>
@endsection