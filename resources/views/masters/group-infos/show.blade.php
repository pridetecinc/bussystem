@extends('layouts.app')

@section('title', 'グループ詳細')

@section('content')
<div class="container-fluid px-4 py-2">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="color: #374151; font-size: 1rem;">グループ詳細</h5>
        <div>
            <button type="button" onclick="openEditModal()" class="btn btn-primary btn-sm px-3 py-1" 
                    style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
                編集
            </button>
            <button type="button" onclick="closeIframe()" class="btn btn-sm btn-outline-secondary px-3 py-1 ms-2" 
                    style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                閉じる
            </button>
        </div>
    </div>

    <div class="bg-white p-3 rounded shadow-sm">
        <table class="table table-bordered" style="border-color: #E5E7EB; font-size: 0.875rem;">
            <tr>
                <th style="width: 150px; background-color: #F3F4F6; color: #374151; font-weight: 500;">初始</th>
                <td>
                    @if($groupInfo->start_date)
                        {{ \Carbon\Carbon::parse($groupInfo->start_date)->format('Y/m/d') }}
                        @if($groupInfo->start_time)
                            {{ substr($groupInfo->start_time, 0, 5) }}
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <th style="background-color: #F3F4F6; color: #374151; font-weight: 500;">终了</th>
                <td>
                    @if($groupInfo->end_date)
                        {{ \Carbon\Carbon::parse($groupInfo->end_date)->format('Y/m/d') }}
                        @if($groupInfo->end_time)
                            {{ substr($groupInfo->end_time, 0, 5) }}
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <th style="background-color: #F3F4F6; color: #374151; font-weight: 500;">代理店</th>
                <td>{{ $groupInfo->agency ?? '--' }}</td>
            </tr>
            <tr>
                <th style="background-color: #F3F4F6; color: #374151; font-weight: 500;">状态</th>
                <td>{{ $groupInfo->reservation_status ?? '--' }}</td>
            </tr>
            <tr>
                <th style="background-color: #F3F4F6; color: #374151; font-weight: 500;">人数</th>
                <td>
                    大人: {{ $groupInfo->adult_count ?? 0 }}人<br>
                    子供: {{ $groupInfo->child_count ?? 0 }}人<br>
                    ガイド: {{ $groupInfo->guide_count ?? 0 }}人<br>
                    その他: {{ $groupInfo->other_count ?? 0 }}人
                </td>
            </tr>
            <tr>
                <th style="background-color: #F3F4F6; color: #374151; font-weight: 500;">車両</th>
                <td>{{ $groupInfo->vehicle ?? '--' }}</td>
            </tr>
            <tr>
                <th style="background-color: #F3F4F6; color: #374151; font-weight: 500;">remark</th>
                <td>{{ $groupInfo->remarks ?? '--' }}</td>
            </tr>
        </table>

        @if($groupInfo->dailyItineraries && $groupInfo->dailyItineraries->count() > 0)
        <div class="mt-4">
            <h6 style="color: #374151; font-weight: 500; font-size: 0.875rem;">旅程情報</h6>
            <table class="table table-sm table-bordered" style="border-color: #E5E7EB; font-size: 0.875rem;">
                <thead style="background-color: #F3F4F6;">
                    <tr>
                        <th class="text-center px-2 py-1">日付</th>
                        <th class="text-center px-2 py-1">内容</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupInfo->dailyItineraries as $itinerary)
                    <tr>
                        <td class="text-center px-2 py-1">
                            {{ \Carbon\Carbon::parse($itinerary->date)->format('Y/m/d') }}
                        </td>
                        <td class="px-2 py-1">
                            {{ $itinerary->description ?? '--' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection


@push('styles')
<style>
.table th {
    border-color: #E5E7EB;
    vertical-align: middle;
}
.table td {
    border-color: #E5E7EB;
    vertical-align: middle;
}
</style>
@endpush


@push('scripts')
<script>
function closeIframe() {
    window.parent.postMessage('close-iframe', '*');
}

function openEditModal() {
    window.parent.postMessage('open-edit', '{{ route('masters.group-infos.edit', $groupInfo->id) }}');
}
</script>
@endpush