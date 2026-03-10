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

    <!-- 表格区域 -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-center" style="width: 80px;">ID</th>
                        <th class="text-center" style="width: 150px;">账单标题</th>
                        <th class="text-center" style="width: 140px;">請求書番号</th>
                        <th class="text-center" style="width: 120px;">顧客</th>
                        <th class="text-center" style="width: 120px;">請求日</th>
                        <th class="text-center" style="width: 120px;">支払期日</th>
                        <th class="text-center" style="width: 100px;">通貨</th>
                        <th class="text-center" style="width: 120px;">合計金額</th>
                        
                        <!-- 【修改点 1】表头改为“ロック” (锁定) -->
                        <th class="text-center" style="width: 100px;" title="データロック状態">ロック</th>
                        
                        <th class="text-center" style="width: 160px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                    <tr>
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
                        
                        <!-- 【修改点 2】状态列改为锁开关按钮 -->
                        <td class="text-center">
                            <button type="button" 
                                    class="btn btn-sm border-0 toggle-lock-btn" 
                                    data-id="{{ $invoice->id }}" 
                                    data-locked="{{ $invoice->is_locked ? 1 : 0 }}"
                                    title="{{ $invoice->is_locked ? 'ロックを解除' : 'ロックを掛ける' }}"
                                    style="width: 40px; height: 40px; border-radius: 50%; transition: all 0.2s;">
                                
                                @if($invoice->is_locked)
                                    <!-- 已锁定：显示闭合的锁 (红色) -->
                                    <i class="bi bi-lock-fill text-danger fs-5"></i>
                                @else
                                    <!-- 未锁定：显示打开的锁 (绿色/灰色) -->
                                    <i class="bi bi-unlock-fill text-success fs-5"></i>
                                @endif
                            </button>
                        </td>

                        <td>
                            <div class="d-flex gap-1 justify-content-center">
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
                                <!-- 编辑 (如果已锁定，可以禁用编辑按钮以防误触，可选) -->
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
                        <td colspan="10" class="text-center py-5">
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
    
    <!-- 分页区域 (保持不变) -->
    @if($invoices->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $invoices->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $invoices->previousPageUrl() }}&group_id={{ request('group_id') }}">
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
                            <a class="page-link" href="{{ $invoices->url(1) }}&group_id={{ request('group_id') }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $invoices->url($i) }}&group_id={{ request('group_id') }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $invoices->url($last) }}&group_id={{ request('group_id') }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$invoices->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $invoices->nextPageUrl() }}&group_id={{ request('group_id') }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中：{{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} / 全 {{ $invoices->total() }} 件
            </div>
        </div>
    @endif
</div>

<!-- 【新增】AJAX 脚本处理锁状态切换 -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lockButtons = document.querySelectorAll('.toggle-lock-btn');
    
    // 初始化 Bootstrap Toast
    const toastEl = document.getElementById('lockToast');
    const toastBody = document.getElementById('lockToastMessage');
    let toast;
    if(toastEl) {
        toast = new bootstrap.Toast(toastEl, { delay: 800 }); // 设置短一点的延迟，因为马上要刷新
    }

    lockButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const invoiceId = this.dataset.id;
            const currentLocked = parseInt(this.dataset.locked); 
            const icon = this.querySelector('i');
            const row = this.closest('tr');
            // 尝试获取发票号用于确认框，如果找不到就用ID
            const invoiceNumberCell = row.querySelector('.fw-bold.text-primary') || row.querySelector('td:nth-child(2)'); 
            const invoiceNumber = invoiceNumberCell ? invoiceNumberCell.innerText.trim() : '#' + invoiceId;

            // 1. 操作前确认
            const actionText = currentLocked === 1 ? 'ロックを解除' : 'ロックを掛ける';
            
            if (!confirm(`請求書「${invoiceNumber}」の${actionText}を行いますか？\n\n操作後、ページを再読み込みします。`)) {
                return; 
            }

            // 2. 视觉反馈：加载中
            const originalIconClass = icon.className;
            icon.classList.add('bi-hourglass-split'); 
            icon.classList.remove('bi-lock-fill', 'bi-unlock-fill');
            this.disabled = true;
            this.style.opacity = '0.6';

            const url = `/masters/invoices/${invoiceId}/toggle-lock`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ locked: currentLocked === 1 ? 0 : 1 })
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // 准备提示信息
                    let message = '';
                    if (data.is_locked == 1) {
                        message = '<i class="bi bi-lock-fill me-2"></i>ロックしました (页面将刷新)';
                    } else {
                        message = '<i class="bi bi-unlock-fill me-2"></i>ロックを解除しました (页面将刷新)';
                    }

                    // 显示 Toast
                    if(toastEl && toast) {
                        toastBody.innerHTML = message;
                        toastEl.classList.remove('bg-success', 'bg-danger', 'bg-dark');
                        toastEl.classList.add(data.is_locked == 1 ? 'bg-danger' : 'bg-success');
                        toast.show();
                    }

                    // 3. 【关键】延迟一小会儿后刷新页面
                    // 给用户 0.6 秒的时间看到提示，然后刷新
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);

                } else {
                    throw new Error(data.message || '操作失败');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // 失败则恢复原状，不刷新
                icon.className = originalIconClass;
                this.disabled = false;
                this.style.opacity = '1';
                
                alert('エラーが発生しました: ' + error.message);
            });
        });
    });
});
</script>
@endsection