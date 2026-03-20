@extends('layouts.app')

@section('title', '請求書作成')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}">請求書管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">請求書作成</li>
                </ol>
            </nav>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle"></i> 入力エラーがあります</h5>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            <form action="{{ route('masters.invoices.store') }}" method="POST" id="invoiceForm">
                @csrf
                <input type="hidden" name="group_id" value="{{ $groupId }}">

                <!-- ================= 第一部分：基本情報 ================= -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> 請求書基本情報</h5>
                    </div>
                    <div class="card-body p-3">
                        
                        <!-- Row 1: 左侧代理店 (6 列) + 右侧 8 字段 (6 列) -->
                        <div class="row g-3 mb-3 align-items-start">
                            
                            <!-- 左侧：代理店 & Textarea (占 6 列，去掉了卡片包裹和多余标题) -->
                            <div class="col-lg-6">
                                <!-- 新增：Flex 容器，让文字和下拉框在同一行 -->
                                <div class="d-flex align-items-center mb-2">
                                    <label for="agency_id" class="fw-bold me-2 mb-0">
                                        <i class="bi bi-building"></i> 代理店
                                    </label>
                                    <!-- 下拉框：去掉 form-select-lg，增加 style 控制宽度 -->
                                    <select class="form-select form-select-sm" id="agency_id" name="agency_id" style="width: auto; min-width: 100px;">
                                        <option value="">-- 代理店を選択してください --</option>
                                        @foreach($agencies ?? [] as $agency)
                                            <option value="{{ $agency->id }}" {{ old('agency_id') == $agency->id ? 'selected' : '' }}
                                                    data-agency-name="{{ $agency->agency_name ?? '' }}"
                                                    data-agency-code="{{ $agency->agency_code ?? '' }}">
                                                {{ $agency->agency_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <!-- Textarea：rows 从 7 改为 3 -->
                                <textarea required class="form-control" id="agency_detail" name="agency_detail" rows="3" placeholder="代理店を選択すると自動入力されます...">{{ old('agency_detail') }}</textarea>
                            </div>

                            <!-- 右侧：8 个字段 (占 6 列，强制 2 行 x 4 列) -->
                            <div class="col-lg-6">
                                <div class="row g-2">
                                    <!-- 第一行：4 个字段 -->
                                    <div class="col-md-3 col-6">
                                        <label for="billing_contact" class="form-label small mb-1">請求担当</label>
                                        <select class="form-select form-select-sm" id="staff_id" name="staff_id">
                                            @foreach($staffs ?? [] as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_id') == $staff->id ? 'selected' : '' }}>
                                                    {{ $staff->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small mb-1">税込/税別</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="tax_mode" id="tax_mode_1" value="1" {{ old('tax_mode', '1') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="tax_mode_1">税込</label>

                                            <input type="radio" class="btn-check" name="tax_mode" id="tax_mode_2" value="2" {{ old('tax_mode', '1') == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="tax_mode_2">税別</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small mb-1">言語</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="language" id="lang_1" value="1" {{ old('language', '1') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="lang_1">日本語</label>

                                            <input type="radio" class="btn-check" name="language" id="lang_2" value="2" {{ old('language', '1') == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="lang_2">英語</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small mb-1">タイプ</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="type" id="type_1" value="1" {{ old('type', '1') == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="type_1">正式</label>

                                            <input type="radio" class="btn-check" name="type" id="type_2" value="2" {{ old('type', '1') == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="type_2">見積</label>
                                        </div>
                                    </div>

                                    <!-- 第二行：4 个字段 -->
                                    <div class="col-md-3 col-6">
                                        <label for="currency_code" class="form-label small mb-1">通貨</label>
                                        <select class="form-select form-select-sm" id="currency_code" name="currency_code">
                                            @foreach($currencies ?? [] as $currency)
                                                <option value="{{ $currency->currency_code }}" {{ old('currency_code') == $currency->currency_code ? 'selected' : '' }}>
                                                    {{ $currency->currency_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="invoice_date" class="form-label small mb-1">請求日</label>
                                        <input type="date" class="form-control form-control-sm" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', now()->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="operation_date" class="form-label small mb-1">運行日</label>
                                        <input type="date" class="form-control form-control-sm" id="operation_date" name="operation_date" value="{{ old('operation_date') }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="reservation_id" class="form-label small mb-1">予約 ID</label>
                                        <input type="text" class="form-control form-control-sm" id="reservation_id" name="reservation_id" value="{{ old('reservation_id') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3 text-muted">

                        <!-- Row 2: 标题、支付日、银行、锁 -->
                        <div class="row g-3 align-items-end mb-3">
                            <div class="col-md-4">
                                <label for="billing_title" class="form-label fw-bold">タイトル</label>
                                <input type="text" class="form-control @error('billing_title') is-invalid @enderror"
                                    id="billing_title" name="billing_title"
                                    value="{{ old('billing_title') }}" placeholder="例：2024 年 3 月分請求書">
                            </div>
                            <div class="col-md-3">
                                <label for="due_date" class="form-label fw-bold">支払指定日</label>
                                <input type="date" required class="form-control @error('due_date') is-invalid @enderror"
                                    id="due_date" name="due_date" value="{{ old('due_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="bank_id" class="form-label fw-bold">入金銀行</label>
                                <select class="form-select @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id">
                                    @foreach($banks ?? [] as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold d-block text-center">ロック</label>
                                <!-- 隐藏真实的 checkbox，但保留功能 -->
                                <input class="form-check-input" type="checkbox" role="switch" id="lock_switch" 
                                    {{ old('is_locked') ? 'checked' : '' }} style="display: none;">
                                
                                <!-- 只显示这个图标容器，点击它来触发隐藏的 checkbox -->
                                <div class="text-center mt-2" id="lock_icon_container" style="cursor: pointer; font-size: 2rem;">
                                    <!-- 图标内容由 JS 控制 -->
                                    <i class="bi bi-unlock-fill text-success"></i>
                                </div>
                                
                                <!-- 提交用的隐藏域 -->
                                <input type="hidden" name="is_locked" id="lock_hidden_input" value="{{ old('is_locked') ? '1' : '0' }}">
                            </div>
                        </div>

                        <!-- Row 3: 備考 (独占一行) -->
                        <div class="row">
                            <div class="col-12">
                                <label for="notes" class="form-label fw-bold">備考</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="請求書的下に表示する備考を入力してください。">{{ old('notes') }}</textarea>
                                @error('notes') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ================= 第二部分：請求明細 (保持原样) ================= -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-task"></i> 請求明細</h5>
                        <button type="button" class="btn btn-light btn-sm" id="addItemRowBtn">
                            <i class="bi bi-plus-lg"></i> 行を追加
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No.</th>
                                        <th style="width: 500px;">内容</th>
                                        <th style="width: 100px;">単価</th>
                                        <th style="width: 100px;">数量</th>
                                        <th style="width: 100px;">税率 (%)</th>
                                        <th style="width: 120px;">小計</th> 
                                        <th style="width: 100px;">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php
                                        $oldItems = old('items', []);
                                        $displayItems = count($oldItems) > 0 ? $oldItems : array_fill(0, 10, []);
                                    @endphp
                                    @foreach($displayItems as $index => $item)
                                        @php $index = (int)$index; $orderNumber = $index + 1; @endphp
                                        <tr data-index="{{ $index }}">
                                            <td class="text-center align-middle display-order">{{ $orderNumber }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm description" 
                                                    name="items[{{ $index }}][description]" list="product-list-{{ $index }}" 
                                                    value="{{ $item['description'] ?? old('items.'.$index.'.description') }}" placeholder="入力または選択">
                                                <datalist id="product-list-{{ $index }}">
                                                    @foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach
                                                </datalist>
                                            </td>
                                            <td><input type="number" class="form-control form-control-sm unit-price" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" min="0" step="0.01"></td>
                                            <td><input type="number" class="form-control form-control-sm quantity" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? '' }}" min="1" step="1"></td>
                                            <td>
                                                <select class="form-control form-control-sm tax-rate" name="items[{{ $index }}][tax_rate]">
                                                    <option value="10" {{ (isset($item['tax_rate']) && $item['tax_rate'] == 10) ? 'selected' : '' }}>10</option>
                                                    <option value="8"  {{ (isset($item['tax_rate']) && $item['tax_rate'] == 8)  ? 'selected' : '' }}>8</option>
                                                    <option value="-1" {{ (isset($item['tax_rate']) && $item['tax_rate'] == -1) ? 'selected' : '' }}>免税</option>
                                                    <option value="-2" {{ (isset($item['tax_rate']) && $item['tax_rate'] == -2) ? 'selected' : '' }}>非課税</option>
                                                </select>
                                            </td>
                                            <td class="align-middle"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="0.00" readonly style="background-color: #f8f9fa; text-align: left;"></td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動" tabindex="-1"><i class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動" tabindex="-1"><i class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加" tabindex="-1"><i class="bi bi-plus-lg"></i></button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除" tabindex="-1"><i class="bi bi-dash-lg"></i></button>
                                                </div>
                                                <input type="hidden" name="items[{{ $index }}][display_order]" value="{{ $orderNumber }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Template -->
                <template id="newRowTemplate">
                    <tr>
                        <td class="text-center align-middle display-order"></td>
                        <td>
                            <input type="text" class="form-control form-control-sm description" name="items[__index__][description]" list="product-list-__index__" value="" placeholder="入力または選択">
                            <datalist id="product-list-__index__">@foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach</datalist>
                        </td>
                        <td><input type="number" class="form-control form-control-sm unit-price" name="items[__index__][unit_price]" min="0" step="0.01" value=""></td>
                        <td><input type="number" class="form-control form-control-sm quantity" name="items[__index__][quantity]" min="1" step="1" value=""></td>
                        <td>
                            <select class="form-control form-control-sm tax-rate" name="items[__index__][tax_rate]">
                                <option value="10" selected>10</option><option value="8">8</option><option value="-1">免税</option><option value="-2">非課税</option>
                            </select>
                        </td>
                        <td class="align-middle"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="0.00" readonly style="background-color: #f8f9fa; text-align: left;"></td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" tabindex="-1"><i class="bi bi-arrow-up"></i></button>
                                <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" tabindex="-1"><i class="bi bi-arrow-down"></i></button>
                                <button type="button" class="btn btn-outline-success btn-sm add-row-btn" tabindex="-1"><i class="bi bi-plus-lg"></i></button>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" tabindex="-1"><i class="bi bi-dash-lg"></i></button>
                            </div>
                            <input type="hidden" name="items[__index__][display_order]" value="">
                        </td>
                    </tr>
                </template>

                <!-- 操作ボタン -->
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> 登録する</button>
                        <a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}" class="btn btn-secondary"><i class="bi bi-x-circle"></i> キャンセル</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    // 1. 代理店联动
    const agencySelect = document.getElementById('agency_id');
    const clientDetailsTextarea = document.getElementById('agency_detail');
    if (agencySelect && clientDetailsTextarea) {
        agencySelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value === "") { clientDetailsTextarea.value = ""; return; }
            const agencyName = selectedOption.getAttribute('data-agency-name') || '';
            const agencyCode = selectedOption.getAttribute('data-agency-code') || '';
            const name = selectedOption.text;
            let detailsText = `会社名:${name}\n`;
            if (agencyCode) detailsText += `コード:${agencyCode}\n`;
            clientDetailsTextarea.value = detailsText;
        });
    }

    // 2. 锁开关
    const lockIconContainer = document.getElementById('lock_icon_container');
    const lockSwitch = document.getElementById('lock_switch'); // 隐藏的开关
    const lockHiddenInput = document.getElementById('lock_hidden_input');

    if (lockIconContainer && lockSwitch && lockHiddenInput) {
        
        // 初始化图标状态
        updateLockIcon();

        // 点击图标容器 -> 触发隐藏开关的点击
        lockIconContainer.addEventListener('click', function () {
            lockSwitch.checked = !lockSwitch.checked; // 反转状态
            updateLockIcon(); // 更新图标
        });

        function updateLockIcon() {
            const iconElement = lockIconContainer.querySelector('i');
            if (lockSwitch.checked) {
                // 锁定状态：红色锁头
                iconElement.className = 'bi bi-lock-fill text-danger';
                lockHiddenInput.value = '1';
            } else {
                // 解锁状态：绿色开锁
                iconElement.className = 'bi bi-unlock-fill text-success';
                lockHiddenInput.value = '0';
            }
        }
    }

    // 3. 表格逻辑 (保持不变)
    const existingRows = document.querySelectorAll('#itemsBody tr[data-index]');
    let globalRowIndex = existingRows.length;
    function updateDisplayOrder() {
        document.querySelectorAll('#itemsBody tr[data-index]').forEach((row, idx) => {
            const order = idx + 1;
            row.querySelector('.display-order').textContent = order;
            const hiddenInput = row.querySelector('input[name$="[display_order]"]');
            if (hiddenInput) hiddenInput.value = order;
        });
    }
    function addNewRow(afterIndex = null) {
        const template = document.getElementById('newRowTemplate');
        const newRow = template.content.firstElementChild.cloneNode(true);
        const newIndex = globalRowIndex++;
        newRow.dataset.index = newIndex;
        newRow.innerHTML = newRow.innerHTML.replace(/__index__/g, newIndex);
        const tbody = document.getElementById('itemsBody');
        if (afterIndex !== null) {
            const targetRow = tbody.querySelector(`tr[data-index="${afterIndex}"]`);
            if (targetRow && targetRow.nextElementSibling) tbody.insertBefore(newRow, targetRow.nextElementSibling);
            else tbody.appendChild(newRow);
        } else { tbody.appendChild(newRow); }
        updateDisplayOrder();
    }
    document.getElementById('addItemRowBtn').addEventListener('click', () => addNewRow());
    document.getElementById('itemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.add-row-btn, .delete-row-btn, .move-up-btn, .move-down-btn');
        if (!btn) return;
        const row = btn.closest('tr');
        const currentIndex = row.dataset.index;
        if (btn.classList.contains('add-row-btn')) addNewRow(currentIndex);
        if (btn.classList.contains('delete-row-btn')) {
            if (document.querySelectorAll('#itemsBody tr[data-index]').length > 1) { row.remove(); updateDisplayOrder(); }
            else alert('明細は最低 1 行必要です。');
        }
        if (btn.classList.contains('move-up-btn')) {
            const prevRow = row.previousElementSibling;
            if (prevRow && prevRow.dataset.index !== undefined) { row.parentNode.insertBefore(row, prevRow); updateDisplayOrder(); }
        }
        if (btn.classList.contains('move-down-btn')) {
            const nextRow = row.nextElementSibling;
            if (nextRow && nextRow.dataset.index !== undefined) { row.parentNode.insertBefore(nextRow, row); updateDisplayOrder(); }
        }
    });
    updateDisplayOrder();
    function calculateRowTotal(row) {
        const unitPriceInput = row.querySelector('.unit-price');
        const quantityInput = row.querySelector('.quantity');
        const totalInput = row.querySelector('.line-total-input');
        if (!unitPriceInput || !quantityInput || !totalInput) return;
        const price = parseFloat(unitPriceInput.value) || 0;
        const qty = parseFloat(quantityInput.value) || 0;
        totalInput.value = (price * qty).toLocaleString('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    document.getElementById('itemsBody').addEventListener('input', function (e) {
        if (e.target.classList.contains('unit-price') || e.target.classList.contains('quantity')) {
            calculateRowTotal(e.target.closest('tr'));
        }
    });
    document.querySelectorAll('#itemsBody tr[data-index]').forEach(row => calculateRowTotal(row));
})();
</script>

<style>
.required::after { content: " *"; color: #dc3545; }
.table-bordered td { border: 1px solid #dee2e6; }
#itemsTable tbody tr:hover { background-color: #f8f9fa; }
#itemsTable tbody tr td { vertical-align: middle; }
.gap-1 { gap: 0.25rem; }
#itemsTable .form-control-sm { padding: 0.125rem 0.25rem !important; font-size: 0.875rem !important; height: auto !important; }
#itemsTable tbody tr { height: 44px !important; }
#itemsTable td { padding: 0.25rem !important; vertical-align: middle; }
#itemsTable .btn-sm { padding: 0.125rem 0.25rem !important; font-size: 0.75rem !important; }
</style>
@endsection