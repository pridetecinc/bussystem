@extends('layouts.app')

@section('title', '請求書編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3 d-flex justify-content-between align-items-center">
                
                <!-- 左侧：面包屑导航 -->
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}">請求書管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">請求書編集</li>
                </ol>

                <!-- 右侧：三个操作按钮 (新規、コピー、入金) -->
                <div class="d-flex gap-2">
                    <!-- 1. 新規 (新建) -->
                    <a href="{{ route('masters.invoices.create', ['group_id' => $groupId]) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-lg"></i> 新規
                    </a>

                    <!-- 2. コピー (复制) -->
                    <a href="{{ route('masters.invoices.duplicate', $invoice->id) }}" 
                    class="btn btn-info btn-sm text-white"
                    onclick="return confirm('この請求書をコピーして新規作成しますか？');">
                        <i class="bi bi-copy"></i> コピー
                    </a>

                    <!-- 3. 【修改后】入金 (记录付款) - 触发模态框 -->
                    @if($invoice->total_amount > $invoice->paid_amount)
                        <button type="button" 
                                class="btn btn-primary btn-sm"
                                data-bs-toggle="modal" 
                                data-bs-target="#bulkReconcileModal" {{-- 注意：ID 必须是 bulkReconcileModal --}}
                                
                                {{-- 关键：属性名必须与 JS 中的 dataset.xxx 对应 (驼峰式) --}}
                                data-invoice-id="{{ $invoice->id }}"
                                value="{{ $invoice->id }}"
                                data-locked="{{ $invoice->is_locked ? 1 : 0 }}"
                                data-customer-id="{{ $invoice->agency_id }}"
                                data-invoice-no="{{ $invoice->invoice_number }}"
                                data-currency-code="{{ $invoice->currency_code }}"
                                data-customer-name="{{ $invoice->agency->agency_name ?? ''}}"
                                data-request-amount="{{ number_format($invoice->total_amount, 2, '.', '') }}" 
                                data-balance-amount="{{ number_format($invoice->total_amount - $invoice->paid_amount, 2, '.', '') }}"
                                
                                {{ $invoice->is_locked ? 'disabled' : '' }}>
                            <i class="bi bi-cash-coin"></i> 入金
                        </button>
                    @else
                        <!-- 已付清状态显示为禁用按钮 -->
                        <button type="button" 
                                class="btn btn-secondary btn-sm" 
                                disabled
                                title="全額入金済み">
                            <i class="bi bi-check-circle-fill"></i> 入金済
                        </button>
                    @endif
                </div>
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

            <div id="pdf-status-alert" class="alert alert-info alert-dismissible fade show" role="alert" style="display: none;">
                <i class="bi bi-info-circle-fill"></i> 
                <span id="pdf-status-message">PDF を生成中です...</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <form action="{{ route('masters.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="group_id" value="{{ $groupId }}">

                <!-- ================= 第一部分：基本情報 (与 Create 页完全一致) ================= -->
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
                                    <label for="agency_id" class="fw-bold me-2 mb-0">
                                        <i class="bi bi-building"></i> 代理店
                                    </label>
                                    <select class="form-select form-select-sm" id="agency_id" name="agency_id" style="width: auto; min-width: 100px;">
                                        <option value="0">-- 代理店を選択してください --</option>
                                        @foreach($agencies ?? [] as $agency)
                                            <option value="{{ $agency->id }}" 
                                                    {{ old('agency_id', $invoice->agency_id) == $agency->id ? 'selected' : '' }}
                                                    data-agency-name="{{ $agency->agency_name ?? '' }}"
                                                    data-agency-code="{{ $agency->agency_code ?? '' }}">
                                                {{ $agency->agency_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <textarea class="form-control" id="agency_detail" name="agency_detail" rows="3" placeholder="代理店を選択すると自動入力されます...">{{ old('agency_detail', $invoice->agency_detail) }}</textarea>
                            </div>

                            <!-- 右侧：8 个字段 (2 行 x 4 列) -->
                            <div class="col-lg-6">
                                <div class="row g-2">
                                    <!-- 第一行 -->
                                    <div class="col-md-3 col-6">
                                        <label for="staff_id" class="form-label small mb-1">請求担当</label>
                                        <select class="form-select form-select-sm" id="staff_id" name="staff_id">
                                            <option value="">-- 選択 --</option>
                                            @foreach($staffs ?? [] as $staff)
                                                <option value="{{ $staff->id }}" {{ old('staff_id', $invoice->staff_id) == $staff->id ? 'selected' : '' }}>
                                                    {{ $staff->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small mb-1">税込/税別</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="tax_mode" id="tax_mode_1" value="1" {{ old('tax_mode', $invoice->tax_mode) == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="tax_mode_1">税込</label>

                                            <input type="radio" class="btn-check" name="tax_mode" id="tax_mode_2" value="2" {{ old('tax_mode', $invoice->tax_mode) == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="tax_mode_2">税別</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small mb-1">言語</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="language" id="lang_1" value="1" {{ old('language', $invoice->language) == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="lang_1">日本語</label>

                                            <input type="radio" class="btn-check" name="language" id="lang_2" value="2" {{ old('language', $invoice->language) == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="lang_2">英語</label>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label class="form-label small mb-1">タイプ</label>
                                        <div class="btn-group w-100" role="group">
                                            <input type="radio" class="btn-check" name="type" id="type_1" value="1" {{ old('type', $invoice->type) == '1' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="type_1">正式</label>

                                            <input type="radio" class="btn-check" name="type" id="type_2" value="2" {{ old('type', $invoice->type) == '2' ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary btn-sm" for="type_2">臨時</label>
                                        </div>
                                    </div>

                                    <!-- 第二行 -->
                                    <div class="col-md-3 col-6">
                                        <label for="currency_code" class="form-label small mb-1">通貨</label>
                                        <select class="form-select form-select-sm" id="currency_code" name="currency_code">
                                            @foreach($currencies ?? [] as $currency)
                                                <option value="{{ $currency->currency_code }}" {{ old('currency_code', $invoice->currency_code) == $currency->currency_code ? 'selected' : '' }}>
                                                    {{ $currency->currency_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="invoice_date" class="form-label small mb-1">請求日</label>
                                        <input type="date" class="form-control form-control-sm" id="invoice_date" name="invoice_date" value="{{ old('invoice_date', $invoice->invoice_date) }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="operation_date" class="form-label small mb-1">運行日</label>
                                        <input type="date" class="form-control form-control-sm" id="operation_date" name="operation_date" value="{{ old('operation_date', $invoice->operation_date) }}">
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <label for="reservation_id" class="form-label small mb-1">予約 ID</label>
                                        <input type="text" class="form-control form-control-sm" id="reservation_id" name="reservation_id" value="{{ old('reservation_id', $invoice->reservation_id) }}">
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
                                    value="{{ old('billing_title', $invoice->billing_title) }}" placeholder="例：2024 年 3 月分請求書">
                                @error('billing_title') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="due_date" class="form-label fw-bold">支払指定日</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                    id="due_date" name="due_date" value="{{ old('due_date', $invoice->due_date) }}">
                                @error('due_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label for="bank_id" class="form-label fw-bold">入金銀行</label>
                                <select class="form-select @error('bank_id') is-invalid @enderror" id="bank_id" name="bank_id">
                                    @foreach($banks ?? [] as $bank)
                                        <option value="{{ $bank->id }}" {{ old('bank_id', $invoice->bank_id) == $bank->id ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold d-block text-center">ロック</label>
                                <input class="form-check-input" type="checkbox" role="switch" id="lock_switch" 
                                    {{ old('is_locked', $invoice->is_locked) ? 'checked' : '' }} style="display: none;">
                                
                                <div class="text-center mt-2" id="lock_icon_container" style="cursor: pointer; font-size: 2rem;">
                                    <i class="bi {{ old('is_locked', $invoice->is_locked) ? 'bi-lock-fill text-danger' : 'bi-unlock-fill text-success' }}"></i>
                                </div>
                                
                                <input type="hidden" name="is_locked" id="lock_hidden_input" value="{{ old('is_locked', $invoice->is_locked) ? '1' : '0' }}">
                            </div>
                        </div>

                        <!-- Row 3: 備考 -->
                        <div class="row">
                            <div class="col-12">
                                <label for="notes" class="form-label fw-bold">備考</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="請求書の下に表示する備考を入力してください。">{{ old('notes', $invoice->notes) }}</textarea>
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
                                        $oldItems = old('items');
                                        if ($oldItems === null && isset($items)) {
                                            $oldItems = $items->map(function ($item) {
                                                return [
                                                    'description' => $item->description,
                                                    'unit_price' => $item->unit_price,
                                                    'quantity' => $item->quantity,
                                                    'tax_rate' => $item->tax_rate,
                                                    'display_order' => $item->display_order,
                                                ];
                                            })->all();
                                        } elseif ($oldItems === null) {
                                            $oldItems = [];
                                        }
                                    @endphp

                                    @if(count($oldItems) > 0)
                                        @foreach($oldItems as $index => $oldItem)
                                            @php $index = (int)$index; $orderNumber = $index + 1; @endphp
                                            <tr data-index="{{ $index }}">
                                                <td class="text-center align-middle display-order">{{ $orderNumber }}</td>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm description" 
                                                        name="items[{{ $index }}][description]" list="product-list-{{ $index }}" 
                                                        value="{{ $oldItem['description'] ?? '' }}" placeholder="入力または選択">
                                                    <datalist id="product-list-{{ $index }}">
                                                        @foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach
                                                    </datalist>
                                                </td>
                                                <td><input type="number" class="form-control form-control-sm unit-price" name="items[{{ $index }}][unit_price]" value="{{ $oldItem['unit_price'] ?? '' }}" min="0" step="0.01"></td>
                                                <td><input type="number" class="form-control form-control-sm quantity" name="items[{{ $index }}][quantity]" value="{{ $oldItem['quantity'] ?? '' }}" min="1" step="1"></td>
                                                <td>
                                                    <select class="form-control form-control-sm tax-rate" name="items[{{ $index }}][tax_rate]">
                                                        <option value="10" {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == 10) ? 'selected' : '' }}>10</option>
                                                        <option value="8"  {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == 8)  ? 'selected' : '' }}>8</option>
                                                        <option value="-1" {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == -1) ? 'selected' : '' }}>免税</option>
                                                        <option value="-2" {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == -2) ? 'selected' : '' }}>非課税</option>
                                                    </select>
                                                </td>
                                                <td class="align-middle"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="{{ number_format(($oldItem['unit_price'] ?? 0) * ($oldItem['quantity'] ?? 0), 2) }}" readonly style="background-color: #f8f9fa; text-align: left;"></td>
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
                                    @else
                                        <tr data-index="0">
                                            <td class="text-center align-middle display-order">1</td>
                                            <td><input type="text" class="form-control form-control-sm description" name="items[0][description]" list="product-list-0" value="" placeholder="入力または選択"><datalist id="product-list-0">@foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach</datalist></td>
                                            <td><input type="number" class="form-control form-control-sm unit-price" name="items[0][unit_price]" value="" min="0" step="0.01"></td>
                                            <td><input type="number" class="form-control form-control-sm quantity" name="items[0][quantity]" value="" min="1" step="1"></td>
                                            <td><select class="form-control form-control-sm tax-rate" name="items[0][tax_rate]"><option value="10" selected>10</option><option value="8">8</option><option value="-1">免税</option><option value="-2">非課税</option></select></td>
                                            <td class="align-middle"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="0.00" readonly style="background-color: #f8f9fa; text-align: left;"></td>
                                            <td class="text-center align-middle"><div class="d-flex justify-content-center gap-1"><button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" tabindex="-1"><i class="bi bi-arrow-up"></i></button><button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" tabindex="-1"><i class="bi bi-arrow-down"></i></button><button type="button" class="btn btn-outline-success btn-sm add-row-btn" tabindex="-1"><i class="bi bi-plus-lg"></i></button><button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" tabindex="-1"><i class="bi bi-dash-lg"></i></button></div><input type="hidden" name="items[0][display_order]" value="1"></td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Template -->
                <template id="newRowTemplate">
                    <tr>
                        <td class="text-center align-middle display-order"></td>
                        <td><input type="text" class="form-control form-control-sm description" name="items[__index__][description]" list="product-list-__index__" value="" placeholder="入力または選択"><datalist id="product-list-__index__">@foreach($products ?? [] as $product) <option value="{{ $product->name }}"> @endforeach</datalist></td>
                        <td><input type="number" class="form-control form-control-sm unit-price" name="items[__index__][unit_price]" min="0" step="0.01" value=""></td>
                        <td><input type="number" class="form-control form-control-sm quantity" name="items[__index__][quantity]" min="1" step="1" value=""></td>
                        <td><select class="form-control form-control-sm tax-rate" name="items[__index__][tax_rate]"><option value="10" selected>10</option><option value="8">8</option><option value="-1">免税</option><option value="-2">非課税</option></select></td>
                        <td class="align-middle"><input type="text" tabindex="-1" class="form-control form-control-sm line-total-input" value="0.00" readonly style="background-color: #f8f9fa; text-align: left;"></td>
                        <td class="text-center align-middle"><div class="d-flex justify-content-center gap-1"><button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" tabindex="-1"><i class="bi bi-arrow-up"></i></button><button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" tabindex="-1"><i class="bi bi-arrow-down"></i></button><button type="button" class="btn btn-outline-success btn-sm add-row-btn" tabindex="-1"><i class="bi bi-plus-lg"></i></button><button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" tabindex="-1"><i class="bi bi-dash-lg"></i></button></div><input type="hidden" name="items[__index__][display_order]" value=""></td>
                    </tr>
                </template>

                <!-- 操作ボタン -->
                <div class="d-flex justify-content-between w-100 mb-4">
                    
                    <!-- 左侧：原有按钮组 (更新、PDF、取消) -->
                    <!-- 建议包裹在一个 div 中并添加 gap-2 让按钮之间有间距 -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 更新する
                        </button>
                        
                        @php
                            $hasPdf = !empty($invoice->pdf_file_path);
                            $pdfUrl = $hasPdf ? '/storage/' . $invoice->pdf_file_path : '';
                        @endphp
                        <a href="javascript:void(0)" 
                        data-invoice-id="{{ $invoice->id }}"
                        data-has-pdf="{{ $hasPdf ? '1' : '0' }}"
                        data-pdf-url="{{ $pdfUrl }}"
                        onclick="handlePdfClick(this)" 
                        class="btn btn-secondary btn-pdf-action">
                            <i class="bi bi-file-earmark-pdf"></i> <span class="btn-text">PDF 表示</span>
                        </a>
                        
                        <a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>

                     @if( !$invoice->is_locked && $invoice->total_amount == $invoice->paid_amount )
                    <form action="{{ route('masters.invoices.destroy', $invoice) }}" 
                        method="POST" 
                        class="d-inline ms-auto" 
                        onsubmit="return confirm('本当にこの請求書を削除しますか？復元できません。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> 削除する
                        </button>
                    </form>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
{{-- 引入公共销账模态框 --}}
@include('masters.invoices.components.bulk-reconcile-modal')
<script>
(function () {
    // 1. 代理店联动 (Edit Page Logic)
    const agencySelect = document.getElementById('agency_id');
    const clientDetailsTextarea = document.getElementById('agency_detail');
    if (agencySelect && clientDetailsTextarea) {
        // 初始化：如果页面加载时已有选中项，触发一次 change 以填充 textarea (防止 old() 有值但 JS 没运行)
        if(agencySelect.value) {
            const event = new Event('change');
            agencySelect.dispatchEvent(event);
        }

        agencySelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value === "") { 
                // 如果用户清空选择，是否清空 textarea? 这里选择保留用户手动修改的内容，或者你可以取消注释下一行来清空
                // clientDetailsTextarea.value = ""; 
                return; 
            }
            const agencyName = selectedOption.getAttribute('data-agency-name') || '';
            const agencyCode = selectedOption.getAttribute('data-agency-code') || '';
            const name = selectedOption.text;
            
            // 只有当 textarea 为空或者是默认提示语时才覆盖？或者直接覆盖？
            // 这里采用直接覆盖策略，确保数据一致性
            let detailsText = `会社名:${name}\n`;
            if (agencyCode) detailsText += `コード:${agencyCode}\n`;
            clientDetailsTextarea.value = detailsText;
        });
    }

    // 2. 锁开关 (Lock Switch Logic)
    const lockIconContainer = document.getElementById('lock_icon_container');
    const lockSwitch = document.getElementById('lock_switch');
    const lockHiddenInput = document.getElementById('lock_hidden_input');

    if (lockIconContainer && lockSwitch && lockHiddenInput) {
        lockIconContainer.addEventListener('click', function () {
            lockSwitch.checked = !lockSwitch.checked;
            updateLockIcon();
        });

        function updateLockIcon() {
            const iconElement = lockIconContainer.querySelector('i');
            if (lockSwitch.checked) {
                iconElement.className = 'bi bi-lock-fill text-danger';
                lockHiddenInput.value = '1';
            } else {
                iconElement.className = 'bi bi-unlock-fill text-success';
                lockHiddenInput.value = '0';
            }
        }
    }

    // 3. 表格逻辑 (Table Logic - Reused from Create)
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
        calculateRowTotal(newRow);
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
        
        // 计算总额并四舍五入到整数
        const total = Math.round(price * qty);
        
        // 格式化为带千分位的整数 (例如: 1,000)，如果不为0则显示数字，为0显示 "0"
        totalInput.value = total.toLocaleString('ja-JP');
    }

    document.getElementById('itemsBody').addEventListener('input', function (e) {
        if (e.target.classList.contains('unit-price') || e.target.classList.contains('quantity')) {
            calculateRowTotal(e.target.closest('tr'));
        }
    });
    
    // 初始化计算所有行
    document.querySelectorAll('#itemsBody tr[data-index]').forEach(row => calculateRowTotal(row));

    // 4. PDF 轮询逻辑 (Reused from Edit)
    const pollingTimers = {}; 

    function showStatusMessage(message, type = 'info') {
        const alertBox = document.getElementById('pdf-status-alert');
        if (!alertBox) return; 
        alertBox.className = `alert alert-${type} alert-dismissible fade show`;
        let iconClass = 'bi-info-circle-fill';
        if (type === 'success') iconClass = 'bi-check-circle-fill';
        if (type === 'warning') iconClass = 'bi-hourglass-split';
        if (type === 'danger') iconClass = 'bi-exclamation-triangle-fill';
        alertBox.innerHTML = `<i class="bi ${iconClass}"></i> <span>${message}</span><button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alertBox.style.display = 'block';
        if (type === 'success') {
            setTimeout(() => { if(alertBox) { const bsAlert = bootstrap.Alert.getOrCreateInstance(alertBox); if(bsAlert) bsAlert.close(); } }, 5000);
        }
    }

    window.handlePdfClick = function(btn) {
        const invoiceId = btn.dataset.invoiceId;
        const hasPdf = btn.dataset.hasPdf === '1';
        const pdfUrl = btn.dataset.pdfUrl;

        if (hasPdf && pdfUrl) {
            window.open(pdfUrl, '_blank');
            return;
        }
        if (pollingTimers[invoiceId]) {
            showStatusMessage('PDF はバックグラウンドで生成中です。完了すると自動的に開きます。', 'warning');
            return;
        }
        startPolling(invoiceId, btn);
    }

    function startPolling(invoiceId, btn) {
        setBtnLoadingState(btn, true);
        showStatusMessage('PDF を生成中です。完了まで数秒かかります...', 'info');
        checkStatus(invoiceId, btn);
        pollingTimers[invoiceId] = setInterval(() => { checkStatus(invoiceId, btn); }, 2000);
    }

    function checkStatus(invoiceId, btn) {
        fetch(`/masters/invoices/${invoiceId}/pdf-status`)
            .then(response => response.json())
            .then(data => {
                const isReady = (data.ready === true || data.ready === 'true');
                if (isReady) {
                    clearInterval(pollingTimers[invoiceId]);
                    delete pollingTimers[invoiceId];
                    btn.dataset.hasPdf = '1';
                    btn.dataset.pdfUrl = data.url;
                    setBtnLoadingState(btn, false);
                    showStatusMessage('PDF の準備ができました！表示しています...', 'success');
                    window.open(data.url, '_blank');
                    btn.querySelector('.btn-text').textContent = 'PDF を開く';
                    btn.classList.remove('btn-secondary');
                    btn.classList.add('btn-success');
                }
            })
            .catch(err => console.error('轮询失败', err));
    }

    function setBtnLoadingState(btn, isLoading) {
        const textSpan = btn.querySelector('.btn-text');
        if (isLoading) {
            btn.classList.add('disabled');
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
            if (!btn.querySelector('.spinner-border')) {
                const spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-2';
                btn.insertBefore(spinner, textSpan);
            }
            textSpan.textContent = '生成中...';
        } else {
            btn.classList.remove('disabled', 'btn-secondary');
            btn.classList.add('btn-success'); 
            const spinner = btn.querySelector('.spinner-border');
            if (spinner) spinner.remove();
            textSpan.textContent = 'PDF を開く'; 
        }
    }

        document.addEventListener('DOMContentLoaded', function () {
        const reconcileModal = document.getElementById('reconcileModal');

        // 如果页面上没有这个模态框（例如组件引入失败），则直接退出
        if (!reconcileModal) {
            console.warn('Reconcile Modal not found on this page.');
            return;
        }

        // 检查 Bootstrap 是否已加载
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap JS is not loaded! Cannot initialize modal logic.');
            return;
        }

        // 监听模态框隐藏事件（可选：用于清空表单或重置状态）
        reconcileModal.addEventListener('hidden.bs.modal', function () {
            const form = reconcileModal.querySelector('form');
            if (form) form.reset();
            // 清除可能的错误提示
            reconcileModal.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            reconcileModal.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
        });


    });

    document.addEventListener('DOMContentLoaded', function () {
        const modalEl = document.getElementById('bulkReconcileModal');
        if (!modalEl) return;

        // 监听模态框即将显示的事件
        modalEl.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            // 1. 检查是否是从 Edit 页面的按钮触发的 (通过检查 data-invoice-id)
            const invoiceId = button.getAttribute('data-invoice-id');
            if (!invoiceId) {
                // 如果没有 ID，说明可能是从其他逻辑触发的，或者是空的，直接返回
                console.warn('未检测到发票 ID，跳过渲染。');
                return;
            }

            console.log('🚀 Edit 页触发单条入金渲染，ID:', invoiceId);

            // 2. 准备数据对象 (从按钮的 data 属性获取)
            // 注意：确保 HTML 按钮上的属性名与这里一致 (data-invoice-no, data-balance-amount 等)
            const singleData = {
                id: invoiceId,
                invoice_no: button.getAttribute('data-invoice-no') || 'No ID',
                customer_name: button.getAttribute('data-customer-name') || '未知',
                currency_code: button.getAttribute('data-currency-code') || 'JPY',
                request_amount: parseFloat(button.getAttribute('data-request-amount')) || 0,
                balance_amount: parseFloat(button.getAttribute('data-balance-amount')) || 0
            };

            // 3. 获取模态框内部的关键元素
            const container = document.getElementById('reconcile-items-container');
            const template = document.getElementById('invoice-item-template');
            const initialModalEl = document.getElementById('initialActionModal');

            if (!container || !template) {
                console.error('❌ 找不到模态框容器或模板元素！');
                return;
            }

            // 4. 【关键步骤】如果存在“第一步选择模态框”，立即关闭它
            // 因为单条操作不需要问用户选“详细”还是“全额”，直接进入详情页
            if (initialModalEl) {
                const initialModalInstance = bootstrap.Modal.getInstance(initialModalEl);
                if (initialModalInstance) {
                    initialModalInstance.hide();
                }
                // 防止初始模态框的遮罩层残留
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
            }

            // 5. 手动执行渲染逻辑 (复制自 bulk-reconcile-modal 的核心逻辑)
            container.innerHTML = ''; // 清空旧内容
            const index = 0; // 单条模式索引为 0
            
            const clone = document.importNode(template.content, true);

            // --- 填充具体数据 ---
            clone.querySelector('.item-invoice-id').value = singleData.id;
            clone.querySelector('.item-original-amount').value = singleData.request_amount;
            clone.querySelector('.item-invoice-no').textContent = singleData.invoice_no;
            clone.querySelector('.item-invoice-no').title = singleData.invoice_no; // 设置 title 用于截断显示
            clone.querySelector('.item-customer-name').textContent = singleData.customer_name;
            clone.querySelector('.item-customer-name').title = singleData.customer_name;
            clone.querySelector('.item-request-amount').textContent = singleData.request_amount.toLocaleString('ja-JP');

            // 货币符号
            const currencySpan = clone.querySelector('.item-currency-display');
            if(currencySpan) currencySpan.textContent = singleData.currency_code;
            
            // 余额和输入框逻辑
            const balanceEl = clone.querySelector('.item-balance-amount');
            const amountInput = clone.querySelector('.item-payment-amount');
            
            if (singleData.balance_amount <= 0.005) {
                // 已付清
                balanceEl.textContent = "0";
                balanceEl.className = 'fw-bold font-monospace text-secondary item-balance-amount';
                amountInput.value = "0.00";
                amountInput.disabled = true;
                amountInput.classList.add('bg-light', 'text-muted');
                amountInput.setAttribute('data-max-amount', "0");
            } else {
                // 有余额
                balanceEl.textContent = singleData.balance_amount.toLocaleString('ja-JP');
                // 默认填入余额
                amountInput.value = singleData.balance_amount.toFixed(2); 
                amountInput.disabled = false;
                amountInput.setAttribute('data-max-amount', singleData.balance_amount);
                
                // 移除可能的错误样式
                amountInput.classList.remove('is-invalid', 'bg-danger', 'text-white', 'bg-light', 'text-muted');
                
                // 绑定输入事件以更新底部统计 (如果全局函数存在)
                amountInput.addEventListener('input', function() {
                    const val = parseFloat(this.value) || 0;
                    const max = parseFloat(this.getAttribute('data-max-amount')) || 0;
                    this.classList.toggle('is-invalid', val > max + 0.001);
                    if (typeof updateFooterStats === 'function') updateFooterStats();
                });
            }

            // --- 修正表单 name 属性 (将 {index} 替换为 0) ---
            clone.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if(name) {
                    el.setAttribute('name', name.replace('{index}', index));
                }
            });

            // --- 绑定删除按钮 (单条模式下删除即关闭) ---
            const closeBtn = clone.querySelector('.btn-close');
            if(closeBtn) {
                closeBtn.addEventListener('click', function() {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if(modalInstance) modalInstance.hide();
                });
            }

            // 将渲染好的卡片加入容器
            container.appendChild(clone);

            // 6. 更新底部统计栏
            if (typeof updateFooterStats === 'function') {
                updateFooterStats();
            } else {
                // 兜底：如果全局函数还没加载，手动设置计数
                document.getElementById('reconcile-count').textContent = '1';
                document.getElementById('reconcile-count-footer').textContent = '1';
                document.getElementById('footer-request-total').textContent = singleData.request_amount.toLocaleString('ja-JP');
                document.getElementById('footer-balance-total').textContent = singleData.balance_amount.toLocaleString('ja-JP');
                document.getElementById('footer-payment-total').textContent = singleData.balance_amount.toLocaleString('ja-JP');
                document.getElementById('footer-currency-label').textContent = singleData.currency_code;
            }
        });
        
        // 监听模态框隐藏，清理可能残留的 backdrop (防止多层遮罩)
        modalEl.addEventListener('hidden.bs.modal', function () {
            // 可选：如果需要每次关闭都重置表单，可以在这里加 logic
            // 但通常不需要，因为下次 show 时会重新 innerHTML = ''
        });
    });

    window.addEventListener('beforeunload', () => {
        Object.keys(pollingTimers).forEach(id => { clearInterval(pollingTimers[id]); delete pollingTimers[id]; });
    });
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