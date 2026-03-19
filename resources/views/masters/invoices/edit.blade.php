@extends('layouts.app')

@section('title', '請求書編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}">請求書管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">請求書編集</li>
                </ol>
            </nav>

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
                </h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Main Form -->
            <form action="{{ route('masters.invoices.update', $invoice) }}" method="POST" id="invoiceForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="group_id" value="{{ $groupId }}">

                <!-- 請求書基本情報カード -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> 請求書基本情報
                        </h5>
                    </div>
                    <div class="card-body p-2">
                        <div class="row g-2">
                            <!-- 账单标题 -->
                            <div class="col-md-4">
                                <label for="billing_title" class="form-label  mb-1">タイトル</label>
                                <input type="text" class="form-control @error('billing_title') is-invalid @enderror"
                                    id="billing_title" name="billing_title"
                                    value="{{ old('billing_title', $invoice->billing_title) }}"
                                    maxlength="100" placeholder="">
                                @error('billing_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 计税模式 -->
                            <div class="col-md-4">
                                <label for="tax_mode" class="form-label required mb-1">税込/税別</label>
                                <select class="form-select @error('tax_mode') is-invalid @enderror"
                                        id="tax_mode" name="tax_mode" required>
                                    <option value="1" {{ (old('tax_mode', $invoice->tax_mode) == 1) ? 'selected' : '' }}>税込</option>
                                    <option value="2" {{ (old('tax_mode', $invoice->tax_mode) == 2) ? 'selected' : '' }}>税別</option>
                                </select>
                                @error('tax_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 语言 -->
                            <div class="col-md-4">
                                <label for="language" class="form-label required mb-1">言語</label>
                                <select class="form-select @error('language') is-invalid @enderror"
                                        id="language" name="language" required>
                                    <option value="1" {{ (old('language', $invoice->language) == 1) ? 'selected' : '' }}>日本語</option>
                                    <option value="2" {{ (old('language', $invoice->language) == 2) ? 'selected' : '' }}>英語</option>
                                </select>
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 通貨 -->
                            <div class="col-md-4">
                                <label for="currency_code" class="form-label required mb-1">通貨</label>
                                <select class="form-select @error('currency_code') is-invalid @enderror"
                                        id="currency_code" name="currency_code" required>
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->currency_code }}"
                                            {{ (old('currency_code', $invoice->currency_code) ==  $currency->currency_code) ? 'selected' : '' }}>
                                            {{ $currency->currency_code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 開票日 -->
                            <div class="col-md-4">
                                <label for="invoice_date" class="form-label required mb-1">請求日</label>
                                <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                                    id="invoice_date" name="invoice_date"
                                    value="{{ old('invoice_date', $invoice->invoice_date) }}" required>
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 支払期日 -->
                            <div class="col-md-4">
                                <label for="due_date" class="form-label required mb-1">支払期日</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                    id="due_date" name="due_date"
                                    value="{{ old('due_date', $invoice->due_date) }}" placeholder="年/月/日">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 特記事項 -->
                            <div class="col-md-4">
                                <label for="notes" class="form-label mb-1">備考</label>
                                <input type="text" class="form-control @error('notes') is-invalid @enderror"
                                    id="notes" name="notes"
                                    value="{{ old('notes', $invoice->notes) }}" placeholder="請求書の下に表示する。">
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 入金银行 -->
                            <div class="col-md-4">
                                <label for="bank_id" class="form-label required mb-1">入金銀行</label>
                                <select class="form-select @error('bank_id') is-invalid @enderror"
                                        id="bank_id" name="bank_id" required>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->id }}"
                                            {{ (old('bank_id', $invoice->bank_id ?? '') == $bank->id) ? 'selected' : '' }}>
                                            {{ $bank->bank_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('bank_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 属性 -->
                            <div class="col-md-4">
                                <label for="type" class="form-label required mb-1">タイプ</label>
                                <select class="form-select @error('type') is-invalid @enderror"
                                        id="type" name="type" required>
                                    <option value="1" {{ (old('type', $invoice->type) == 1) ? 'selected' : '' }}>正式</option>
                                    <option value="2" {{ (old('type', $invoice->type) == 2) ? 'selected' : '' }}>臨時</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 請求明細カード -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-list-task"></i> 請求明細
                        </h5>
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
                                        <th style="width: 200px;">内容</th>
                                        <th style="width: 150px;">単価</th>
                                        <th style="width: 100px;">数量</th>
                                        <th style="width: 120px;">税率 (%)</th>
                                        <th style="width: 180px;">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php
                                        // 优先使用 old()（验证失败时），否则用数据库数据
                                        $oldItems = old('items');
                                        if ($oldItems === null) {
                                            $oldItems = $items->map(function ($item) {
                                                return [
                                                    'description' => $item->description,
                                                    'unit_price' => $item->unit_price,
                                                    'quantity' => $item->quantity,
                                                    'tax_rate' => $item->tax_rate,
                                                    'display_order' => $item->display_order,
                                                ];
                                            })->all();
                                        }
                                    @endphp

                                    @if(count($oldItems) > 0)
                                        @foreach($oldItems as $index => $oldItem)
                                            @php
                                                $index = (int)$index;
                                                $orderNumber = $index + 1;
                                            @endphp
                                            <tr data-index="{{ $index }}">
                                                <td class="text-center align-middle display-order">{{ $orderNumber }}</td>
                                                <td>
                                                    <select class="form-select form-select-sm description" name="items[{{ $index }}][description]">
                                                        <option value="">-- 選択してください --</option>
                                                        @foreach($products as $product)
                                                            <option value="{{ $product->name }}" 
                                                                    {{ (isset($oldItem['description']) && $oldItem['description'] == $product->name) ? 'selected' : '' }}>
                                                                {{ $product->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm unit-price"
                                                           name="items[{{ $index }}][unit_price]"
                                                           value="{{ $oldItem['unit_price'] ?? "" }}" min="0" step="0.01">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm quantity"
                                                           name="items[{{ $index }}][quantity]"
                                                           value="{{ $oldItem['quantity'] ?? "" }}" min="1" step="1">
                                                </td>
                                                <td>
                                                    <select class="form-control form-control-sm tax-rate" name="items[{{ $index }}][tax_rate]">
                                                        <option value="10" {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == 10) ? 'selected' : '' }}>10</option>
                                                        <option value="8"  {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == 8)  ? 'selected' : '' }}>8</option>
                                                        <option value="0"  {{ (isset($oldItem['tax_rate']) && $oldItem['tax_rate'] == 0)  ? 'selected' : '' }}>0</option>
                                                    </select>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                                            <i class="bi bi-arrow-up"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                                            <i class="bi bi-arrow-down"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                                            <i class="bi bi-plus-lg"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                                            <i class="bi bi-dash-lg"></i>
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="items[{{ $index }}][display_order]" value="{{ $orderNumber }}">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        {{-- fallback: 至少一行 --}}
                                        <tr data-index="0">
                                            <td class="text-center align-middle display-order">1</td>
                                            <td><input type="text" class="form-control form-control-sm description" name="items[0][description]" placeholder=""></td>
                                            <td><input type="number" class="form-control form-control-sm unit-price" name="items[0][unit_price]" value="" min="0" step="0.01"></td>
                                            <td><input type="number" class="form-control form-control-sm quantity" name="items[0][quantity]" value="" min="1" step="1"></td>
                                            <td>
                                                <select class="form-control form-control-sm tax-rate" name="items[0][tax_rate]">
                                                    <option value="10" selected>10</option>
                                                    <option value="8">8</option>
                                                    <option value="0">0</option>
                                                </select>
                                            </td>
                                            <td class="text-center align-middle">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動"><i class="bi bi-arrow-up"></i></button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動"><i class="bi bi-arrow-down"></i></button>
                                                    <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加"><i class="bi bi-plus-lg"></i></button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除"><i class="bi bi-dash-lg"></i></button>
                                                </div>
                                                <input type="hidden" name="items[0][display_order]" value="1">
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 模板（用于 JS 动态添加） -->
                <template id="newRowTemplate">
                    <tr>
                        <td class="text-center align-middle display-order"></td>
                        <td>
                            <select class="form-select form-select-sm description" name="items[__index__][description]">
                                <option value="">-- 選択してください --</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->name }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm unit-price"
                                   name="items[__index__][unit_price]" min="0" step="0.01" value="">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm quantity"
                                   name="items[__index__][quantity]" min="1" step="1" value="">
                        </td>
                        <td>
                            <select class="form-control form-control-sm tax-rate" name="items[__index__][tax_rate]">
                                <option value="10" selected>10</option>
                                <option value="8">8</option>
                                <option value="0">0</option>
                            </select>
                        </td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn" title="上へ移動">
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn" title="下へ移動">
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm add-row-btn" title="行を追加">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn" title="行を削除">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                            </div>
                            <input type="hidden" name="items[__index__][display_order]" value="">
                        </td>
                    </tr>
                </template>

                <!-- 操作ボタン -->
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 更新する
                        </button>
                        @php
                            $hasPdf = !empty($invoice->pdf_file_path);
                            $pdfUrl = $hasPdf ? '/storage/' . $invoice->pdf_file_path : '';
                        @endphp

                        <a href="javascript:void(0)" 
                        onclick="if({{ $hasPdf ? 'true' : 'false' }}) { window.open('{{ $pdfUrl }}', '_blank'); } else { alert('PDF はバックグラウンドで生成中です。完了まで 5〜10 秒ほどかかる見込みですので、しばらくしてから再度開いてください'); }" 
                        class="btn btn-secondary">
                            <i class="bi bi-file-earmark-pdf"></i> PDF表示
                        </a>
                        <a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 复用 create 的 JS --}}
<script>
(function () {
    const existingRows = document.querySelectorAll('#itemsBody tr[data-index]');
    let globalRowIndex = existingRows.length;

    function updateDisplayOrder() {
        document.querySelectorAll('#itemsBody tr[data-index]').forEach((row, idx) => {
            const order = idx + 1;
            row.querySelector('.display-order').textContent = order;
            const hiddenInput = row.querySelector('input[name$="[display_order]"]');
            if (hiddenInput) {
                hiddenInput.value = order;
            }
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
            if (targetRow && targetRow.nextElementSibling) {
                tbody.insertBefore(newRow, targetRow.nextElementSibling);
            } else {
                tbody.appendChild(newRow);
            }
        } else {
            tbody.appendChild(newRow);
        }
        updateDisplayOrder();
    }

    document.getElementById('addItemRowBtn').addEventListener('click', () => addNewRow());

    document.getElementById('itemsBody').addEventListener('click', function (e) {
        const btn = e.target.closest('.add-row-btn, .delete-row-btn, .move-up-btn, .move-down-btn');
        if (!btn) return;

        const row = btn.closest('tr');
        const currentIndex = row.dataset.index;

        if (btn.classList.contains('add-row-btn')) {
            addNewRow(currentIndex);
        }

        if (btn.classList.contains('delete-row-btn')) {
            if (document.querySelectorAll('#itemsBody tr[data-index]').length > 1) {
                row.remove();
                updateDisplayOrder();
            } else {
                alert('明細は最低1行必要です。');
            }
        }

        if (btn.classList.contains('move-up-btn')) {
            const prevRow = row.previousElementSibling;
            if (prevRow && prevRow.dataset.index !== undefined) {
                row.parentNode.insertBefore(row, prevRow);
                updateDisplayOrder();
            }
        }

        if (btn.classList.contains('move-down-btn')) {
            const nextRow = row.nextElementSibling;
            if (nextRow && nextRow.dataset.index !== undefined) {
                row.parentNode.insertBefore(nextRow, row);
                updateDisplayOrder();
            }
        }
    });

    updateDisplayOrder();
})();
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.bg-light { background-color: #f8f9fa !important; }
.table-bordered td { border: 1px solid #dee2e6; }
#itemsTable tbody tr:hover { background-color: #f8f9fa; }
#itemsTable tbody tr td { vertical-align: middle; }
.gap-1 { gap: 0.25rem; }

#itemsTable .form-control-sm {
    padding: 0.125rem 0.25rem !important;
    font-size: 0.875rem !important;
    height: auto !important;
}

#itemsTable tbody tr {
    height: 44px !important;
}

#itemsTable td {
    padding: 0.25rem !important;
}

#itemsTable .btn-sm {
    padding: 0.125rem 0.25rem !important;
    font-size: 0.75rem !important;
}
</style>

@endsection