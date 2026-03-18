@extends('layouts.app')

@section('title', '請求書マスター')

@section('content')
<div class="container-fluid">
    <!-- 标题与新建按钮 -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-file-text me-2 text-primary"></i>請求書マスター</h4>
        <a href="{{ route('masters.invoices.create', ['group_id' => request('group_id')]) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> 新規追加
        </a>
    </div>

    <!-- 成功/错误提示 -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- 搜索区域 -->
    <div class="mb-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="GET" action="{{ route('masters.invoices.index') }}" class="row g-2 align-items-end">
                    <input type="hidden" name="group_id" value="{{ request('group_id') }}">

                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">請求書番号・件名</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="例: INV-2026-001"
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">账单标题</label>
                        <input type="text" name="billing_title" class="form-control" 
                               placeholder=""
                               value="{{ request('billing_title') }}">
                    </div>

                    <div class="col-md-auto">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-search"></i> 検索
                        </button>
                        @if(request()->hasAny(['search', 'status']))
                            <a href="{{ route('masters.invoices.index', ['group_id' => request('group_id')]) }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> クリア
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request()->hasAny(['search', 'status']))
        <div class="alert alert-info mb-3 d-flex align-items-center">
            <i class="bi bi-info-circle me-2 fs-5"></i>
            <div>
                @if(request('search'))<strong>「{{ request('search') }}」</strong> を含む @endif
                @if(request('status'))
                    ステータス: <strong>
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
    <div id="bulk-action-bar" class="card border-primary mb-3 shadow-sm d-none" style="background-color: #f8fbff;">
        <div class="card-body py-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <span class="badge bg-primary me-2" id="selected-count">0</span>
                <span class="text-primary fw-bold small">件選択中</span>
            </div>
            <div class="d-flex gap-2">
                <!-- 【新增】批量销账按钮 (放在这里) -->
                <button type="button" class="btn btn-sm btn-primary shadow-sm" id="btn-bulk-reconcile" title="選択した請求書を一括で消し込み">
                    <i class="bi bi-cash-coin"></i> 一括消し込み
                </button>
                <div class="vr mx-1"></div>
                <!-- 批量锁定 -->
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-bulk-lock" title="選択した項目をロック">
                    <i class="bi bi-lock-fill"></i> 一括ロック
                </button>
                <!-- 批量解锁 -->
                <button type="button" class="btn btn-sm btn-outline-success" id="btn-bulk-unlock" title="選択した項目のロックを解除">
                    <i class="bi bi-unlock-fill"></i> 一括ロック解除
                </button>

            
                <div class="vr mx-1"></div>
                <!-- 批量下载 PDF (使用表单提交以支持多文件打包或重定向) -->
                <form action="{{ route('masters.invoices.bulk-pdf') }}" method="POST" target="_blank" id="form-bulk-pdf" class="d-inline">
                    @csrf
                    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                    <div id="bulk-pdf-inputs"></div>
                    <button type="submit" class="btn btn-sm btn-outline-dark" title="選択した項目のPDFをダウンロード">
                        <i class="bi bi-file-earmark-pdf"></i> 一括PDFダウンロード
                    </button>
                </form>
                
                <button type="button" class="btn btn-sm btn-link text-decoration-none" id="btn-clear-selection">
                    クリア
                </button>
            </div>
        </div>
    </div>

    <!-- 表格区域 -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped align-middle table-compact">
                <thead class="table-secondary">
                    <tr>
                        <!-- 【新增】全选复选框 -->
                        <th class="text-center" style="width: 50px;">
                            <input type="checkbox" class="form-check-input" id="select-all" title="全選択">
                        </th>
                        <th class="text-center" style="width: 80px;">ID</th>
                        <th class="text-center" style="width: 150px;">账单标题</th>
                        <th class="text-center" style="width: 140px;">請求書番号</th>
                        <th class="text-center" style="width: 120px;">顧客</th>
                        <th class="text-center" style="width: 120px;">請求日</th>
                        <th class="text-center" style="width: 120px;">支払期日</th>
                        <th class="text-center" style="width: 100px;">通貨</th>
                        <th class="text-center" style="width: 120px;">合計金額</th>
                        <th class="text-center" style="width: 120px;">余额</th>
                        <th class="text-center" style="width: 100px;">属性</th>
                        <th class="text-center" style="width: 100px;" title="データロック状態">ロック</th>
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
                        <!-- 【新增】行复选框 -->
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input invoice-checkbox" 
                                   value="{{ $invoice->id }}" 
                                   data-locked="{{ $invoice->is_locked ? 1 : 0 }}">
                        </td>
                        <td class="text-center text-muted small">{{ $invoice->id }}</td>
                        <td class="text-center fw-bold text-primary">{{ $invoice->billing_title }}</td>
                        <td class="text-center fw-bold text-primary">{{ $invoice->invoice_number }}</td>
                        <td class="text-center">
                            @if($invoice->customer)
                                <span class="fw-medium">{{ $invoice->customer->name }}</span>
                            @else
                                <span class="text-muted">未設定</span>
                            @endif
                        </td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('Y/m/d') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y/m/d') }}</td>
                        <td class="text-center">{{ $invoice->currency_code }}</td>
                        <td class="text-center font-monospace">
                            {{ number_format($invoice->total_amount, 2) }}
                        </td>
                        <td class="text-center font-monospace">
                            {{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}
                        </td>
                        <td class="text-center font-monospace">
                            {{ $invoice->type == 1 ? '正式' : '临时' }}
                        </td>
                        
                        <!-- 状态列：锁开关按钮 -->
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-sm border-0 toggle-lock-btn" 
                                    data-id="{{ $invoice->id }}" 
                                    data-locked="{{ $invoice->is_locked ? 1 : 0 }}"
                                    title="{{ $invoice->is_locked ? 'ロックを解除' : 'ロックを掛ける' }}"
                                    style="width: 36px; height: 36px; border-radius: 50%; transition: all 0.2s; display:inline-flex; align-items:center; justify-content:center;">
                                
                                @if($invoice->is_locked)
                                    <i class="bi bi-lock-fill text-danger fs-6"></i>
                                @else
                                    <i class="bi bi-unlock-fill text-success fs-6"></i>
                                @endif
                            </button>
                        </td>

                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- 【新增】单行销账按钮 -->
                                @if($invoice->total_amount > $invoice->paid_amount)
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-warning btn-single-reconcile" 
                                            data-id="{{ $invoice->id }}"
                                            title="消し込み (入金登録)">
                                        <i class="bi bi-cash-coin"></i>
                                    </button>
                                @else
                                    <!-- 已付清状态 -->
                                    <button type="button" 
                                            class="btn btn-sm btn-light text-muted" 
                                            disabled
                                            title="全額入金済み">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </button>
                                @endif


                                <!-- PDF 下载按钮 -->
                                <a href="{{ route('masters.invoices.pdf', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-success" 
                                   title="PDF ダウンロード"
                                   target="_blank">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>

                                <!-- 详细 -->
                                <a href="{{ route('masters.invoices.show', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if(!$invoice->is_locked)
                                <a href="{{ route('masters.invoices.edit', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                   class="btn btn-sm btn-outline-primary {{ $invoice->is_locked ? 'disabled' : '' }}" 
                                   title="{{ $invoice->is_locked ? 'ロック中です' : '編集' }}">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @endif
                                
                                <!-- 删除 -->
                                <form action="{{ route('masters.invoices.destroy', ['invoice' => $invoice, 'group_id' => request('group_id')]) }}" 
                                      method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirm('本当に請求書「{{ $invoice->invoice_number }}」を削除しますか？\nこの操作は元に戻せません。')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="削除">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            @if(request()->hasAny(['search', 'status']))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">検索条件に一致する請求書が見つかりませんでした</p>
                                    <p class="small">検索条件を変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-file-text display-6 mb-2 d-block"></i>
                                    <p class="mb-0 fw-bold">請求書が登録されていません</p>
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
    
    <!-- 分页区域 (保持不变，但需注意如果使用了批量操作，翻页后选中状态会丢失，这是无状态HTTP的正常行为) -->
    @if($invoices->hasPages() || $invoices->total() > 0)
        <div class="mt-4">
            <!-- 使用 flex 容器实现整体居中，并确保内部元素垂直居中对齐 -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
                
                <!-- 1. 左侧：行数选择器 -->
                <div class="d-flex align-items-center">
                    <label for="per_page_select" class="form-label small text-muted mb-0 me-2">
                        表示件数:
                    </label>
                    <!-- 关键：添加 form-select-sm -->
                    <select id="per_page_select" class="form-select form-select-sm" style="width: auto;">
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
                            <a class="page-link" href="{{ $invoices->previousPageUrl() }}" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        @php
                            $current = $invoices->currentPage();
                            $last = $invoices->lastPage();
                            // 简单的分页逻辑：显示当前页附近
                            $start = max(1, $current - 2);
                            $end = min($last, $current + 2);
                        @endphp

                        <!-- 第一页 (如果不在范围内) -->
                        @if($start > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $invoices->url(1) }}">1</a>
                            </li>
                            @if($start > 2)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endif

                        <!-- 循环页码 -->
                        @for($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $i == $current ? 'active' : '' }}">
                                <a class="page-link" href="{{ $invoices->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        <!-- 最后一页 (如果不在范围内) -->
                        @if($end < $last)
                            @if($end < $last - 1)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                            <li class="page-item">
                                <a class="page-link" href="{{ $invoices->url($last) }}">{{ $last }}</a>
                            </li>
                        @endif

                        <!-- 下一页 -->
                        <li class="page-item {{ !$invoices->hasMorePages() ? 'disabled' : '' }}">
                            <a class="page-link" href="{{ $invoices->nextPageUrl() }}" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- 3. 底部：统计信息 -->
            <div class="text-center text-muted small mt-2">
                表示中：{{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} / 全 {{ $invoices->total() }} 件
            </div>
        </div>
    @endif
</div>

<!-- Toast 容器 (用于单行操作反馈) -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="lockToast" class="toast align-items-center text-white bg-dark border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body" id="lockToastMessage">
                <!-- 消息内容 -->
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- 引入批量销账模态框组件 --}}
@include('masters.invoices.components.bulk-reconcile-modal')

<!-- 【新增/修改】AJAX 脚本处理锁状态切换及批量操作 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const perPageSelect = document.getElementById('per_page_select');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const newPerPage = this.value;
            const url = new URL(window.location.href);
            
            // 设置新的 per_page 参数
            url.searchParams.set('per_page', newPerPage);
            
            // 重置到第一页，避免页数超出新每页行数的范围
            url.searchParams.set('page', '1');
            
            // 跳转
            window.location.href = url.toString();
        });
    }

    // 1. 获取元素并增加调试日志
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.invoice-checkbox');
    const bulkActionBar = document.getElementById('bulk-action-bar');
    const selectedCountBadge = document.getElementById('selected-count');
    
    if (!selectAllCheckbox) {
        console.error('Error: "select-all" checkbox not found! Check the ID in the <th>.');
        return;
    }
    if (rowCheckboxes.length === 0) {
        console.warn('Warning: No ".invoice-checkbox" found. Bulk actions will not work until data exists.');
    }

    console.log(`Found 1 header checkbox and ${rowCheckboxes.length} row checkboxes.`);

    // 2. 定义更新函数
    function updateBulkActionBar() {
        const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (selectedCountBadge) selectedCountBadge.textContent = count;

        if (count > 0) {
            if(bulkActionBar) bulkActionBar.classList.remove('d-none');
            
            // 更新 PDF 表单的 hidden inputs
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

    // 3. 绑定全选事件 (关键修复点)
    selectAllCheckbox.addEventListener('change', function() {
        const isChecked = this.checked;
        console.log('Header checkbox changed to:', isChecked);
        
        rowCheckboxes.forEach(cb => {
            cb.checked = isChecked;
            // 手动触发 change 事件，确保其他监听器（如果有）也能响应
            cb.dispatchEvent(new Event('change')); 
        });
        
        updateBulkActionBar();
    });

    // 4. 绑定行复选框事件
    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            // 如果手动取消了一个，取消全选框
            if (!this.checked && selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }
            // 如果所有都选中了，勾选全选框
            if (selectAllCheckbox) {
                const allChecked = Array.from(rowCheckboxes).every(c => c.checked);
                selectAllCheckbox.checked = allChecked;
            }
            updateBulkActionBar();
        });
    });

    // 5. 绑定清除按钮
    const btnClearSelection = document.getElementById('btn-clear-selection');
    if(btnClearSelection) {
        btnClearSelection.addEventListener('click', function() {
            rowCheckboxes.forEach(cb => cb.checked = false);
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
            updateBulkActionBar();
        });
    }

    // --- 原有的批量操作和单行锁定逻辑保持不变 (此处省略以节省篇幅，请保留你原代码中的后续逻辑) ---
    // 请确保将原本 script 标签中 updateBulkActionBar 之后的代码 (btnBulkLock 等逻辑) 粘贴在这里
    // 为了简洁，我只展示了修复全选的核心部分。
    
    // [重要] 如果你之前的代码里有 btnBulkLock 等逻辑，请把它们粘贴在下方
    // 这里为了演示完整性，我把核心逻辑补全：
    
    const btnBulkLock = document.getElementById('btn-bulk-lock');
    const btnBulkUnlock = document.getElementById('btn-bulk-unlock');
    const toastEl = document.getElementById('lockToast');
    let toast = null;
    if(toastEl && window.bootstrap) {
        toast = new bootstrap.Toast(toastEl, { delay: 800 });
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

    async function processBulkAction(ids, lockState) {
        // 禁用按钮防止重复点击
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
                if(btnBulkLock) btnBulkLock.disabled = false;
                if(btnBulkUnlock) btnBulkUnlock.disabled = false;
            }
        } catch(e) {
            console.error(e);
            alert('Network error');
            if(btnBulkLock) btnBulkLock.disabled = false;
            if(btnBulkUnlock) btnBulkUnlock.disabled = false;
        }
    }
    
    // 单行锁定逻辑 (保持原有逻辑，确保选择器正确)
    document.querySelectorAll('.toggle-lock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // ... (保留你原有的单行锁定代码) ...
            // 为节省空间，此处不重复，请使用你原代码中的这部分
            const id = this.dataset.id;
            const current = parseInt(this.dataset.locked);
            if(!confirm('操作しますか？')) return;
            
            // 简单模拟刷新，实际请用你原来的 fetch 逻辑
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
    padding-top: 0.25rem;    
    padding-bottom: 0.25rem; 
    vertical-align: middle;
}

/* 优化操作按钮 */
.table-compact .btn {
    padding: 0.15rem 0.35rem;
    font-size: 0.85rem;
}

/* 批量操作栏动画 */
#bulk-action-bar {
    transition: all 0.3s ease-in-out;
}
</style>
@endsection