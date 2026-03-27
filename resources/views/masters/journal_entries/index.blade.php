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
    .editor-compact .form-select.is-invalid {
        border-color: #dc3545 !important;
        padding-right: calc(1.5em + 0.75rem);
        background-repeat: no-repeat;
        background-position: right calc(0.375em + 0.1875rem) center;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
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
                            <tr style="cursor: pointer;" onclick="fetchAndLoadEntry({{ $entry->id }})">
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
                    <button type="button" class="btn btn-sm btn-light text-danger py-0" style="height: 22px;" onclick="addLine(1)"><i class="bi bi-plus"></i> 行追加</button>
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
                        <tbody class="sortable-list" data-side="1"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 d-flex flex-column h-100">
            <div class="card h-100 border-primary border-1">
                <div class="card-header bg-primary text-white py-0 d-flex justify-content-between align-items-center editor-header">
                    <span><i class="bi bi-arrow-up-right"></i> 貸方</span>
                    <button type="button" class="btn btn-sm btn-light text-primary py-0" style="height: 22px;" onclick="addLine(2)"><i class="bi bi-plus"></i> 行追加</button>
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
                        <tbody class="sortable-list" data-side="2"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 全局变量
    const accountsData = @json($accounts);
    const accountsDataJie = @json($accountsJie ?? []); // 借方科目数据
    const accountsDataDai = @json($accountsDai ?? []); // 贷方科目数据
    const partnersData = @json($partners);
    const taxesData = @json($taxes);
    const departmentsData = @json($departments);
    const csrfToken = '{{ csrf_token() }}';
    const saveUrl = "{{ route('masters.journal_entries.store') }}";
    const updateUrlBase = "{{ route('masters.journal_entries.update', '__ID__') }}";
    let getSubsUrlTemplate = "{{ route('masters.account.account-subs', ['accountId' => '__ID__']) }}";

    // 1. 初始化
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Sortable !== 'undefined') {
            document.querySelectorAll('.sortable-list').forEach(list => {
                new Sortable(list, { 
                    animation: 150, 
                    handle: '.drag-handle', 
                    ghostClass: 'bg-light', 
                    onEnd: calculateTotals 
                });
            });
        }
        // 初始化添加借(1)贷(2)行
        addLine(1);
        addLine(2);
    });

    // 2. 添加行 (Side 使用数字 1 或 2)
    function addLine(side, data = {}) {
        // 确保 side 是数字
        side = parseInt(side);
        
        // 根据数字 side (1/2) 获取对应的 tbody
        const tbody = document.querySelector(`.sortable-list[data-side="${side}"]`);
        if (!tbody) {
            console.error('找不到对应的 tbody:', side);
            return;
        }

        const rowId = 'row-' + Date.now() + Math.random().toString(36).substr(2, 9);
        const tr = document.createElement('tr');
        tr.setAttribute('data-row-id', rowId);
        tr.setAttribute('data-side', side);
        if(data && data.id) tr.setAttribute('data-db-id', data.id);

        const accList = accountsData || [];
        const partList = partnersData || [];
        const taxList = taxesData || [];

        const currentAccountData = side === 1 ? accountsDataJie : accountsDataDai;

        // 1. 勘定科目
        let currentAccountText = '';
        if (data.account_full_text) {
            currentAccountText = data.account_full_text;
        } else if (data.account_id) {
            const found = currentAccountData.find(a => a.id == data.account_id);
            if (found) currentAccountText = `${found.code} - ${found.name}`;
        }
        
        const accountDataListId = `account-list-${rowId}`;
        const accountOptions = currentAccountData.map(a => `<option value="${a.code} - ${a.name}">`).join('');

        // 2. 補助科目
        const subDataListId = `sub-list-${rowId}`;
        let currentSubText = data.account_sub_name || ''; 

        // 3. 取引先
        let currentPartnerText = '';
        if (data.partner_id) {
            const found = partList.find(p => p.id == data.partner_id);
            if (found) currentPartnerText = found.name;
        } else if (data.partner_name) {
            currentPartnerText = data.partner_name;
        }
        const partnerDataListId = `partner-list-${rowId}`;
        const partnerOptions = partList.map(p => `<option value="${p.name}">`).join('');

        // 4. 税区分
        const taxOptions = `<option value="">[税区分無し]</option>` + taxList.map(t => `<option value="${t.id}" ${(data.tax_type_id == t.id) ? 'selected' : ''}>${t.name}</option>` ).join('');

        tr.innerHTML = `
            <td class="text-center drag-handle" style="cursor:move; font-size: 0.7rem;"><i class="bi bi-grip-vertical"></i></td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm account-input" 
                           list="${accountDataListId}" value="${currentAccountText}" placeholder="科目"
                           onchange="handleAccountChange(this); this.classList.remove('is-invalid');" onblur="validateAccountInput(this)">
                    <datalist id="${accountDataListId}">${accountOptions}</datalist>
                    <input type="hidden" class="account-id-hidden" value="${data.account_id || ''}">
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm sub-input" 
                           list="${subDataListId}" value="${currentSubText}" placeholder="補助 (任意)"
                           ${!data.account_id ? 'disabled' : ''}>
                    <datalist id="${subDataListId}"></datalist>
                    <input type="hidden" class="sub-id-hidden" value="${data.account_sub_id || ''}">
                </div>
            </td>
            <td>
                <div class="position-relative">
                    <input type="text" class="form-control form-control-sm partner-input" 
                           list="${partnerDataListId}" value="${currentPartnerText}" placeholder="取引先 (任意)">
                    <datalist id="${partnerDataListId}">${partnerOptions}</datalist>
                    <input type="hidden" class="partner-id-hidden" value="${data.partner_id || ''}">
                </div>
            </td>
            <td><select class="form-select tax-select">${taxOptions}</select></td>
            <td>
                <div class="input-group input-group-sm">
                    <input type="number" step="0.01" class="form-control amount-input text-end" 
                           value="${data.amount || ''}" oninput="calculateTotals(); this.classList.remove('is-invalid');" placeholder="0">
                    <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)" style="border-left:0;"><i class="bi bi-x-lg"></i></button>
                </div>
            </td>
        `;
        
        tbody.appendChild(tr);
        calculateTotals();

        if (data.account_id) {
            const accInput = tr.querySelector('.account-input');
            fetchAccountSubs(accInput, data.account_sub_id, currentSubText);
        }
    }

    // 3. 勘定科目变更
    function handleAccountChange(inputElement) {
        const textValue = inputElement.value.trim();
        const row = inputElement.closest('tr');
        const hiddenIdInput = row.querySelector('.account-id-hidden');
        const subInput = row.querySelector('.sub-input');
        const subHidden = row.querySelector('.sub-id-hidden');
        const accList = accountsData || [];

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

            const taxSelect = row.querySelector('.tax-select');
            if (taxSelect && found.tax_id) {
                taxSelect.value = found.tax_id; 
            }
        } else {
            hiddenIdInput.value = ''; 
            inputElement.classList.add('is-invalid');
            setTimeout(() => inputElement.classList.remove('is-invalid'), 2000);
        }
        calculateTotals();
    }
    function validateAccountInput(el) { handleAccountChange(el); }

    // 4. AJAX 获取辅助科目
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

    function clearEditor(addDefaultLines = true) {
        document.getElementById('edit-entry-id').value = '';
        document.getElementById('editing-id-badge').classList.add('d-none');
        document.getElementById('post-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('post-dept').value = '';
        document.getElementById('post-source-type').value = '';
        
        // 清空列表
        document.querySelector('.sortable-list[data-side="1"]').innerHTML = '';
        document.querySelector('.sortable-list[data-side="2"]').innerHTML = '';
        
        // 只有当 addDefaultLines 为 true 时才添加空行
        if (addDefaultLines) {
            addLine(1);
            addLine(2);
        }
        
        calculateTotals();
    }

    // 5. 计算合计
    function calculateTotals() {
        let debitTotal = 0, creditTotal = 0;
        document.querySelectorAll('.sortable-list[data-side="1"] .amount-input').forEach(i => debitTotal += parseFloat(i.value)||0);
        document.querySelectorAll('.sortable-list[data-side="2"] .amount-input').forEach(i => creditTotal += parseFloat(i.value)||0);
        
        document.getElementById('total-debit-display').innerText = debitTotal.toLocaleString();
        document.getElementById('total-credit-display').innerText = creditTotal.toLocaleString();
        
        const badge = document.getElementById('balance-status');
        if (Math.abs(debitTotal - creditTotal) < 0.01 && debitTotal > 0) {
            badge.className = 'badge bg-success'; badge.innerText = 'OK'; return true;
        } else {
            badge.className = 'badge bg-danger'; badge.innerText = 'NG (' + (debitTotal - creditTotal).toLocaleString() + ')'; return false;
        }
    }

    // 6. 提交数据 (Side 使用数字 1 或 2)
    function submitJournalEntry() {
        // 0. 预清理：提交前先清除所有红框，让界面清爽
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        // 1. 再次确认平衡
        if (!calculateTotals()) { 
            Swal.fire('エラー', '借貸合計が一致していません。', 'error'); 
            return; 
        }

        const entryId = document.getElementById('edit-entry-id').value;
        const linesData = [];
        
        // 2. 定义三个独立的错误标志
        let hasAccountError = false; // 科目错误
        let hasAmountError = false;  // 金额错误
        let hasTaxError = false;     // 税区分错误

        // 3. 定义处理行的函数
        const processRows = (selector, sideCode) => {
            const rows = document.querySelectorAll(selector + ' tr');
            
            rows.forEach(tr => {
                // 获取 DOM 元素
                const accInput = tr.querySelector('.account-input');
                const amountInput = tr.querySelector('.amount-input');
                const taxSelect = tr.querySelector('.tax-select');

                // 获取值 (使用 ?. 防止报错)
                const accId = tr.querySelector('.account-id-hidden')?.value;
                const amount = amountInput?.value;
                const taxValue = taxSelect?.value;

                // 获取辅助信息 (用于提交)
                const subText = tr.querySelector('.sub-input')?.value.trim() || '';
                const partText = tr.querySelector('.partner-input')?.value.trim() || '';
                const subId = tr.querySelector('.sub-id-hidden')?.value || null;
                const partId = tr.querySelector('.partner-id-hidden')?.value || null;

                let rowValid = true;

                // --- 独立校验 1: 勘定科目 ---
                if (!accId) {
                    hasAccountError = true;
                    rowValid = false;
                    if (accInput) accInput.classList.add('is-invalid');
                }

                // --- 独立校验 2: 金额 ---
                if (!amount) {
                    hasAmountError = true;
                    rowValid = false;
                    if (amountInput) amountInput.classList.add('is-invalid');
                }


                // 只有当这一行所有校验都通过时，才加入数据数组
                if (rowValid) {
                    linesData.push({
                        id: tr.getAttribute('data-db-id') || null,
                        side: sideCode,
                        account_id: accId,
                        account_sub_id: subId, 
                        account_sub_name: subText,
                        partner_id: partId,
                        partner_name: partText,
                        tax_type_id: taxValue,
                        amount: amount,
                        remark: ''
                    });
                }
            });
        };

        // 4. 处理借方 (1) 和 贷方 (2)
        processRows('.sortable-list[data-side="1"]', 1);
        processRows('.sortable-list[data-side="2"]', 2);

        // 5. 根据错误类型，分别弹出提示 (或者合并提示)
        // 这里为了用户体验，如果同时有多个错误，我会把它们合并在一个弹窗里，但红框是分开标的
        
        let errorMessage = '';
        if (hasAccountError) errorMessage += '・勘定科目が選択されていません。<br>';
        if (hasAmountError) errorMessage += '・金額が入力されていません。<br>';

        if (errorMessage) {
            Swal.fire('入力エラー', errorMessage, 'warning');
            return;
        }
        
        // 6. 处理部门
        let deptId = null;
        const deptName = document.getElementById('post-dept').value;
        if(deptName) {
            const found = departmentsData.find(d => d.name === deptName);
            deptId = found ? found.id : null;
        }

        const formData = {
            posting_date: document.getElementById('post-date').value,
            department_id: deptId,
            department_name: deptName, 
            source_type: document.getElementById('post-source-type').value,
            lines: linesData,
            _token: csrfToken
        };

        // 7. 发送请求
        const url = entryId ? updateUrlBase.replace('__ID__', entryId) : saveUrl;
        const method = entryId ? 'PUT' : 'POST';
        const btn = document.querySelector('button[onclick="submitJournalEntry()"]');
        
        btn.disabled = true; 
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> ...';

        fetch(url, {
            method: method,
            headers: { 
                'Content-Type': 'application/json', 
                'X-Requested-With': 'XMLHttpRequest', 
                'Accept': 'application/json' 
            },
            body: JSON.stringify(formData)
        })
        .then(res => res.ok ? res.json() : res.json().then(e => { throw new Error(e.message || 'Error'); }))
        .then(data => {
            Swal.fire('成功', data.message || '保存しました', 'success').then(() => location.reload());
        })
        .catch(err => {
            console.error(err);
            Swal.fire('エラー', err.message || '通信エラーが発生しました', 'error');
            btn.disabled = false; 
            btn.innerHTML = '<i class="bi bi-save"></i> 保存';
        });
    }

    // 7. AJAX 获取完整数据
    function fetchAndLoadEntry(entryId) {
        const btn = document.querySelector('button[onclick="submitJournalEntry()"]');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> 読込中...';

        const fetchUrl = "{{ route('masters.journal_entries.show', '__ID__') }}".replace('__ID__', entryId);

        fetch(fetchUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => {
            if (!res.ok) throw new Error('データの取得に失敗しました');
            return res.json();
        })
        .then(data => {
            loadEntryToEditorFull(data);
        })
        .catch(err => {
            Swal.fire('エラー', err.message, 'error');
            console.error(err);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }

    // 8. 渲染回显数据
    function loadEntryToEditorFull(data) {
        clearEditor(false);

        document.getElementById('edit-entry-id').value = data.id;
        document.getElementById('editing-id-val').innerText = data.id;
        document.getElementById('editing-id-badge').classList.remove('d-none');
        document.getElementById('post-date').value = data.posting_date;
        document.getElementById('post-source-type').value = data.source_type || '';
        
        const deptInput = document.getElementById('post-dept');
        if (data.department && data.department.name) {
            deptInput.value = data.department.name;
        } else if (data.department_id) {
            const found = departmentsData.find(d => d.id == data.department_id);
            deptInput.value = found ? found.name : '';
        }

        if (data.lines && Array.isArray(data.lines)) {
            data.lines.forEach(line => {
                const lineData = {
                    id: line.id,
                    account_id: line.account_id,
                    account_sub_id: line.account_sub_id,
                    partner_id: line.partner_id,
                    tax_type_id: line.tax_type_id,
                    amount: line.amount,
                    side: line.side, // 直接使用数字 1 或 2
                    account_full_text: line.account_full_text || '',
                    account_sub_name: line.account_sub ? (line.account_sub.display_name || `${line.account_sub.code||''} - ${line.account_sub.name}`) : '',
                    partner_name: line.partner ? line.partner.name : ''
                };

                addLine(line.side, lineData);
            });
        }
        
        calculateTotals();
        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
    }
</script>

@endsection