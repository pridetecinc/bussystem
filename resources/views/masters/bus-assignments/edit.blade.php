@extends('layouts.app')

@section('title', '運行割当編集')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0 page-title">運行割当編集</h5>
        <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bi bi-arrow-left"></i> 一覧に戻る
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3 success-alert" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error') || $errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 error-alert" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') ?? '入力エラーがあります' }}
        @if($errors->any())
        <ul class="mb-0 ps-3 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('masters.bus-assignments.update', $busAssignment->id) }}" id="editForm">
        @csrf
        @method('PUT')

        <div id="operation-details-container">
            @php $index = 1; @endphp
            <div class="card shadow-sm mb-1 vehicle-detail-card" data-vehicle-index="{{ $index }}" data-bus-id="{{ $busAssignment->id }}">
                <div class="card-header py-1 px-3 d-flex align-items-center" style="background-color: #141c28; border-bottom: 1px solid #aaa;">
                    <h6 class="mb-0 me-3" style="color: #fff; font-size: 0.875rem; font-weight: 500;">
                        運行詳細-{{ sprintf('%02d', $index) }}
                    </h6>
                    <div class="d-flex align-items-center ms-auto" style="gap: 15px;">
                        <div class="form-check d-flex align-items-center">
                            <label class="form-check-label me-2" for="lock_arrangement" style="font-size: 0.8rem; color: #fff;">操作ロック</label>
                            <input type="checkbox" class="form-check-input" id="lock_arrangement" name="lock_arrangement" value="1" {{ $busAssignment->lock_arrangement ? 'checked' : '' }} style="margin: 0;">
                        </div>
                    </div>
                </div>
                <div class="card-body p-2">
                    
                <div class="row" style="margin-right: -5px; margin-left: -5px;">
                    <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                        <input type="hidden" name="id" value="{{ $busAssignment->id }}">
                        <input type="hidden" name="vehicle_index" value="{{ $index }}">
    
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center w-100" style="gap: 8px; justify-content: space-between;">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">運行ID</span>
                                        <span class="border px-2 py-1 bg-white rounded" style="min-width: 338px; color: #2563eb;">
                                            {{ $busAssignment->id ?? '' }}
                                        </span>
                                    </div>
    
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">号車</span>
                                        <input type="text" class="form-control form-control-sm border" name="vehicle_number" value="{{ $busAssignment->vehicle_number ?? sprintf('%02d', $index) }}" style="width: 60px;">
                                    </div>
    
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">最終確認</span>
                                        <input type="checkbox" class="form-check-input" name="status_finalized" value="1" {{ $busAssignment->status_finalized ? 'checked' : '' }} style="margin: 0;">
                                    </div>
    
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">送信</span>
                                        <input type="checkbox" class="form-check-input" name="status_sent" value="1" {{ $busAssignment->status_sent ? 'checked' : '' }} style="margin: 0;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center w-100" style="gap: 15px;">
                                    <div class="d-flex align-items-center" style="flex: 1; gap: 8px;">
                                        <span class="span-label" style="white-space: normal; word-break: break-all; line-height: 1.2; min-width: 70px;">ステップカー</span>
                                        <input type="text" class="form-control form-control-sm border" name="step_car" value="{{ $busAssignment->step_car ?? '' }}" placeholder="ステップカー情報" style="flex: 1; min-width: 338px;">
                                    </div>
    
                                    <div class="d-flex align-items-center" style="flex: 2; gap: 5px; justify-content: flex-end;">
                                        <span class="span-label" style="white-space: nowrap; line-height: 29px;">人数</span>
                                        <input type="number" class="form-control form-control-sm border" name="adult_count" value="{{ $busAssignment->adult_count ?? 1 }}" placeholder="大人" style="width: 58px;" min="0">
                                        <input type="number" class="form-control form-control-sm border" name="child_count" value="{{ $busAssignment->child_count ?? 1 }}" placeholder="小人" style="width: 58px;" min="0">
                                        <input type="number" class="form-control form-control-sm border" name="guide_count" value="{{ $busAssignment->guide_count ?? 2 }}" placeholder="Guide" style="width: 60px;" min="0">
                                        <input type="number" class="form-control form-control-sm border" name="other_count" value="{{ $busAssignment->other_count ?? 0 }}" placeholder="その他" style="width: 60px;" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center w-100" style="gap: 10px;">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">車両</span>
                                        <select class="form-select form-select-sm border vehicle-select"
                                                id="vehicle_select"
                                                name="vehicle_id"
                                                style="width: 338px;">
                                            <option value="">-- 車両を選択 --</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}" {{ $busAssignment->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->registration_number }} {{ $vehicle->vehicleModel ? '(' . $vehicle->vehicleModel->model_name . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
    
                                    <div class="d-flex align-items-center" style="margin-left: auto; gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">車種指定</span>
                                        <input type="checkbox" class="form-check-input" name="vehicle_type_spec_check" value="1" {{ $busAssignment->vehicle_type_spec_check ? 'checked' : '' }} style="margin: 0;">
                                    </div>
    
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">荷物</span>
                                        <input type="number" class="form-control form-control-sm border" name="luggage_count" value="{{ $busAssignment->luggage_count ?? 0 }}" style="width: 60px;" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center w-100" style="gap: 8px;">
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">運転手</span>
                                        <select class="form-select form-select-sm border driver-select"
                                                id="driver_select"
                                                name="driver_id"
                                                style="width: 150px;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers as $driver)
                                                <option value="{{ $driver->id }}" {{ $busAssignment->driver_id == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} {{ $driver->driver_code ? '(' . $driver->driver_code . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
    
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap; width: 20px !important; min-width: 20px !important; margin-right: 0;">仮</span>
                                        <input type="checkbox" class="form-check-input" name="temporary_driver" value="1" {{ $busAssignment->temporary_driver ? 'checked' : '' }} style="margin: 0;">
                                    </div>
    
                                    <div class="d-flex align-items-center" style="gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap; width: 33px !important; min-width: 33px !important; margin-right: 0;">添乗</span>
                                        <select class="form-select form-select-sm border guide-select"
                                                id="guide_select"
                                                name="guide_id"
                                                style="width: 90px;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($guides as $guide)
                                                <option value="{{ $guide->id }}" {{ $busAssignment->guide_id == $guide->id ? 'selected' : '' }}>
                                                    {{ $guide->name }} {{ $guide->guide_code ? '(' . $guide->guide_code . ')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
    
                                    <div class="d-flex align-items-center" style="margin-left: auto; gap: 8px;">
                                        <span class="span-label" style="white-space: nowrap;">代表</span>
                                        <input type="text" class="form-control form-control-sm border" name="representative" value="{{ $busAssignment->representative ?? '' }}" placeholder="Name" style="width: 100px;">
                                        <input type="text" class="form-control form-control-sm border" name="representative_phone" value="{{ $busAssignment->representative_phone ?? '' }}" placeholder="Tel/Cell">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                        <div class="row mt-2" style="margin-right: -5px; margin-left: -5px;">
                            <div class="col-md-12" style="padding-right: 5px; padding-left: 5px;">
                                <div class="tab-container-{{ $index }}">
                                    <div class="d-flex w-100" style="border-bottom: 1px solid #aaa;">
                                        <span class="tab-button2 active flex-fill text-center px-2 py-1" data-container="{{ $index }}" data-tab2="basic2-{{ $index }}" style="background-color: white; border: 1px solid #aaa; border-bottom-color: white; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #374151; font-size: 0.8rem; cursor: pointer;">基本</span>
                                        <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $index }}" data-tab2="doc-{{ $index }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">DOC</span>
                                        <span class="tab-button2 flex-fill text-center px-2 py-1" data-container="{{ $index }}" data-tab2="history2-{{ $index }}" style="background-color: #F3F4F6; border: 1px solid #aaa; border-bottom-color: #aaa; border-top-left-radius: 4px; border-top-right-radius: 4px; margin-bottom: -1px; color: #6B7280; font-size: 0.8rem; cursor: pointer; margin-left: -1px;">履歴</span>
                                    </div>
    
                                    <div id="basic2-{{ $index }}" class="tab-content2" style="border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <span class="span-label">備考</span>
                                                    <textarea name="operation_basic_remarks" rows="3" class="form-control form-control-sm border" style="height: 80px;" placeholder="備考を入力...">{{ $busAssignment->operation_basic_remarks ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
    
                                    <div id="doc-{{ $index }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <span class="span-label">備考</span>
                                                    <textarea name="doc_remarks" rows="3" class="form-control form-control-sm border" style="height: 80px;" placeholder="DOC備考...">{{ $busAssignment->doc_remarks ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
    
                                    <div id="history2-{{ $index }}" class="tab-content2" style="display: none; border: 1px solid #aaa; border-top: 0; background-color: #fff; padding: 10px; height: 100px;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <span class="span-label">備考</span>
                                                    <textarea name="history_remarks" rows="3" class="form-control form-control-sm border" style="height: 80px;" placeholder="履歴備考...">{{ $busAssignment->history_remarks ?? '' }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    
                <div class="row mt-2">
                    <div class="col-md-12">
                        <table class="table table-bordered table-sm" style="font-size: 0.8rem; background-color: white;" data-vehicle-table="{{ $index }}">
                            <thead style="background-color: #f3f4f6; text-align: center;">
                                <tr>
                                    <th style="width: 10%; text-align: center; background-color: #f3f4f6;">運行日</th>
                                    <th style="width: 10%; text-align: center; background-color: #f3f4f6;">開始時刻/場所</th>
                                    <th style="width: 10%; text-align: center; background-color: #f3f4f6;">終了時刻/場所</th>
                                    <th style="text-align: center; background-color: #f3f4f6;">行程</th>
                                    <th style="width: 5%; text-align: center; background-color: #f3f4f6;">選択</th>
                                    <th style="width: 180px; text-align: center; background-color: #f3f4f6;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($busAssignment->dailyItineraries ?? [] as $itineraryIndex => $itinerary)
                                @php
                                    $globalIndex = ($index - 1) * 100 + $itineraryIndex;
                                @endphp
                                <tr class="itinerary-row" data-vehicle="{{ $index }}" data-index="{{ $globalIndex }}" data-bus-id="{{ $busAssignment->id }}" data-itinerary-id="{{ $itinerary->id }}">
                                    <td style="vertical-align: middle; text-align: center; background-color: #f9f9f9; position: relative;">
                                        <span class="row-number" style="position: absolute; top: 2px; left: 2px; color: #2563eb; font-size: 10px; font-weight: bold;">{{ $itineraryIndex + 1 }}</span>
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][id]" value="{{ $itinerary->id }}">
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][display_order]" value="{{ $globalIndex + 1 }}">
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][bus_assignment_id]" value="{{ $busAssignment->id }}" class="itinerary-bus-id">
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][vehicle_id]" value="{{ $busAssignment->vehicle_id }}" class="itinerary-vehicle-id">
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][driver_id]" value="{{ $busAssignment->driver_id }}" class="itinerary-driver-id">
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][guide_id]" value="{{ $busAssignment->guide_id }}" class="itinerary-guide-id">
                                        <input type="text" class="form-control form-control-sm border datepicker-3months" name="daily_itineraries[{{ $globalIndex }}][date]" value="{{ $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y-m-d') : '' }}" style="width: 100%; text-align: center;" placeholder="YYYY-MM-DD">
                                        <input type="hidden" name="daily_itineraries[{{ $globalIndex }}][vehicle_group]" value="{{ $index }}">
                                    </td>
                                    <td style="padding: 2px;">
                                        <div class="d-flex flex-column" style="gap: 2px;">
                                            <input type="time" class="form-control form-control-sm border"
                                                   name="daily_itineraries[{{ $globalIndex }}][time_start]"
                                                   value="{{ $itinerary->time_start ? \Carbon\Carbon::parse($itinerary->time_start)->format('H:i') : '08:00' }}"
                                                   style="width: 100%;" step="60">
                                            <input type="text" class="form-control form-control-sm border"
                                                   name="daily_itineraries[{{ $globalIndex }}][start_location]"
                                                   value="{{ $itinerary->start_location ?? '' }}"
                                                   placeholder="開始場所" style="width: 100%;">
                                        </div>
                                    </td>
                                    <td style="padding: 2px;">
                                        <div class="d-flex flex-column" style="gap: 2px;">
                                            <input type="time" class="form-control form-control-sm border"
                                                   name="daily_itineraries[{{ $globalIndex }}][time_end]"
                                                   value="{{ $itinerary->time_end ? \Carbon\Carbon::parse($itinerary->time_end)->format('H:i') : '18:00' }}"
                                                   style="width: 100%;" step="60">
                                            <input type="text" class="form-control form-control-sm border"
                                                   name="daily_itineraries[{{ $globalIndex }}][end_location]"
                                                   value="{{ $itinerary->end_location ?? '' }}"
                                                   placeholder="終了場所" style="width: 100%;">
                                        </div>
                                    </td>
                                    <td style="vertical-align: middle; padding: 2px;">
                                        <textarea name="daily_itineraries[{{ $globalIndex }}][itinerary]" rows="2"
                                                  class="form-control form-control-sm border"
                                                  style="width: 100%; height: 100%; min-height: 60px;">{{ $itinerary->itinerary ?? '' }}</textarea>
                                    </td>
                                    <td style="padding: 2px; text-align: center; vertical-align: middle;">
                                        <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                                            <input type="checkbox" class="form-check-input itinerary-select"
                                                   id="select_itinerary_{{ $globalIndex }}"
                                                   style="margin: 0; width: 18px; height: 18px; cursor: pointer;">
                                        </div>
                                    </td>
                                    <td style="padding: 2px; text-align: center; vertical-align: middle;">
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
                                    </td>
                                </tr>
                                @empty
                                <tr class="no-data-row">
                                    <td colspan="6" class="text-center py-4" style="color: #6c757d; background-color: #f9f9f9;">
                                        <i class="bi bi-info-circle me-1"></i> 旅程データがありません。「+」ボタンを押して追加してください。
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-2" style="margin-right: -5px; margin-left: -5px;">
                    <div class="col-md-6" style="width:60%; padding-right: 5px; padding-left: 5px;">
                        <div class="d-flex w-100 mb-1">
                            <span class="span-label" style="min-width: 30px;">注意</span>
                            <input type="text" name="attention" class="form-control form-control-sm border" value="{{ $busAssignment->attention ?? '' }}">
                        </div>

                        <div class="d-flex w-100">
                            <span class="span-label" style="min-width: 30px;">備考</span>
                            <textarea name="operation_remarks" rows="1" class="form-control form-control-sm border" placeholder="指示書に表示">{{ $busAssignment->operation_remarks ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="col-md-6" style="width:40%; padding-right: 5px; padding-left: 5px;">
                        <textarea name="operation_memo" rows="2" class="form-control form-control-sm border" style="height: 62px;" placeholder="手配メモ一">{{ $busAssignment->operation_memo ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="d-flex justify-content-between align-items-center mt-3">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm px-3" id="saveBtn">
                    <i class="bi bi-check-circle"></i> 保存
                </button>
                <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-sm btn-outline-secondary px-3 ms-2">
                    <i class="bi bi-x-circle"></i> キャンセル
                </a>
            </div>
            <div>
                <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="if(confirm('本当にこの運行割当を削除しますか？\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                    <i class="bi bi-trash"></i> 運行割当削除
                </button>
            </div>
        </div>
    </form>

    <form id="deleteForm" action="{{ route('masters.bus-assignments.destroy', $busAssignment->id) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>

<style>
.text-gray { color: #6b7280; font-size: 11px; }
.form-input { width: 100%; border: 1px solid #aaa; border-radius: 4px; font-size: 11px; padding: 4px 6px; height: 28px; }
.form-input-small { border: 1px solid #aaa; border-radius: 4px; padding: 4px; height: 28px; font-size: 11px; }
.checkbox { width: 12px; height: 12px; margin-right: 2px; }
.checkbox-large { width: 14px; height: 14px; margin-right: 4px; }
.label-text { color: #374151; font-size: 11px; }
.label-text-gray { color: #6b7280; font-size: 11px; }
.tab-container { position: relative; }
.tab-wrapper { display: flex; margin-bottom: -1px; }
.tab-item { font-size: 11px; padding: 6px 16px; border-radius: 4px 4px 0 0; cursor: pointer; }
.tab-item.active { background-color: white; color: #374151; border-top: 1px solid #d1d5db; border-left: 1px solid #d1d5db; border-right: 1px solid #d1d5db; border-bottom: none; z-index: 2; }
.tab-item.inactive { background-color: #f3f4f6; color: #374151; border: 1px solid #aaa; border-bottom: 1px solid #d1d5db; }
.tab-line { height: 1px; background-color: #d1d5db; width: 100%; margin-top: -1px; z-index: 1; }
.tab-content { padding-top: 4px; }
.btn-primary { background-color: #2563eb; border: none; color: white; font-size: 12px; padding: 6px 24px; border-radius: 4px; cursor: pointer; }
.btn-primary:hover { background-color: #1d4ed8; }
.btn-primary:disabled { background-color: #93c5fd; cursor: not-allowed; }
.btn-outline-danger { border-color: #dc2626; color: #dc2626; font-size: 12px; padding: 6px 16px; }
.btn-outline-danger:hover { background-color: #dc2626; color: white; }
.dashed-box { color: #6b7280; font-size: 11px; padding: 16px; background-color: #f9fafb; border-radius: 4px; text-align: center; border: 1px dashed #d1d5db; }
.span-label { text-align: right; min-width: 50px !important; width: 50px !important; font-size: 0.8rem; margin-right: 10px; white-space: nowrap;}
.card-body { background-color:#f3f4f6; font-size: 0.8rem;}
.container-fluid { max-width: 1600px; }
.page-title { color: #374151; font-size: 1rem; }
.form-control-sm, .form-select-sm { border-color: #E5E7EB; font-size: 0.8rem; border-radius: 4px; }
.form-control-sm:focus, .form-select-sm:focus { border-color: #2563eb; box-shadow: 0 0 0 0.1rem rgba(37, 99, 235, 0.25); }
.btn-sm { font-size: 0.8rem; }
.form-check-input { margin-top: 0; }
.gap-3 { gap: 1rem; }
.gap-4 { gap: 1.5rem; }
.bg-white { background-color: #ffffff !important; }
.rounded { border-radius: 4px !important; }
.table { margin-bottom: 6px; }
.table th { font-weight: 500; color: #aaa; border-color: #E5E7EB; }
.table td { border-color: #E5E7EB; padding: 0.5rem; }
.vehicle-detail-card { margin-bottom: 1rem; border: 1px solid #aaa; }
.vehicle-detail-card .card-header { background-color: #141c28; }
.vehicle-detail-card .card-header h6 span { color: #a0aec0; font-weight: normal; }
.row-number { position: absolute; top: 2px; left: 2px; color: #2563eb; font-size: 10px; font-weight: bold; z-index: 1; }
.tab-button2 { cursor: pointer; transition: all 0.2s; outline: none; }
.tab-button2:hover { background-color: #f9fafb !important; }
.tab-button2.active { background-color: white !important; border-bottom-color: white !important; color: #374151 !important; font-weight: 500; }
.tab-button2:not(.active) { background-color: #F3F4F6 !important; border-bottom-color: #aaa !important; color: #6B7280 !important; }
.tab-content2 { border: 1px #E5E7EB solid; border-top: 0; background-color: #fff; padding: 10px; height: 140px; }

.flatpickr-calendar {
    border: 1px solid #ddd !important;
    border-radius: 6px !important;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.12) !important;
    font-family: inherit !important;
    font-size: 11px !important;
    overflow: hidden !important;
}

.flatpickr-calendar.multiMonth {
    width: 516px !important;
    max-width: 95vw !important;
}

.flatpickr-calendar.multiMonth .flatpickr-innerContainer {
    width: 100% !important;
}

.flatpickr-calendar.multiMonth .flatpickr-months {
    display: flex !important;
}

.flatpickr-calendar.multiMonth .flatpickr-month {
    flex: 1 !important;
}

.flatpickr-calendar.multiMonth .flatpickr-month:not(:last-child) {
    border-right: 1px solid #e9ecef !important;
}

.flatpickr-months {
    background: linear-gradient(135deg, #1f3241 0%, #2d4a5e 100%) !important;
    border-radius: 6px 6px 0 0 !important;
    display: flex !important;
}

.flatpickr-month {
    height: 28px !important;
    padding-right: 0 !important;
}

.flatpickr-current-month {
    padding: 3px 0 0 0 !important;
}

.flatpickr-current-month .flatpickr-monthDropdown-months {
    font-weight: 600 !important;
    color: #fff !important;
    font-size: 11px !important;
}

.flatpickr-current-month .numInputWrapper span {
    color: #fff !important;
}

.flatpickr-current-month input.cur-year {
    color: #fff !important;
    font-weight: 600 !important;
    font-size: 11px !important;
}

.flatpickr-months .flatpickr-month,
.flatpickr-months .flatpickr-next-month,
.flatpickr-months .flatpickr-prev-month {
    color: #fff !important;
    fill: #fff !important;
}

.flatpickr-months .flatpickr-next-month:hover svg,
.flatpickr-months .flatpickr-prev-month:hover svg {
    fill: #ffc107 !important;
}

.flatpickr-months .flatpickr-next-month,
.flatpickr-months .flatpickr-prev-month {
    width: 20px !important;
    height: 20px !important;
    padding: 2px !important;
}

.flatpickr-weekdays {
    background: #f8f9fa !important;
    border-bottom: 1px solid #e9ecef !important;
    margin: 0 !important;
}

.flatpickr-weekday {
    color: #495057 !important;
    font-weight: 600 !important;
    font-size: 10px !important;
    padding: 1px 0 !important;
}

.flatpickr-days {
    border: none !important;
    padding: 0 !important;
}

.flatpickr-day {
    color: #374151 !important;
    border-radius: 2px !important;
    margin: 0 !important;
    border: 1px solid transparent !important;
    max-width: 24px !important;
    width: 24px !important;
    height: 22px !important;
    line-height: 20px !important;
    font-size: 10px !important;
}

.flatpickr-day:hover {
    background: #e0f2fe !important;
    border-color: #2563eb !important;
    color: #2563eb !important;
}

.flatpickr-day.selected {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
    font-weight: 600 !important;
}

.flatpickr-day.selected:hover {
    background: #1d4ed8 !important;
}

.flatpickr-day.startRange,
.flatpickr-day.endRange {
    background: #2563eb !important;
    border-color: #2563eb !important;
    color: #fff !important;
}

.flatpickr-day.inRange {
    background: #dbeafe !important;
    border-color: transparent !important;
    color: #1e40af !important;
}

.flatpickr-day.today {
    border-color: #ffc107 !important;
    background: #fffbeb !important;
    color: #374151 !important;
}

.flatpickr-day.today:hover {
    background: #fef3c7 !important;
    border-color: #f59e0b !important;
    color: #374151 !important;
}

.flatpickr-months .flatpickr-month {
    background: transparent !important;
}

span.flatpickr-weekday {
    background: #f8f9fa !important;
}

.flatpickr-calendar.showTimeInput.hasTime .flatpickr-time {
    border-top: 1px solid #e9ecef !important;
}

.flatpickr-calendar.multiMonth .dayContainer {
    width: 168px !important;
    min-width: 168px !important;
    max-width: 168px !important;
    position: relative !important;
}

.month-wrapper {
    flex: 1 !important;
    position: relative !important;
    padding: 2px !important;
    height: 135px !important;
}

.month-wrapper:not(:last-child)::after {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 1px;
    background-color: #e9ecef;
}

.flatpickr-calendar.multiMonth .flatpickr-days {
    display: flex !important;
    position: relative;
    width: 514px !important;
}

.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer {
    padding: 0 !important;
}

.flatpickr-calendar.multiMonth .flatpickr-rContainer {
    width: 514px !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.datepicker-3months', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        mode: 'single',
        disableMobile: true,
        wrap: false,
        onOpen: function(selectedDates, dateStr, instance) {
            instance.calendarContainer.style.zIndex = '9999';
        },
        onReady: function(selectedDates, dateStr, instance) {
            const daysContainer = instance.daysContainer;
            if (daysContainer) {
                const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                dayContainers.forEach(function(dayContainer) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'month-wrapper';
                    dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                    wrapper.appendChild(dayContainer);
                });
            }
        }
    });

    document.querySelectorAll('.tab-button2').forEach(button => {
        button.addEventListener('click', function() {
            const container = this.getAttribute('data-container');
            const tabId = this.getAttribute('data-tab2');

            const parentContainer = document.querySelector(`.tab-container-${container}`);

            if (parentContainer) {
                const groupButtons = parentContainer.querySelectorAll('.tab-button2');
                const groupContents = parentContainer.querySelectorAll('.tab-content2');

                groupButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.backgroundColor = '#F3F4F6';
                    btn.style.borderBottomColor = '#E5E7EB';
                    btn.style.color = '#6B7280';
                });

                this.classList.add('active');
                this.style.backgroundColor = 'white';
                this.style.borderBottomColor = 'white';
                this.style.color = '#374151';

                groupContents.forEach(content => {
                    content.style.display = 'none';
                });
            }

            document.getElementById(tabId).style.display = 'block';
        });
    });

    document.querySelectorAll('.add-row-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            addRowAfter(this);
        });
    });

    document.querySelectorAll('.delete-row-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            deleteRow(this);
        });
    });

    document.querySelectorAll('.move-up-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            moveUp(this);
        });
    });

    document.querySelectorAll('.move-down-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            moveDown(this);
        });
    });

    function addRowAfter(clickedButton) {
        const currentRow = clickedButton.closest('tr.itinerary-row');
        if (!currentRow) return;

        const table = currentRow.closest('table');
        const tbody = table.querySelector('tbody');

        const dateInput = currentRow.querySelector('input[name*="[date]"]');
        const date = dateInput ? dateInput.value : '';

        const card = currentRow.closest('.card');
        const cardBusId = card ? card.getAttribute('data-bus-id') || '' : '';
        const vehicleGroup = card.getAttribute('data-vehicle-index') || '1';

        const vehicleSelect = card.querySelector('.vehicle-select');
        const driverSelect = card.querySelector('.driver-select');
        const guideSelect = card.querySelector('.guide-select');

        const vehicleId = vehicleSelect ? vehicleSelect.value : '';
        const driverId = driverSelect ? driverSelect.value : '';
        const guideId = guideSelect ? guideSelect.value : '';

        const allRows = Array.from(tbody.querySelectorAll('tr.itinerary-row:not(.no-data-row)'));
        let newIndex = allRows.length;

        const newRow = createNewRow(date, newIndex, vehicleGroup, cardBusId, vehicleId, driverId, guideId);

        const nextRow = currentRow.nextElementSibling;
        if (nextRow && !nextRow.classList.contains('no-data-row')) {
            tbody.insertBefore(newRow, nextRow);
        } else {
            tbody.appendChild(newRow);
        }

        const noDataRow = tbody.querySelector('.no-data-row');
        if (noDataRow) {
            noDataRow.remove();
        }

        reindexRows(table);
        updateMoveButtons(table);

        const newDateInput = newRow.querySelector('.datepicker-3months');
        if (newDateInput && !newDateInput._flatpickr) {
            flatpickr(newDateInput, {
                locale: 'ja',
                dateFormat: 'Y-m-d',
                showMonths: 3,
                allowInput: true,
                clickOpens: true,
                mode: 'single',
                disableMobile: true,
                wrap: false,
                onOpen: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.style.zIndex = '9999';
                },
                onReady: function(selectedDates, dateStr, instance) {
                    const daysContainer = instance.daysContainer;
                    if (daysContainer) {
                        const dayContainers = daysContainer.querySelectorAll('.dayContainer');
                        dayContainers.forEach(function(dayContainer) {
                            const wrapper = document.createElement('div');
                            wrapper.className = 'month-wrapper';
                            dayContainer.parentNode.insertBefore(wrapper, dayContainer);
                            wrapper.appendChild(dayContainer);
                        });
                    }
                }
            });
        }
    }

    function createNewRow(date, index, vehicleGroup = '1', busId = '', vehicleId = '', driverId = '', guideId = '') {
        const newRow = document.createElement('tr');
        newRow.className = 'itinerary-row';
        newRow.setAttribute('data-vehicle', vehicleGroup);
        newRow.setAttribute('data-index', index);
        newRow.setAttribute('data-bus-id', busId);
        newRow.setAttribute('data-itinerary-id', '');

        newRow.innerHTML = `
            <td style="vertical-align: middle; text-align: center; background-color: #f9f9f9; position: relative;">
                <span class="row-number" style="position: absolute; top: 2px; left: 2px; color: #2563eb; font-size: 10px; font-weight: bold;">${index + 1}</span>
                <input type="hidden" name="daily_itineraries[${index}][id]" value="">
                <input type="hidden" name="daily_itineraries[${index}][display_order]" value="${index + 1}">
                <input type="hidden" name="daily_itineraries[${index}][bus_assignment_id]" value="${busId}" class="itinerary-bus-id">
                <input type="hidden" name="daily_itineraries[${index}][vehicle_id]" value="${vehicleId}" class="itinerary-vehicle-id">
                <input type="hidden" name="daily_itineraries[${index}][driver_id]" value="${driverId}" class="itinerary-driver-id">
                <input type="hidden" name="daily_itineraries[${index}][guide_id]" value="${guideId}" class="itinerary-guide-id">
                <input type="text" class="form-control form-control-sm border datepicker-3months" name="daily_itineraries[${index}][date]" value="${date}" style="width: 100%; text-align: center;" placeholder="YYYY-MM-DD">
                <input type="hidden" name="daily_itineraries[${index}][vehicle_group]" value="${vehicleGroup}">
            </td>
            <td style="padding: 2px;">
                <div class="d-flex flex-column" style="gap: 2px;">
                    <input type="time" class="form-control form-control-sm border"
                           name="daily_itineraries[${index}][time_start]" value="08:00"
                           style="width: 100%;" step="60">
                    <input type="text" class="form-control form-control-sm border"
                           name="daily_itineraries[${index}][start_location]" value=""
                           placeholder="開始場所" style="width: 100%;">
                </div>
            </td>
            <td style="padding: 2px;">
                <div class="d-flex flex-column" style="gap: 2px;">
                    <input type="time" class="form-control form-control-sm border"
                           name="daily_itineraries[${index}][time_end]" value="18:00"
                           style="width: 100%;" step="60">
                    <input type="text" class="form-control form-control-sm border"
                           name="daily_itineraries[${index}][end_location]" value=""
                           placeholder="終了場所" style="width: 100%;">
                </div>
            </td>
            <td style="vertical-align: middle; padding: 2px;">
                <textarea name="daily_itineraries[${index}][itinerary]" rows="2"
                          class="form-control form-control-sm border"
                          style="width: 100%; height: 100%; min-height: 60px;"></textarea>
            </td>
            <td style="padding: 2px; text-align: center; vertical-align: middle;">
                <div style="display: flex; justify-content: center; align-items: center; height: 100%;">
                    <input type="checkbox" class="form-check-input itinerary-select"
                           id="select_itinerary_${index}"
                           style="margin: 0; width: 18px; height: 18px; cursor: pointer;">
                </div>
            </td>
            <td style="padding: 2px; text-align: center; vertical-align: middle;">
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
            </td>
        `;

        return newRow;
    }

    function deleteRow(clickedButton) {
        if (!confirm('この行程を削除してもよろしいですか？')) {
            return;
        }

        const row = clickedButton.closest('tr.itinerary-row');
        if (!row) return;

        const table = row.closest('table');
        const tbody = table.querySelector('tbody');

        row.remove();

        const rows = tbody.querySelectorAll('tr.itinerary-row:not(.no-data-row)');
        if (rows.length === 0) {
            tbody.innerHTML = `
                <tr class="no-data-row">
                    <td colspan="6" class="text-center py-4" style="color: #6c757d; background-color: #f9f9f9;">
                        <i class="bi bi-info-circle me-1"></i> 旅程データがありません。「+」ボタンを押して追加してください。
                    </td>
                </tr>
            `;
        } else {
            reindexRows(table);
            updateMoveButtons(table);
        }
    }

    function moveUp(clickedButton) {
        const row = clickedButton.closest('tr.itinerary-row');
        const prevRow = row.previousElementSibling;

        if (prevRow && !prevRow.classList.contains('no-data-row')) {
            const table = row.closest('table');
            row.parentNode.insertBefore(row, prevRow);
            reindexRows(table);
            updateMoveButtons(table);
        }
    }

    function moveDown(clickedButton) {
        const row = clickedButton.closest('tr.itinerary-row');
        const nextRow = row.nextElementSibling;

        if (nextRow && !nextRow.classList.contains('no-data-row')) {
            const table = row.closest('table');
            row.parentNode.insertBefore(nextRow, row);
            reindexRows(table);
            updateMoveButtons(table);
        }
    }

    function reindexRows(table) {
        const rows = table.querySelectorAll('tbody tr.itinerary-row:not(.no-data-row)');
        rows.forEach((row, idx) => {
            row.querySelectorAll('input, select, textarea').forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('daily_itineraries[')) {
                    const newName = name.replace(/daily_itineraries\[\d+\]/, `daily_itineraries[${idx}]`);
                    input.setAttribute('name', newName);
                }
            });

            const displayOrder = row.querySelector('input[name*="[display_order]"]');
            if (displayOrder) {
                displayOrder.value = idx + 1;
            }

            const rowNumber = row.querySelector('.row-number');
            if (rowNumber) {
                rowNumber.textContent = idx + 1;
            }

            const checkId = row.querySelector('[id^="select_itinerary_"]');
            if (checkId) {
                checkId.id = `select_itinerary_${idx}`;
            }

            row.setAttribute('data-index', idx);
        });
    }

    function updateMoveButtons(table) {
        const rows = table.querySelectorAll('tbody tr.itinerary-row:not(.no-data-row)');
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

    const tables = document.querySelectorAll('table');
    tables.forEach(table => {
        updateMoveButtons(table);
    });
});
</script>
@endsection