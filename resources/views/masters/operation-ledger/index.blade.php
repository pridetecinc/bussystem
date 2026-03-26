@extends('layouts.app')

@section('title', '運行台帳')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運行台帳</h5>
    </div>
    
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.operation-ledger.index') }}" class="row g-2 align-items-center" id="searchForm">
            <div class="col-auto">
                <input type="text" name="start_date" value="{{ $startDate }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 120px;" placeholder="開始日" id="start_date">
            </div>
            <div class="col-auto">
                <input type="text" name="end_date" value="{{ $endDate }}" 
                       class="form-control form-control-sm datepicker-3months" style="width: 120px;" placeholder="終了日" id="end_date">
            </div>
            
            <div class="col-auto">
                <select name="period" class="form-select form-select-sm" style="width: 100px;" id="period_select">
                    <option value="1" {{ request('period') == 1 ? 'selected' : '' }}>1週間</option>
                    <option value="2" {{ request('period') == 2 ? 'selected' : '' }}>2週間</option>
                    <option value="3" {{ request('period') == 3 ? 'selected' : '' }}>3週間</option>
                    <option value="4" {{ request('period') == 4 ? 'selected' : '' }}>1ヶ月</option>
                </select>
            </div>
            
            <div class="col-auto">
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setToday()">今日</button>
            </div>
            
            <div class="col-auto">
                <select name="vehicle_type_id" class="form-select form-select-sm">
                    <option value="">車種：全て</option>
                    @foreach($vehicleTypes ?? [] as $type)
                        <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->type_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-auto">
                <select name="agency_id" class="form-select form-select-sm">
                    <option value="">代理店：全て</option>
                    @foreach($agencies ?? [] as $agency)
                        <option value="{{ $agency->id }}" {{ request('agency_id') == $agency->id ? 'selected' : '' }}>
                            {{ $agency->agency_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-auto">
                <select name="reservation_status" class="form-select form-select-sm">
                    <option value="">予約状態：全て</option>
                    <option value="予約" {{ request('reservation_status') == '予約' ? 'selected' : '' }} style="background-color: #ccf5ff;">予約</option>
                    <option value="仮押さえ" {{ request('reservation_status') == '仮押さえ' ? 'selected' : '' }} style="background-color: #ffff99;">仮押さえ</option>
                    <option value="見積" {{ request('reservation_status') == '見積' ? 'selected' : '' }} style="background-color: #ccffcc;">見積</option>
                    <option value="危ない" {{ request('reservation_status') == '危ない' ? 'selected' : '' }} style="background-color: #ffcccc;">危ない</option>
                    <option value="確定待ち" {{ request('reservation_status') == '確定待ち' ? 'selected' : '' }} style="background-color: #ffd9b3;">確定待ち</option>
                    <option value="確定" {{ request('reservation_status') == '確定' ? 'selected' : '' }} style="background-color: #cbb87c;">確定</option>
                    <option value="送信済" {{ request('reservation_status') == '送信済' ? 'selected' : '' }} style="background-color: #e6e6fa;">送信済</option>
                    <option value="実績待ち" {{ request('reservation_status') == '実績待ち' ? 'selected' : '' }} style="background-color: #e0b0ff;">実績待ち</option>
                    <option value="運行済" {{ request('reservation_status') == '運行済' ? 'selected' : '' }} style="background-color: #c0c0c0;">運行済</option>
                    <option value="請求済" {{ request('reservation_status') == '請求済' ? 'selected' : '' }} style="background-color: #b0e0e6;">請求済</option>
                    <option value="キャンセル" {{ request('reservation_status') == 'キャンセル' ? 'selected' : '' }} style="background-color: #d3d3d3;">キャンセル</option>
                    <option value="稼働不可" {{ request('reservation_status') == '稼働不可' ? 'selected' : '' }} style="background-color: #2c2c2c; color: white;">稼働不可</option>
                </select>
            </div>
            
            <div class="col-auto">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="has_guide" name="has_guide" value="1" {{ request('has_guide') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="has_guide" style="font-size: 0.8rem;">添乗員あり</label>
                </div>
            </div>
            
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">検索</button>
            </div>
            
            <div class="col-auto">
                <a href="{{ route('masters.operation-ledger.index') }}" class="btn btn-sm btn-outline-secondary">リセット</a>
            </div>
        </form>
    </div>
    
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="table table-bordered table-sm ledger-table" style="font-size: 0.75rem; min-width: 800px;">
            <thead>
                <tr>
                    <th class="text-center" style="position: sticky; left: 0; background-color: #f8f9fa; z-index: 10; min-width: 180px;">車両名 / 代理店</th>
                    @foreach($dates as $date)
                        <th class="text-center" style="background-color: #e9ecef; min-width: 100px;">
                            {{ $date['display'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($vehicles as $index => $vehicle)
                    @php
                        $rowBgColor = $index % 2 == 0 ? '#f8f9fa' : '#ffffff';
                        $schedule = $scheduleData[$vehicle->id]['schedule'] ?? [];
                    @endphp
                    <tr style="background-color: {{ $rowBgColor }};">
                        <td class="align-top" style="position: sticky; left: 0; background-color: {{ $rowBgColor }}; z-index: 5;">
                            <strong>{{ $vehicle->registration_number }}</strong>
                            @if($vehicle->vehicleModel)
                                <br><small class="text-muted">{{ $vehicle->vehicleModel->model_name }}</small>
                            @endif
                            <br><small>{{ $vehicle->branch->branch_name ?? '' }}</small>
                        </td>
                        @foreach($dates as $dateInfo)
                            @php
                                $dateStr = $dateInfo['date']->format('Y-m-d');
                                $dayItineraries = $schedule[$dateStr] ?? [];
                            @endphp
                            <td class="position-relative p-0" style="background-color: {{ $rowBgColor }}; cursor: pointer;" 
                                onclick="openCreateGroup({{ $vehicle->id }}, '{{ $dateStr }}', '{{ $vehicle->registration_number }}')">
                                <div class="timeline-cell" style="background-color: {{ $rowBgColor }};">
                                    @foreach($dayItineraries as $idx => $itinerary)
                                        @php
                                            $leftPercent = ($itinerary['start_minutes'] / 1440) * 100;
                                            $zIndex = count($dayItineraries) - $idx;
                                            $categoryColor = $itinerary['category_color'] ?? 'transparent';
                                            
                                            if ($leftPercent < 0) $leftPercent = 0;
                                            if ($leftPercent > 100) $leftPercent = 100;
                                        @endphp
                                        <div class="timeline-event" style="left: {{ $leftPercent }}%; z-index: {{ $zIndex }}; background-color: {{ $categoryColor }};" onclick="event.stopPropagation(); openBusAssignmentEdit({{ $itinerary['bus_assignment_id'] }})">
                                            <div class="event-content" style="border-left: 3px solid {{ $itinerary['status_color'] }};">
                                                <div>
                                                    {{ $itinerary['group_info_id'] }} [{{ $itinerary['bus_assignment_id'] }}]
                                                </div>
                                                <div>
                                                    @if($itinerary['vehicle_type_spec_check'])
                                                        <span style="color: #f59e0b; cursor: help;" title="車種指定">⭐</span>
                                                    @endif
                                                    @if($itinerary['guide_name'])
                                                        <span style="color: #10b981; cursor: help;" title="添乗員: {{ $itinerary['guide_name'] }}">👤</span>
                                                    @endif
                                                    @if($itinerary['status_finalized'])
                                                        <span style="color: #22c55e; cursor: help; font-weight: bold;" title="最終確認済み">✓</span>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if($itinerary['agency_code'])
                                                        <span title="代理店コード: {{ $itinerary['agency_code'] }}">{{ $itinerary['agency_code'] }}</span> /
                                                    @endif
                                                    <span title="団体名: {{ $itinerary['group_name'] }}">{{ $itinerary['group_name'] }}</span>
                                                </div>
                                                <div>
                                                    @if($itinerary['is_temporary_driver'])
                                                        <span style="color: #f59e0b; cursor: help;" title="仮運転手">(仮)</span>
                                                    @endif
                                                    <span title="運転手名: {{ $itinerary['driver_name'] }}{{ $itinerary['driver_name_kana'] ? ' (' . $itinerary['driver_name_kana'] . ')' : '' }}{{ $itinerary['driver_phone'] ? ' / 電話: ' . $itinerary['driver_phone'] : '' }}">
                                                        {{ $itinerary['driver_name'] }}
                                                        @if($itinerary['driver_name_kana'])
                                                            <span style="font-size: 0.6rem; color: #666;">({{ $itinerary['driver_name_kana'] }})</span>
                                                        @endif
                                                    </span>
                                                    @if($itinerary['driver_phone'])
                                                        <span style="cursor: help;" title="電話番号: {{ $itinerary['driver_phone'] }}">📞</span>
                                                    @endif
                                                </div>
                                                @if($itinerary['remarks'])
                                                    <div style="font-size: 0.6rem; color: #666; white-space: normal; cursor: help;" title="備考: {{ $itinerary['remarks'] }}">
                                                        {{ Str::limit($itinerary['remarks'], 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if(count($dayItineraries) == 0)
                                        <div></div>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center" style="background-color: #e9ecef;">日付</th>
                    @foreach($dates as $date)
                        <th class="text-center" style="background-color: #e9ecef;">
                            {{ $date['display'] }}
                        </th>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="iframeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 9999; overflow: auto;">
    <div style="position: relative; width: 100%; min-height: 100%; display: flex; justify-content: center; align-items: center; padding: 20px;">
        <div id="modalContent" style="background-color: #f3f4f6; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); width: 90%; max-width: 550px; overflow: hidden; transition: all 0.3s ease;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 6px 16px; color: #fff; font-size: 14px; font-weight: 500; background-color: #374151;">
                <span id="modalTitle">新規グループ作成</span>
                <button onclick="closeIframeModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #fff;">&times;</button>
            </div>
            <iframe id="modalIframe" src="" style="width: 100%; height: 480px; border: none; display: block; transition: height 0.3s ease;"></iframe>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.ledger-table th,
.ledger-table td {
    border: 1px solid #dee2e6;
    vertical-align: top;
    position: relative;
    overflow: visible;
}

.ledger-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background-color: #e9ecef;
}

.timeline-cell {
    position: relative;
    height: 60px;
    width: 100%;
    overflow: visible;
}

.timeline-event {
    position: absolute;
    top: 0;
    bottom: 0;
    border-left: 1px dashed #666;
    overflow: visible;
    z-index: 100;
    cursor: pointer;
    white-space: nowrap;
    min-width: 30px;
    pointer-events: auto;
}

.event-content {
    height: 60px;
    position: relative;
    padding: 2px 4px;
    font-size: 0.7rem;
    line-height: 1.3;
    border-left-width: 3px;
    border-left-style: solid;
    white-space: nowrap;
    z-index: 101;
    color: #000;
    display: inline-block;
    background-color: inherit;
    pointer-events: auto;
}

.datepicker-3months {
    border-color: #E5E7EB;
    border-radius: 4px;
    font-size: 0.8rem;
}

.table-responsive {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
    overflow-x: auto;
}

.table-responsive .table {
    overflow: visible;
}

.position-relative {
    overflow: visible !important;
}

.ledger-table td {
    line-height: 1.2;
}

.table-responsive::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr('.datepicker-3months', {
        locale: 'ja',
        dateFormat: 'Y-m-d',
        showMonths: 3,
        allowInput: true,
        clickOpens: true,
        disableMobile: true,
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
    
    const periodSelect = document.getElementById('period_select');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const searchForm = document.getElementById('searchForm');
    
    if (periodSelect) {
        periodSelect.addEventListener('change', function() {
            const period = parseInt(this.value);
            const today = new Date();
            const startDate = new Date(today);
            const endDate = new Date(today);
            endDate.setDate(today.getDate() + (period * 7 - 1));
            
            const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            startDateInput.value = formatDate(startDate);
            endDateInput.value = formatDate(endDate);
            searchForm.submit();
        });
    }
});

function setToday() {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    const todayStr = `${year}-${month}-${day}`;
    
    document.getElementById('start_date').value = todayStr;
    document.getElementById('end_date').value = todayStr;
    document.getElementById('searchForm').submit();
}

function openIframeModal(url, title = '新規グループ作成') {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    const modalContent = document.getElementById('modalContent');
    const modalTitle = document.getElementById('modalTitle');
    
    if (!iframe || !modal) return;
    
    iframe.src = url;
    modalTitle.textContent = title;
    iframe.style.height = '480px';
    if (modalContent) modalContent.style.maxWidth = '550px';
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeIframeModal() {
    const iframe = document.getElementById('modalIframe');
    const modal = document.getElementById('iframeModal');
    
    if (iframe) iframe.src = '';
    if (modal) modal.style.display = 'none';
    document.body.style.overflow = '';
}

function openCreateGroup(vehicleId, date, vehicleName) {
    const url = '{{ route("masters.group-infos.create") }}' + 
                '?vehicle_id=' + encodeURIComponent(vehicleId) +
                '&vehicle_name=' + encodeURIComponent(vehicleName) +
                '&start_date=' + encodeURIComponent(date) +
                '&end_date=' + encodeURIComponent(date);
    
    openIframeModal(url, '新規グループ作成');
}

function openBusAssignmentEdit(busAssignmentId) {
    const url = '/masters/bus-assignments/' + busAssignmentId + '/edit';
    window.open(url, '_blank');
}
</script>
@endpush