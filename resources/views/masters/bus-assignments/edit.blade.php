@extends('layouts.app')

@section('title', 'バス割当編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">バス割当編集</h3>
                    <div class="card-tools">
                        <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('masters.bus-assignments.update', $busAssignment->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <strong>ステータス:</strong> 
                                    <span class="badge bg-{{ $busAssignment->status_finalized ? 'success' : ($busAssignment->status_sent ? 'primary' : ($busAssignment->lock_arrangement ? 'warning' : 'secondary')) }}">
                                        {{ $busAssignment->status_display }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-primary card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">基本情報</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="yoyaku_uuid">団体/予約 <span class="text-danger">*</span></label>
                                            <select name="yoyaku_uuid" id="yoyaku_uuid" 
                                                    class="form-control select2 @error('yoyaku_uuid') is-invalid @enderror" 
                                                    style="width: 100%;" required>
                                                <option value="">-- 選択してください --</option>
                                                @foreach($groupInfos as $group)
                                                    <option value="{{ $group->key_uuid }}" 
                                                        {{ old('yoyaku_uuid', $busAssignment->yoyaku_uuid) == $group->key_uuid ? 'selected' : '' }}>
                                                        {{ $group->group_name }} 
                                                        ({{ $group->start_date ? \Carbon\Carbon::parse($group->start_date)->format('Y/m/d') : '' }} 〜)
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('yoyaku_uuid')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="daily_itinerary_id">基準日別行程</label>
                                            <select name="daily_itinerary_id" id="daily_itinerary_id" 
                                                    class="form-control select2 @error('daily_itinerary_id') is-invalid @enderror"
                                                    style="width: 100%;">
                                                <option value="">-- 選択してください --</option>
                                                @foreach($dailyItineraries as $itinerary)
                                                    <option value="{{ $itinerary->id }}" 
                                                        {{ old('daily_itinerary_id', $busAssignment->daily_itinerary_id) == $itinerary->id ? 'selected' : '' }}>
                                                        {{ $itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y/m/d') : '' }} - {{ $itinerary->itinerary }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('daily_itinerary_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="start_date">開始日 <span class="text-danger">*</span></label>
                                                    @php
                                                        $startDate = '';
                                                        if ($busAssignment->start_date) {
                                                            if ($busAssignment->start_date instanceof \Carbon\Carbon) {
                                                                $startDate = $busAssignment->start_date->format('Y-m-d');
                                                            } else {
                                                                $startDate = date('Y-m-d', strtotime($busAssignment->start_date));
                                                            }
                                                        }
                                                    @endphp
                                                    <input type="date" name="start_date" id="start_date" 
                                                           class="form-control @error('start_date') is-invalid @enderror"
                                                           value="{{ old('start_date', $startDate) }}" 
                                                           required>
                                                    @error('start_date')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="end_date">終了日 <span class="text-danger">*</span></label>
                                                    @php
                                                        $endDate = '';
                                                        if ($busAssignment->end_date) {
                                                            if ($busAssignment->end_date instanceof \Carbon\Carbon) {
                                                                $endDate = $busAssignment->end_date->format('Y-m-d');
                                                            } else {
                                                                $endDate = date('Y-m-d', strtotime($busAssignment->end_date));
                                                            }
                                                        }
                                                    @endphp
                                                    <input type="date" name="end_date" id="end_date" 
                                                           class="form-control @error('end_date') is-invalid @enderror"
                                                           value="{{ old('end_date', $endDate) }}" 
                                                           required>
                                                    @error('end_date')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="start_time">開始時間</label>
                                                    <input type="time" name="start_time" id="start_time" 
                                                           class="form-control @error('start_time') is-invalid @enderror"
                                                           value="{{ old('start_time', $busAssignment->start_time) }}">
                                                    @error('start_time')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="end_time">終了時間</label>
                                                    <input type="time" name="end_time" id="end_time" 
                                                           class="form-control @error('end_time') is-invalid @enderror"
                                                           value="{{ old('end_time', $busAssignment->end_time) }}">
                                                    @error('end_time')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-success card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">割当情報</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="vehicle_id">車両</label>
                                            <select name="vehicle_id" id="vehicle_id" 
                                                    class="form-control select2 @error('vehicle_id') is-invalid @enderror"
                                                    style="width: 100%;">
                                                <option value="">-- 選択してください --</option>
                                                @foreach($vehicles as $vehicle)
                                                    <option value="{{ $vehicle->id }}" 
                                                        {{ old('vehicle_id', $busAssignment->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                                        {{ $vehicle->registration_number }} 
                                                        ({{ $vehicle->vehicleModel->model_name ?? '不明' }} / 
                                                        {{ $vehicle->seating_capacity }}名 / 
                                                        {{ $vehicle->branch->branch_name ?? '不明' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="driver_id">ドライバー</label>
                                            <select name="driver_id" id="driver_id" 
                                                    class="form-control select2 @error('driver_id') is-invalid @enderror"
                                                    style="width: 100%;">
                                                <option value="">-- 選択してください --</option>
                                                @foreach($drivers as $driver)
                                                    <option value="{{ $driver->id }}" 
                                                        {{ old('driver_id', $busAssignment->driver_id) == $driver->id ? 'selected' : '' }}>
                                                        {{ $driver->name }} ({{ $driver->branch->branch_name ?? '不明' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('driver_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="count_daily">日次カウント</label>
                                            <input type="number" name="count_daily" id="count_daily" 
                                                   class="form-control @error('count_daily') is-invalid @enderror"
                                                   value="{{ old('count_daily', $busAssignment->count_daily) }}" 
                                                   min="0" readonly>
                                            <small class="text-muted">※ 関連する日別旅程数が自動設定されます</small>
                                            @error('count_daily')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card card-secondary card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">ステータス設定</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" name="lock_arrangement" id="lock_arrangement" 
                                                           class="form-check-input" value="1"
                                                           {{ old('lock_arrangement', $busAssignment->lock_arrangement) ? 'checked' : '' }}
                                                           {{ $busAssignment->status_finalized ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="lock_arrangement">
                                                        ロック中
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" name="status_sent" id="status_sent" 
                                                           class="form-check-input" value="1"
                                                           {{ old('status_sent', $busAssignment->status_sent) ? 'checked' : '' }}
                                                           {{ $busAssignment->status_finalized ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="status_sent">
                                                        送信済
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-check">
                                                    <input type="checkbox" name="status_finalized" id="status_finalized" 
                                                           class="form-check-input" value="1"
                                                           {{ old('status_finalized', $busAssignment->status_finalized) ? 'checked' : '' }}
                                                           {{ $busAssignment->status_finalized ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="status_finalized">
                                                        最終確定
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted">※ ステータスは「ロック中」＜「送信済」＜「最終確定」の優先順位で表示されます。最終確定後は変更できません。</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="card card-gray card-outline">
                                    <div class="card-header">
                                        <h5 class="card-title">システム情報</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Key UUID</dt>
                                                    <dd class="col-sm-8">{{ $busAssignment->key_uuid }}</dd>
                                                    
                                                    <dt class="col-sm-4">作成者</dt>
                                                    <dd class="col-sm-8">{{ $busAssignment->created_by ?? '---' }}</dd>
                                                    
                                                    <dt class="col-sm-4">作成日時</dt>
                                                    <dd class="col-sm-8">
                                                        @php
                                                            $createdAt = $busAssignment->created_at;
                                                            if ($createdAt) {
                                                                if ($createdAt instanceof \Carbon\Carbon) {
                                                                    echo $createdAt->format('Y/m/d H:i');
                                                                } else {
                                                                    echo date('Y/m/d H:i', strtotime($createdAt));
                                                                }
                                                            } else {
                                                                echo '---';
                                                            }
                                                        @endphp
                                                    </dd>
                                                </dl>
                                            </div>
                                            <div class="col-md-6">
                                                <dl class="row">
                                                    <dt class="col-sm-4">更新者</dt>
                                                    <dd class="col-sm-8">{{ $busAssignment->updated_by ?? '---' }}</dd>
                                                    
                                                    <dt class="col-sm-4">更新日時</dt>
                                                    <dd class="col-sm-8">
                                                        @php
                                                            $updatedAt = $busAssignment->updated_at;
                                                            if ($updatedAt) {
                                                                if ($updatedAt instanceof \Carbon\Carbon) {
                                                                    echo $updatedAt->format('Y/m/d H:i');
                                                                } else {
                                                                    echo date('Y/m/d H:i', strtotime($updatedAt));
                                                                }
                                                            } else {
                                                                echo '---';
                                                            }
                                                        @endphp
                                                    </dd>
                                                </dl>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary" {{ $busAssignment->status_finalized ? 'disabled' : '' }}>
                                    <i class="fas fa-save"></i> 保存
                                </button>
                                <a href="{{ route('masters.bus-assignments.show', $busAssignment->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i> 詳細
                                </a>
                                @if(!$busAssignment->status_finalized)
                                <button type="button" class="btn btn-danger float-right" data-toggle="modal" data-target="#deleteModal">
                                    <i class="fas fa-trash"></i> 削除
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@if(!$busAssignment->status_finalized)
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">削除確認</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>このバス割当を削除してもよろしいですか？</p>
                <p class="text-danger"><small>この操作は取り消せません。</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">キャンセル</button>
                <form action="{{ route('masters.bus-assignments.destroy', $busAssignment->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">削除</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
.select2-container--bootstrap4 .select2-selection--single {
    height: calc(2.25rem + 2px) !important;
}
.select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
    line-height: 2.25rem !important;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
<script>
$(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%',
        placeholder: '-- 選択してください --',
        allowClear: true
    });
    
    $('#start_date, #end_date').on('change', function() {
        var startDate = $('#start_date').val();
        var endDate = $('#end_date').val();
        
        if (startDate && endDate && startDate > endDate) {
            alert('終了日は開始日以降の日付を選択してください。');
            $('#end_date').val('');
        }
    });
    
    $('#status_finalized').on('change', function() {
        if ($(this).is(':checked')) {
            $('#status_sent').prop('checked', true).prop('disabled', true);
            $('#lock_arrangement').prop('checked', true).prop('disabled', true);
        }
    });
    
    $('#status_sent').on('change', function() {
        if ($(this).is(':checked') && !$('#status_finalized').is(':checked')) {
            $('#lock_arrangement').prop('checked', true).prop('disabled', true);
        } else if (!$(this).is(':checked') && !$('#status_finalized').is(':checked')) {
            $('#lock_arrangement').prop('disabled', false);
        }
    });
    
    $('#lock_arrangement').on('change', function() {
        if (!$(this).is(':checked')) {
            if ($('#status_sent').is(':checked') || $('#status_finalized').is(':checked')) {
                alert('送信済または最終確定のステータスではロックを解除できません。');
                $(this).prop('checked', true);
            }
        }
    });
    
    $('#yoyaku_uuid').on('change', function() {
        var yoyakuUuid = $(this).val();
        if (yoyakuUuid) {
            var url = '/masters/bus-assignments/daily-itineraries/by-group/' + yoyakuUuid;
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        var $dailySelect = $('#daily_itinerary_id');
                        $dailySelect.empty().append('<option value="">-- 選択してください --</option>');
                        
                        $.each(response.data, function(index, item) {
                            var date = '';
                            if (item.date) {
                                var d = new Date(item.date);
                                date = d.getFullYear() + '/' + 
                                       ('0' + (d.getMonth() + 1)).slice(-2) + '/' + 
                                       ('0' + d.getDate()).slice(-2);
                            }
                            $dailySelect.append('<option value="' + item.id + '">' + date + ' - ' + (item.itinerary || '') + '</option>');
                        });
                        
                        $dailySelect.trigger('change');
                    }
                },
                error: function(xhr) {
                    console.error('日別行程の取得に失敗しました', xhr);
                }
            });
        }
    });
});
</script>
@endpush