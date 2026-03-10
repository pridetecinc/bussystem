@extends('layouts.app')

@section('title', '請求書作成')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
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
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
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
            <form action="{{ route('masters.invoices.store') }}" method="POST" id="invoiceForm">
                @csrf
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
                                <label for="billing_title" class="form-label required mb-1">账单标题</label>
                                <input type="text" class="form-control @error('billing_title') is-invalid @enderror"
                                    id="billing_title" name="billing_title"
                                    value="{{ old('billing_title') }}"
                                    maxlength="100" placeholder="">
                                @error('billing_title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 计税模式 -->
                            <div class="col-md-4">
                                <label for="tax_mode" class="form-label required mb-1">计税模式</label>
                                <select class="form-select @error('tax_mode') is-invalid @enderror"
                                        id="tax_mode" name="tax_mode" required>
                                    <option value="1" {{ old('tax_mode', '1') == '1' ? 'selected' : '' }}>税入</option>
                                    <option value="2" {{ old('tax_mode', '1') == '2' ? 'selected' : '' }}>税别</option>
                                </select>
                                @error('tax_mode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 计税模式 -->
                            <div class="col-md-4">
                                <label for="language" class="form-label required mb-1">语言</label>
                                <select class="form-select @error('language') is-invalid @enderror"
                                        id="language" name="language" required>
                                    <option value="1" {{ old('language', '1') == '1' ? 'selected' : '' }}>日语</option>
                                    <option value="2" {{ old('language', '1') == '2' ? 'selected' : '' }}>英语</option>
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
                                        {{-- 
                                            逻辑说明：
                                            1. old('currency_code', ...): 优先取验证失败后的输入值
                                            2. $currentItem->currency_code ?? '': 如果是编辑页面，取数据库的值；如果是新增，则为空
                                            3. 如果上述两者相等 且 等于当前循环的 $currency->currency_code，则选中
                                        --}}
                                        <option value="{{ $currency->currency_code }}" 
                                                {{ old('currency_code', $currentItem->currency_code ?? '') == $currency->currency_code ? 'selected' : '' }}>
                                            {{ $currency->currency_code }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('currency_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 開票日 -->
                            <div class="col-md-4">
                                <label for="invoice_date" class="form-label required mb-1">請求日</label>
                                <input type="date" class="form-control @error('invoice_date') is-invalid @enderror"
                                    id="invoice_date" name="invoice_date"
                                    value="{{ old('invoice_date', now()->format('Y-m-d')) }}" required>
                                @error('invoice_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 支払期日 -->
                            <div class="col-md-4">
                                <label for="due_date" class="form-label required mb-1">支払期日</label>
                                <input type="date" class="form-control @error('due_date') is-invalid @enderror"
                                    id="due_date" name="due_date"
                                    value="{{ old('due_date') }}" placeholder="年/月/日">
                                @error('due_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- 特記事項 -->
                            <div class="col-md-8">
                                <label for="notes" class="form-label mb-1">特記事項</label>
                                <input type="text" class="form-control @error('notes') is-invalid @enderror"
                                    id="notes" name="notes"
                                    value="{{ old('notes') }}" placeholder="特記事項">
                                @error('notes')
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
                                        <th style="width: 50px;">日次</th>
                                        <th style="width: 200px;">品目名</th>
                                        <th style="width: 150px;">単価</th>
                                        <th style="width: 100px;">数量</th>
                                        <th style="width: 120px;">税率 (%)</th>
                                        <th style="width: 180px;">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    @php
                                        $oldItems = old('items', []);
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
                                                    <input type="text"
                                                        class="form-control form-control-sm description"
                                                        name="items[{{ $index }}][description]"
                                                        value="{{ $oldItem['description'] ?? '' }}"
                                                        maxlength="500"
                                                        placeholder="">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm unit-price"
                                                           name="items[{{ $index }}][unit_price]"
                                                           value="{{ $oldItem['unit_price'] ?? '' }}" min="0" step="0.01">
                                                </td>
                                                <td>
                                                    <input type="number" class="form-control form-control-sm quantity"
                                                           name="items[{{ $index }}][quantity]"
                                                           value="{{ $oldItem['quantity'] ?? '' }}" min="1" step="1">
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
                                        <tr data-index="0">
                                            <td class="text-center align-middle display-order">1</td>
                                            <td>
                                                <input type="text"
                                                    class="form-control form-control-sm description"
                                                    name="items[0][description]"
                                                    value=""
                                                    maxlength="500"
                                                    placeholder="">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm unit-price" name="items[0][unit_price]" value="" min="0" step="0.01">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm quantity" name="items[0][quantity]" value="" min="1" step="1">
                                            </td>
                                            <td>
                                                <select class="form-control form-control-sm tax-rate" name="items[0][tax_rate]">
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
                                                <input type="hidden" name="items[0][display_order]" value="1">
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 👇 新增：模板放在这里（在 table 之后） -->
                <template id="newRowTemplate">
                    <tr>
                        <td class="text-center align-middle display-order"></td>
                        <td>
                            <input type="text"
                                class="form-control form-control-sm description"
                                name="items[__index__][description]"
                                value=""
                                maxlength="500"
                                placeholder="">
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
                            <i class="bi bi-check-circle"></i> 登録する
                        </button>
                        <a href="{{ route('masters.invoices.index', ['group_id' => $groupId]) }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    // ✅ 从现有行数初始化 index（安全可靠）
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
        // ✅ 关键：从 <template> 的 content 中克隆
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

    // 顶部“行を追加”按钮
    document.getElementById('addItemRowBtn').addEventListener('click', () => {
        addNewRow();
    });

    // 事件委托
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

    // 初始化顺序
    updateDisplayOrder();
})();
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.bg-light { background-color: #f8f9fa !important; }
.bg-white { background-color: #ffffff !important; }
.table-bordered td { border: 1px solid #dee2e6; }
#itemsTable tbody tr:hover { background-color: #f8f9fa; }
#itemsTable tbody tr td { vertical-align: middle; }
.gap-1 { gap: 0.25rem; }

#itemsTable .form-control-sm {
    padding: 0.125rem 0.25rem !important; /* 默认是 0.25rem 0.5rem */
    font-size: 0.875rem !important;
    height: auto !important;
    min-height: auto !important;
}

#itemsTable tbody tr {
    height: 44px !important; /* 可根据需要调整：40px / 42px / 44px */
}

#itemsTable td {
    padding: 0.25rem !important; /* 默认表格单元格 padding 是 0.5rem */
    vertical-align: middle;
}

/* 按钮也缩小一点 */
#itemsTable .btn-sm {
    padding: 0.125rem 0.25rem !important;
    font-size: 0.75rem !important;
}
</style>



@endsection