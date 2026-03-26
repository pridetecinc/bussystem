@extends('layouts.app')

@section('content')
<style>
    body { font-size: 0.9rem; }
    .table-compact th, .table-compact td {
        padding: 0.25rem 0.5rem !important;
        font-size: 0.85rem;
        vertical-align: middle;
        line-height: 1.2;
    }
    .table-compact th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    .search-form .form-control, .search-form .form-select {
        height: 31px; font-size: 0.875rem; padding: 0.25rem 0.5rem;
    }
    .search-form .btn {
        height: 31px; font-size: 0.75rem; padding: 0.35rem 0.6rem;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .editor-compact .form-control, .editor-compact .form-select {
        height: 28px; font-size: 0.8rem; padding: 0.1rem 0.4rem;
    }
    .editor-compact table th, .editor-compact table td {
        padding: 0.2rem 0.3rem !important; font-size: 0.8rem;
    }
    .editor-header { padding: 0.25rem 0.5rem !important; font-size: 0.85rem; }
    
    /* 只针对勘定科目的红色边框 */
    .account-input.is-invalid { 
        border-color: #dc3545 !important; 
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        padding-right: calc(1.5em + 0.75rem);
    }

    .editor-compact .input-group .form-control,
    .editor-compact .input-group .btn {
        height: 28px;
        line-height: 1.2;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
    }

    .editor-compact .input-group .btn-outline-danger {
        padding: 0 0.25rem;
        border-left: 0 !important;
        font-size: 0.9rem;
    }

    .editor-compact .input-group .form-control {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }
    .editor-compact .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
</style>

<div class="container-fluid py-2" style="padding-bottom: 340px;">
    <div class="card shadow-sm mb-2 border-0">
        <div class="card-header bg-dark py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-white fw-bold"><i class="bi bi-list-ul"></i> 仕訳伝票一覧</h6>
            <button type="button" class="btn btn-outline-light btn-sm" onclick="clearEditor()">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">新規</span>
            </button>
        </div>
        <div class="card-body p-2">
            <form method="GET" action="{{ route('masters.journal_entries.index') }}" class="row g-2 align-items-end mb-3 search-form">
                <div class="col-md-2">
                    <label class="form-label mb-0 text-muted" style="font-size: 0.75rem;">記帳日</label>
                    <input type="date" name="posting_date" class="form-control form-control-sm" value="{{ request('posting_date') }}">
                </div>
                <div class="col-md-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-search"></i> 検索</button>
                    @if(request()->hasAny(['search', 'posting_date']))
                        <a href="{{ route('masters.journal_entries.index') }}" class="btn btn-outline-secondary ms-1"><i class="bi bi-x-circle"></i> クリア</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">
                <table class="table table-hover table-bordered align-middle table-compact mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="4%">ID</th>
                            <th width="10%">SourceID</th>
                            <th width="8%">記帳日</th>
                            <th width="8%">部门</th>
                            <th width="5%">伝票種別</th>
                            <th width="16%">借</th>
                            <th width="16%">貸</th>
                            <th width="3%" class="text-center">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            @php
                                $debitSum = $entry->lines->where('side', '借')->sum('amount');
                                $creditSum = $entry->lines->where('side', '貸')->sum('amount');
                            @endphp
                            <tr style="cursor: pointer;"
                                onclick="loadEntryToEditor({{ $entry->id }}, '{{ $entry->posting_date }}', '{{ addslashes($entry->description) }}', {{ $entry->department_id ?? 'null' }}, @json($entry->lines))">
                                <td class="fw-bold text-muted">{{ $entry->id }}</td>
                                <td>{{ $entry->source_id}}</td>
                                <td>{{ $entry->posting_date->format('Y-m-d') }}</td>
                                <td>{{ $entry->department->name ?? '' }}</td>
                                <td>{{ $entry->source_type}}</td>
                                <td>{!! $entry->debit_details_html !!}</td>
                                <td>{!! $entry->credit_details_html !!}</td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" onclick="event.stopPropagation();">
                                        <form action="{{ route('masters.journal_entries.destroy', $entry->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-3 text-muted">データがありません。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-2">
                {{ $entries->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

{{-- 底部编辑器 --}}
<div class="fixed-bottom bg-white border-top shadow-lg p-2 editor-compact" style="z-index: 1000; height: 330px; overflow: hidden; display: flex; flex-direction: column;">
    <div class="d-flex justify-content-between align-items-center mb-1 flex-shrink-0">
        <div class="d-flex gap-2 align-items-center">
            <h6 class="mb-0 text-success fw-bold" style="font-size: 0.9rem;"><i class="bi bi-keyboard"></i> クイック入力</h6>
            <span id="editing-id-badge" class="badge bg-warning text-dark d-none" style="font-size: 0.75rem;">ID: <span id="editing-id-val"></span></span>
            <button type="button" class="btn btn-sm btn-outline-secondary py-0" style="height: 24px; font-size: 0.75rem;" onclick="clearEditor()">クリア</button>
        </div>
        <div class="d-flex gap-3 align-items-center">
            <div class="text-end" style="font-size: 0.8rem; line-height: 1.1;">
                <span class="text-danger">借方：<span id="total-debit-display" class="fw-bold">0</span></span> | 
                <span class="text-primary">貸方：<span id="total-credit-display" class="fw-bold">0</span></span>
                <br><span id="balance-status" class="badge bg-success" style="font-size: 0.7rem;">balanced</span>
            </div>
            <button type="button" class="btn btn-primary px-3 py-0" style="height: 28px; font-size: 0.85rem;" onclick="submitJournalEntry()">
                <i class="bi bi-save"></i> 保存
            </button>
        </div>
    </div>

    <input type="hidden" id="edit-entry-id" value="">
    
    <div class="row g-1 flex-grow-1" style="overflow: hidden;">
        <div class="col-md-12 mb-1">
            <div class="row g-1">
                <div class="col-md-2"><input type="date" id="post-date" class="form-control" value="{{ date('Y-m-d') }}"></div>
                <div class="col-md-2">
                    <input type="text" id="post-dept" class="form-control" list="dept-list-main" placeholder="部門を入力または選択" onchange="validateMainDeptInput()">
                    <datalist id="dept-list-main">
                        @foreach($departments as $dept)<option value="{{ $dept->name }}">{{ $dept->name }}</option>@endforeach
                    </datalist>
                </div>
                <div class="col-md-2"><input type="text" id="post-source-type" class="form-control" placeholder="伝票種別"></div>
            </div>
        </div>

        <div class="col-md-6 d-flex flex-column h-100">
            <div class="card h-100 border-danger border-1">
                <div class="card-header bg-danger text-white py-0 d-flex justify-content-between align-items-center editor-header">
                    <span><i class="bi bi-arrow-down-right"></i> 借方</span>
                    <button type="button" class="btn btn-sm btn-light text-danger py-0" style="height: 22px;" onclick="addLine('借')"><i class="bi bi-plus"></i> 行追加</button>
                </div>
                <div class="card-body p-1 overflow-auto" style="max-height: 220px;">
                    <table class="table table-sm table-bordered mb-0" id="table-debit">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th width="4%"></th>
                                <th width="30%">勘定科目</th>
                                <th width="20%">補助科目</th>
                                <th width="15%">取引先</th>
                                <th width="15%">税区分</th>
                                <th width="16%">金額</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-list" data-side="借"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 d-flex flex-column h-100">
            <div class="card h-100 border-primary border-1">
                <div class="card-header bg-primary text-white py-0 d-flex justify-content-between align-items-center editor-header">
                    <span><i class="bi bi-arrow-up-right"></i> 貸方</span>
                    <button type="button" class="btn btn-sm btn-light text-primary py-0" style="height: 22px;" onclick="addLine('貸')"><i class="bi bi-plus"></i> 行追加</button>
                </div>
                <div class="card-body p-1 overflow-auto" style="max-height: 220px;">
                    <table class="table table-sm table-bordered mb-0" id="table-credit">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th width="4%"></th>
                                <th width="30%">勘定科目</th>
                                <th width="20%">補助科目</th>
                                <th width="15%">取引先</th>
                                <th width="15%">税区分</th>
                                <th width="16%">金額</th>
                            </tr>
                        </thead>
                        <tbody class="sortable-list" data-side="貸"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const accountsData = @json($accounts);
    const partnersData = @json($partners);
    const taxesData = @json($taxes);
    const departmentsData = @json($departments);
    const csrfToken = '{{ csrf_token() }}';
    const saveUrl = "{{ route('masters.journal_entries.store') }}";
    const updateUrlBase = "{{ route('masters.journal_entries.update', '__ID__') }}";
    // 注意：如果路由未定义，JS 会捕获错误并跳过辅助科目加载，但不会崩溃
    let getSubsUrlTemplate = "{{ route('masters.account.account-subs', ['accountId' => '__ID__']) }}";
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Sortable !== 'undefined') {
            document.querySelectorAll('.sortable-list').forEach(list => {
                new Sortable(list, { animation: 150, handle: '.drag-handle', ghostClass: 'bg-light', onEnd: calculateTotals });
            });
        }
        addLine('借');
        addLine('貸');
    });

    function addLine(side, data = {}) {
        const tbody = document.querySelector(`.sortable-list[data-side="${side}"]`);
        if (!tbody) return;

        const rowId = 'row-' + Date.now() + Math.random().toString(36).substr(2, 9);
        const tr = document.createElement('tr');
        tr.setAttribute('data-row-id', rowId);
        tr.setAttribute('data-side', side);
        if(data && data.id) tr.setAttribute('data-db-id', data.id);

        const accList = accountsData || [];
        const partList = partnersData || [];
        const taxList = taxesData || [];

        // 1. 勘定科目
        let currentAccountText = '';
        if (data.account_id) {
            const found = accList.find(a => a.id == data.account_id);
            if (found) currentAccountText = `${found.code} - ${found.name}`;
        }
        const accountDataListId = `account-list-${rowId}`;
        const accountOptions = accList.map(a => `<option value="${a.code} - ${a.name}">`).join('');

        // 2. 補助科目 (初始为空) - 修正：提前定义 ID
        const subDataListId = `sub-list-${rowId}`;
        let currentSubText = data.account_sub_name || ''; 

        // 3. 取引先
        let currentPartnerText = '';
        if (data.partner_id) {
            const found = partList.find(p => p.id == data.partner_id);
            if (found) currentPartnerText = found.name;
        }
        const partnerDataListId = `partner-list-${rowId}`;
        const partnerOptions = partList.map(p => `<option value="${p.name}">`).join('');

        // 4. 税区分
        const taxOptions = `<option value="">なし</option>` + taxList.map(t => 
            `<option value="${t.id}" ${(data.tax_type_id == t.id) ? 'selected' : ''}>${t.name}</option>`
        ).join('');

        // 构建 HTML
        tr.innerHTML = `
            <td class="text-center drag-handle" style="cursor:move; font-size: 0.7rem;"><i class="bi bi-grip-vertical"></i></td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm account-input" 
                           list="${accountDataListId}" value="${currentAccountText}" placeholder="科目"
                           onchange="handleAccountChange(this)" onblur="validateAccountInput(this)">
                    <datalist id="${accountDataListId}">${accountOptions}</datalist>
                    <input type="hidden" class="account-id-hidden" value="${data.account_id || ''}">
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <!-- 修改点：移除 onchange/onblur 验证，允许随意输入 -->
                    <input type="text" class="form-control form-control-sm sub-input" 
                           list="${subDataListId}" value="${currentSubText}" placeholder="補助 (任意)"
                           ${!data.account_id ? 'disabled' : ''}>
                    <datalist id="${subDataListId}"></datalist>
                    <input type="hidden" class="sub-id-hidden" value="${data.account_sub_id || ''}">
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <!-- 修改点：移除 onchange/onblur 验证，允许随意输入 -->
                    <input type="text" class="form-control form-control-sm partner-input" 
                           list="${partnerDataListId}" value="${currentPartnerText}" placeholder="取引先 (任意)"
                           >
                    <datalist id="${partnerDataListId}">${partnerOptions}</datalist>
                    <input type="hidden" class="partner-id-hidden" value="${data.partner_id || ''}">
                </div>
            </td>
            <td><select class="form-select tax-select">${taxOptions}</select></td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" class="form-control amount-input text-end" 
                           value="${data.amount || ''}" oninput="calculateTotals()" placeholder="0">
                    <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)" style="border-left:0;"><i class="bi bi-x-lg"></i></button>
                </div>
            </td>
        `;
        
        tbody.appendChild(tr);
        calculateTotals();

        // 如果是编辑模式且有 account_id，立即触发加载辅助科目
        if (data.account_id) {
            const accInput = tr.querySelector('.account-input');
            fetchAccountSubs(accInput, data.account_sub_id, currentSubText);
        }
    }

    // --- 勘定科目变更：加载辅助科目 (保持验证) ---
    function handleAccountChange(inputElement) {
        const textValue = inputElement.value.trim();
        const row = inputElement.closest('tr');
        const hiddenIdInput = row.querySelector('.account-id-hidden');
        const subInput = row.querySelector('.sub-input');
        const subHidden = row.querySelector('.sub-id-hidden');
        const accList = accountsData || [];

        // 重置辅助科目
        subInput.value = '';
        subHidden.value = '';
        subInput.disabled = true;
        const subDatalist = row.querySelector(`datalist[id^="sub-list-"]`);
        if(subDatalist) subDatalist.innerHTML = '';

        if (!textValue) {
            hiddenIdInput.value = '';
            inputElement.classList.remove('is-invalid');
            calculateTotals();
            return;
        }

        const found = accList.find(a => `${a.code} - ${a.name}` === textValue);
        
        if (found) {
            hiddenIdInput.value = found.id;
            inputElement.value = `${found.code} - ${found.name}`;
            inputElement.classList.remove('is-invalid');
            
            subInput.disabled = false;
            fetchAccountSubs(inputElement, null, null);
        } else {
            hiddenIdInput.value = ''; 
            // 只有勘定科目会变红
            inputElement.classList.add('is-invalid');
            setTimeout(() => inputElement.classList.remove('is-invalid'), 2000);
        }
        calculateTotals();
    }
    function validateAccountInput(el) { handleAccountChange(el); }

    // --- AJAX 获取辅助科目 ---
    function fetchAccountSubs(accountInput, existingSubId = null, existingSubName = null) {
        const row = accountInput.closest('tr');
        const accountId = row.querySelector('.account-id-hidden').value;
        const subInput = row.querySelector('.sub-input');
        const subDatalist = row.querySelector(`datalist[id^="sub-list-"]`);
        const subHidden = row.querySelector('.sub-id-hidden');

        if (!accountId || !subDatalist) return;

        let url = '';
        try {
            url = getSubsUrlTemplate.replace('__ID__', accountId);
        } catch(e) {
            console.warn('Route URL generation failed, skipping subs fetch');
            return;
        }

        fetch(url)
            .then(res => res.ok ? res.json() : [])
            .then(data => {
                // 缓存数据到 row
                row.dataset.subsCache = JSON.stringify(data);
                
                let options = '';
                data.forEach(sub => {
                    options += `<option value="${sub.display}">`;
                });
                subDatalist.innerHTML = options;

                if (existingSubId && existingSubName) {
                    const found = data.find(s => s.id == existingSubId);
                    if (found) {
                        subHidden.value = found.id;
                        subInput.value = found.display;
                    } else {
                        subHidden.value = '';
                        subInput.value = '';
                    }
                }
            })
            .catch(err => console.error('Failed to load subs:', err));
    }

    // --- 辅助科目变更处理 (已简化：不再验证，只存值) ---
    function handleSubChange(inputElement) {
        const textValue = inputElement.value.trim();
        const row = inputElement.closest('tr');
        const hiddenIdInput = row.querySelector('.sub-id-hidden');
        
        if (!textValue) {
            hiddenIdInput.value = '';
            return;
        }

        // 尝试从缓存查找 ID
        let subsData = [];
        if (row.dataset.subsCache) {
            subsData = JSON.parse(row.dataset.subsCache);
            const found = subsData.find(s => s.display === textValue);
            if (found) {
                hiddenIdInput.value = found.id;
            } else {
                // 用户输入了不在列表里的内容，ID 留空，允许提交
                hiddenIdInput.value = ''; 
            }
        } else {
            hiddenIdInput.value = '';
        }
        // 不再添加 is-invalid
    }
    
    // 取引先处理 (已简化：不再验证)
    function handlePartnerChange(inputElement) {
        const textValue = inputElement.value.trim();
        const row = inputElement.closest('tr');
        const hiddenIdInput = row.querySelector('.partner-id-hidden');
        const partList = partnersData || [];
        
        if (!textValue) { 
            hiddenIdInput.value = ''; 
            return; 
        }
        
        const found = partList.find(p => p.name === textValue);
        if (found) {
            hiddenIdInput.value = found.id;
        } else {
            // 用户输入了不在列表里的内容，ID 留空，允许提交
            hiddenIdInput.value = ''; 
        }
        // 不再添加 is-invalid
    }

    function validateMainDeptInput() {
        const inputEl = document.getElementById('post-dept');
        inputEl.classList.remove('is-invalid');
    }

    function removeRow(btn) {
        const tbody = btn.closest('tbody');
        if (tbody.querySelectorAll('tr').length <= 1) { alert('最低 1 行は必要です。'); return; }
        btn.closest('tr').remove();
        calculateTotals();
    }

    function clearEditor() {
        document.getElementById('edit-entry-id').value = '';
        document.getElementById('editing-id-badge').classList.add('d-none');
        document.getElementById('post-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('post-dept').value = '';
        document.getElementById('post-source-type').value = '';
        document.querySelector('.sortable-list[data-side="借"]').innerHTML = '';
        document.querySelector('.sortable-list[data-side="貸"]').innerHTML = '';
        addLine('借');
        addLine('貸');
        calculateTotals();
    }

    window.loadEntryToEditor = function(id, date, desc, deptId, lines) {
        clearEditor();
        document.getElementById('edit-entry-id').value = id;
        document.getElementById('editing-id-val').innerText = id;
        document.getElementById('editing-id-badge').classList.remove('d-none');
        document.getElementById('post-date').value = date;
        
        const deptInput = document.getElementById('post-dept');
        if(deptId) {
            const found = departmentsData.find(d => d.id == deptId);
            deptInput.value = found ? found.name : '';
        }

        const safeLines = lines || [];
        safeLines.filter(l => l.side === '借').forEach(l => addLine('借', {
            ...l, 
            account_sub_name: l.account_sub ? `${l.account_sub.code || ''} - ${l.account_sub.name}` : '' 
        }));
        safeLines.filter(l => l.side === '貸').forEach(l => addLine('貸', {
            ...l, 
            account_sub_name: l.account_sub ? `${l.account_sub.code || ''} - ${l.account_sub.name}` : '' 
        }));
        
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    };

    function calculateTotals() {
        let debitTotal = 0, creditTotal = 0;
        document.querySelectorAll('.sortable-list[data-side="借"] .amount-input').forEach(i => debitTotal += parseFloat(i.value)||0);
        document.querySelectorAll('.sortable-list[data-side="貸"] .amount-input').forEach(i => creditTotal += parseFloat(i.value)||0);
        
        document.getElementById('total-debit-display').innerText = debitTotal.toLocaleString();
        document.getElementById('total-credit-display').innerText = creditTotal.toLocaleString();
        
        const badge = document.getElementById('balance-status');
        if (Math.abs(debitTotal - creditTotal) < 0.01 && debitTotal > 0) {
            badge.className = 'badge bg-success'; badge.innerText = 'OK'; return true;
        } else {
            badge.className = 'badge bg-danger'; badge.innerText = 'NG (' + (debitTotal - creditTotal).toLocaleString() + ')'; return false;
        }
    }

        function submitJournalEntry() {
        if (!calculateTotals()) { Swal.fire('エラー', '借貸合計が一致していません。', 'error'); return; }

        const entryId = document.getElementById('edit-entry-id').value;
        const linesData = [];
        let isValid = true;

        const processRows = (selector, side) => {
            document.querySelectorAll(selector + ' tr').forEach(tr => {
                const accId = tr.querySelector('.account-id-hidden')?.value;
                const subId = tr.querySelector('.sub-id-hidden')?.value;
                const partId = tr.querySelector('.partner-id-hidden')?.value;
                const amount = tr.querySelector('.amount-input')?.value;
                const accInput = tr.querySelector('.account-input');
                
                // 【新增】获取用户实际输入的文本内容
                const subInput = tr.querySelector('.sub-input');
                const partInput = tr.querySelector('.partner-input');
                const subText = subInput ? subInput.value.trim() : '';
                const partText = partInput ? partInput.value.trim() : '';

                // 只验证勘定科目和金额
                if (!accId || !amount) { 
                    isValid = false; 
                    if(accInput) accInput.classList.add('is-invalid'); 
                    return; 
                }
                
                if(accInput) accInput.classList.remove('is-invalid');

                linesData.push({
                    id: tr.getAttribute('data-db-id') || null,
                    side: side,
                    account_id: accId,
                    
                    // 【修改点】同时提交 ID 和 文本内容
                    account_sub_id: subId || null, 
                    account_sub_name: subText, // 新增：提交文本
                    
                    partner_id: partId || null,
                    partner_name: partText,    // 新增：提交文本
                    
                    tax_type_id: tr.querySelector('.tax-select')?.value || null,
                    amount: amount,
                    remark: ''
                });
            });
        };

        processRows('.sortable-list[data-side="借"]', 1);
        processRows('.sortable-list[data-side="貸"]', 2);

        if (!isValid) { 
            Swal.fire('エラー', '勘定科目を選択してください。', 'warning'); 
            return; 
        }

        const formData = {
            posting_date: document.getElementById('post-date').value,
            department_id: document.getElementById('post-dept').value,
            source_type: document.getElementById('post-source-type').value,
            lines: linesData,
            _token: csrfToken
        };

        const url = entryId ? updateUrlBase.replace('__ID__', entryId) : saveUrl;
        const method = entryId ? 'PUT' : 'POST';
        const btn = document.querySelector('button[onclick="submitJournalEntry()"]');
        
        btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> ...';

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify(formData)
        })
        .then(res => res.ok ? res.json() : res.json().then(e => { throw new Error(e.message || 'Error'); }))
        .then(data => {
            Swal.fire('成功', data.message || '保存しました', 'success').then(() => location.reload());
        })
        .catch(err => {
            Swal.fire('エラー', err.message, 'error');
            btn.disabled = false; btn.innerHTML = '<i class="bi bi-save"></i> 保存';
        });
    }

</script>
@endsection