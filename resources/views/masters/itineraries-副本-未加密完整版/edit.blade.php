@extends('layouts.app')

@section('title', '行程編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.itineraries.index') }}">行程管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">行程編集</li>
                </ol>
            </nav>
            
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
            
            <form action="{{ route('masters.itineraries.update', $itinerary) }}" method="POST" id="itineraryForm">
                @csrf
                @method('PUT')
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-info-circle"></i> 行程基本情報
                        </h5>
                    </div>
                    
                    <div class="card-body">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-light" style="width: 25%; padding: 0.5rem;">
                                        <label for="itinerary_code" class="form-label required mb-0">行程コード</label>
                                    </td>
                                    <td class="bg-white" style="width: 50%; padding: 0.5rem;">
                                        <input type="text" class="form-control @error('itinerary_code') is-invalid @enderror" 
                                               id="itinerary_code" name="itinerary_code" 
                                               value="{{ old('itinerary_code', $itinerary->itinerary_code) }}" 
                                               required maxlength="20" placeholder="例: IT001">
                                        @error('itinerary_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="width: 25%; padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">※ 必須、20文字以内、他と重複不可</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <label for="itinerary_name" class="form-label required mb-0">行程名</label>
                                    </td>
                                    <td class="bg-white" style="padding: 0.5rem;">
                                        <input type="text" class="form-control @error('itinerary_name') is-invalid @enderror" 
                                               id="itinerary_name" name="itinerary_name" 
                                               value="{{ old('itinerary_name', $itinerary->itinerary_name) }}" 
                                               required maxlength="100" placeholder="例: 東京一日観光コース">
                                        @error('itinerary_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">※ 必須、100文字以内</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <label for="category" class="form-label mb-0">カテゴリー</label>
                                    </td>
                                    <td class="bg-white" style="padding: 0.5rem;">
                                        <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                               id="category" name="category" 
                                               value="{{ old('category', $itinerary->category) }}"
                                               maxlength="50" placeholder="例: 観光、ビジネス、教育">
                                        @error('category')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light" style="padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">50文字以内</small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <td class="bg-light align-middle" style="padding: 0.5rem;">
                                        <label for="remarks" class="form-label mb-0">備考</label>
                                    </td>
                                    <td class="bg-white" style="padding: 0.5rem;">
                                        <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                                  id="remarks" name="remarks" rows="3"
                                                  maxlength="500" placeholder="例: 行程の詳細説明、注意事項など">{{ old('remarks', $itinerary->remarks) }}</textarea>
                                        @error('remarks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td class="bg-light align-middle" style="padding: 0.5rem;">
                                        <small class="form-text text-muted mb-0">500文字以内</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-day"></i> 行程詳細（日次行程）
                        </h5>
                        <button type="button" class="btn btn-light btn-sm" id="addDetailRowBtn">
                            <i class="bi bi-plus-lg"></i> 行を追加
                        </button>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="detailsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">日次</th>
                                        <th style="width: 150px;">開始時刻</th>
                                        <th style="width: 150px;">終了時刻</th>
                                        <th>行程説明</th>
                                        <th style="width: 150px;">備考</th>
                                        <th style="width: 180px;">操作</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsBody">
                                    @php
                                        $oldDetails = old('details', []);
                                        $detailIndex = 0;
                                    @endphp
                                    
                                    @if(count($oldDetails) > 0)
                                        @foreach($oldDetails as $index => $oldDetail)
                                        @php
                                            $index = (int)$index;
                                            $orderNumber = $index + 1;
                                        @endphp
                                        <tr data-index="{{ $index }}" data-id="{{ $oldDetail['id'] ?? '' }}">
                                            <td class="text-center align-middle display-order">{{ $orderNumber }}</td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm arrival-time" 
                                                       name="details[{{ $index }}][arrival_time]" 
                                                       value="{{ $oldDetail['arrival_time'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm departure-time" 
                                                       name="details[{{ $index }}][departure_time]" 
                                                       value="{{ $oldDetail['departure_time'] ?? '' }}">
                                            </td>
                                            <td>
                                                <textarea class="form-control form-control-sm description" 
                                                          name="details[{{ $index }}][description]" 
                                                          rows="2" maxlength="500" 
                                                          placeholder="行程の説明">{{ $oldDetail['description'] ?? '' }}</textarea>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="details[{{ $index }}][remark]" 
                                                       value="{{ $oldDetail['remark'] ?? '' }}" 
                                                       maxlength="255" placeholder="備考">
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
                                                <input type="hidden" name="details[{{ $index }}][id]" value="{{ $oldDetail['id'] ?? '' }}">
                                                <input type="hidden" name="details[{{ $index }}][display_order]" value="{{ $orderNumber }}">
                                            </td>
                                        </tr>
                                        @php $detailIndex = $index + 1; @endphp
                                        @endforeach
                                    @else
                                        @foreach($itinerary->details as $index => $detail)
                                        <tr data-index="{{ $index }}" data-id="{{ $detail->id }}">
                                            <td class="text-center align-middle display-order">{{ $index + 1 }}</td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm arrival-time" 
                                                       name="details[{{ $index }}][arrival_time]" 
                                                       value="{{ $detail->arrival_time ? $detail->arrival_time->format('H:i') : '' }}">
                                            </td>
                                            <td>
                                                <input type="time" class="form-control form-control-sm departure-time" 
                                                       name="details[{{ $index }}][departure_time]" 
                                                       value="{{ $detail->departure_time ? $detail->departure_time->format('H:i') : '' }}">
                                            </td>
                                            <td>
                                                <textarea class="form-control form-control-sm description" 
                                                          name="details[{{ $index }}][description]" 
                                                          rows="2" maxlength="500" 
                                                          placeholder="行程の説明">{{ $detail->description }}</textarea>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm remark" 
                                                       name="details[{{ $index }}][remark]" 
                                                       value="{{ $detail->remark }}" 
                                                       maxlength="255" placeholder="備考">
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
                                                <input type="hidden" name="details[{{ $index }}][id]" value="{{ $detail->id }}">
                                                <input type="hidden" name="details[{{ $index }}][display_order]" value="{{ $index + 1 }}">
                                            </td>
                                        </tr>
                                        @php $detailIndex = $index + 1; @endphp
                                        @endforeach
                                    @endif
                                    
                                    <tr id="newRowTemplate" class="d-none">
                                        <td class="text-center align-middle display-order"></td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm arrival-time" name="details[__index__][arrival_time]">
                                        </td>
                                        <td>
                                            <input type="time" class="form-control form-control-sm departure-time" name="details[__index__][departure_time]">
                                        </td>
                                        <td>
                                            <textarea class="form-control form-control-sm description" 
                                                      name="details[__index__][description]" 
                                                      rows="2" maxlength="500" placeholder="行程の説明"></textarea>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm remark" 
                                                   name="details[__index__][remark]" maxlength="255" placeholder="備考">
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
                                            <input type="hidden" name="details[__index__][id]" value="">
                                            <input type="hidden" name="details[__index__][display_order]" value="">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> 全ての変更を保存
                        </button>
                        <a href="{{ route('masters.itineraries.index') }}" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> キャンセル
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="if(confirm('本当にこの行程を削除しますか？\\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                            <i class="bi bi-trash"></i> 行程全体を削除
                        </button>
                    </div>
                </div>
            </form>
            
            <form id="deleteForm" action="{{ route('masters.itineraries.destroy', $itinerary) }}" method="POST" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<script>
function addDetailRow(button) {
    const template = document.getElementById('newRowTemplate');
    const tbody = document.getElementById('detailsBody');
    
    let targetRow = template;
    let newIndex = 0;
    
    if (button) {
        const currentRow = button.closest('tr');
        if (currentRow) {
            targetRow = currentRow.nextElementSibling || template;
            
            const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
            const currentIndex = Array.from(rows).indexOf(currentRow);
            newIndex = currentIndex + 1;
        }
    } else {
        const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
        newIndex = rows.length;
    }
    
    const newRow = template.cloneNode(true);
    newRow.removeAttribute('id');
    newRow.classList.remove('d-none');
    
    newRow.setAttribute('data-index', newIndex);
    newRow.innerHTML = newRow.innerHTML.replace(/__index__/g, newIndex);
    
    tbody.insertBefore(newRow, targetRow);
    
    updateDisplayOrders();
    updateMoveButtons();
}

function deleteRow(button) {
    const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
    
    if (rows.length <= 1) {
        alert('少なくとも1行の行程詳細が必要です！');
        return;
    }
    
    if (confirm('この行を削除してもよろしいですか？')) {
        const row = button.closest('tr');
        row.remove();
        updateDisplayOrders();
        updateMoveButtons();
    }
}

function moveRowUp(button) {
    const row = button.closest('tr');
    const prevRow = row.previousElementSibling;
    
    if (prevRow && !prevRow.id) {
        row.parentNode.insertBefore(row, prevRow);
        updateDisplayOrders();
        updateMoveButtons();
    }
}

function moveRowDown(button) {
    const row = button.closest('tr');
    const nextRow = row.nextElementSibling;
    
    if (nextRow && !nextRow.id) {
        row.parentNode.insertBefore(nextRow, row);
        updateDisplayOrders();
        updateMoveButtons();
    }
}

function updateDisplayOrders() {
    const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
    rows.forEach((row, index) => {
        const orderCell = row.querySelector('.display-order');
        if (orderCell) {
            orderCell.textContent = index + 1;
        }
        
        let orderInput = row.querySelector('input[name$="[display_order]"]');
        if (orderInput) {
            orderInput.value = index + 1;
            orderInput.name = `details[${index}][display_order]`;
        }
        
        const inputs = row.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            const name = input.getAttribute('name');
            if (name && name.includes('details[')) {
                const parts = name.split('[');
                if (parts.length >= 3) {
                    const fieldName = parts[2].replace(']', '');
                    input.setAttribute('name', `details[${index}][${fieldName}]`);
                }
            }
        });
    });
}

function updateMoveButtons() {
    const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
    
    rows.forEach((row, index) => {
        const upBtn = row.querySelector('.move-up-btn');
        const downBtn = row.querySelector('.move-down-btn');
        
        if (upBtn) {
            upBtn.disabled = index === 0;
        }
        if (downBtn) {
            downBtn.disabled = index === rows.length - 1;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
    const emptyRows = [];
    
    rows.forEach((row) => {
        const arrivalInput = row.querySelector('.arrival-time');
        const departureInput = row.querySelector('.departure-time');
        const descriptionInput = row.querySelector('.description');
        const remarkInput = row.querySelector('.remark');
        
        const isEmpty = (!arrivalInput || !arrivalInput.value.trim()) &&
                       (!departureInput || !departureInput.value.trim()) &&
                       (!descriptionInput || !descriptionInput.value.trim()) &&
                       (!remarkInput || !remarkInput.value.trim());
        
        if (isEmpty) {
            emptyRows.push(row);
        }
    });
    
    if (emptyRows.length > 0 && rows.length > emptyRows.length) {
        emptyRows.forEach(row => {
            row.remove();
        });
    }
    
    updateDisplayOrders();
    updateMoveButtons();
    
    const addBtn = document.getElementById('addDetailRowBtn');
    if (addBtn) {
        addBtn.addEventListener('click', function() {
            addDetailRow();
        });
    }
    
    const detailsTable = document.getElementById('detailsTable');
    if (detailsTable) {
        detailsTable.addEventListener('click', function(e) {
            if (e.target.closest('.delete-row-btn')) {
                deleteRow(e.target.closest('.delete-row-btn'));
            } else if (e.target.closest('.move-up-btn')) {
                moveRowUp(e.target.closest('.move-up-btn'));
            } else if (e.target.closest('.move-down-btn')) {
                moveRowDown(e.target.closest('.move-down-btn'));
            } else if (e.target.closest('.add-row-btn')) {
                addDetailRow(e.target.closest('.add-row-btn'));
            }
        });
    }
    
    const itineraryForm = document.getElementById('itineraryForm');
    if (itineraryForm) {
        itineraryForm.addEventListener('submit', function(e) {
            updateDisplayOrders();
            
            const rows = document.querySelectorAll('#detailsBody tr:not(#newRowTemplate)');
            
            rows.forEach((row, index) => {
                let orderInput = row.querySelector('input[name$="[display_order]"]');
                if (!orderInput) {
                    orderInput = document.createElement('input');
                    orderInput.type = 'hidden';
                    orderInput.name = `details[${index}][display_order]`;
                    orderInput.value = index + 1;
                    row.appendChild(orderInput);
                }
            });
            
            let isValid = true;
            let emptyDescriptionFound = false;
            
            rows.forEach((row) => {
                const arrivalInput = row.querySelector('.arrival-time');
                const departureInput = row.querySelector('.departure-time');
                const descriptionInput = row.querySelector('.description');
                
                if (descriptionInput && !descriptionInput.value.trim()) {
                    emptyDescriptionFound = true;
                    descriptionInput.classList.add('is-invalid');
                    
                    let errorDiv = descriptionInput.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = '行程説明は必須です。';
                        descriptionInput.parentNode.appendChild(errorDiv);
                    }
                } else if (descriptionInput) {
                    descriptionInput.classList.remove('is-invalid');
                    const errorDiv = descriptionInput.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.remove();
                    }
                }
                
                if (arrivalInput && departureInput && 
                    arrivalInput.value && departureInput.value && 
                    arrivalInput.value > departureInput.value) {
                    alert('出発時間は到着時間より後である必要があります！');
                    isValid = false;
                }
            });
            
            if (emptyDescriptionFound) {
                alert('行程説明は必須です。すべての行の行程説明を入力してください。');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    }
});
</script>

<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.bg-light {
    background-color: #f8f9fa !important;
}
.bg-white {
    background-color: #ffffff !important;
}
.table-bordered td {
    border: 1px solid #dee2e6;
}
#detailsTable tbody tr:hover {
    background-color: #f8f9fa;
}
#detailsTable tbody tr td {
    vertical-align: middle;
}
.gap-1 {
    gap: 0.25rem;
}
</style>
@endsection