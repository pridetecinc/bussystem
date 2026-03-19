@extends('layouts.app')

@section('title', '運行一覧')

@section('content')
<div class="container-fluid px-4 py-0">
    {{-- ヘッダー --}}
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運行一覧</h5>
        <a href="{{ route('masters.bus-assignments.create') }}" class="btn btn-primary btn-sm px-3 py-1"
           style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
            新規作成
        </a>
    </div>

    {{-- 検索部分（コンパクト版） --}}
    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.bus-assignments.index') }}" class="row g-1">
            {{-- 1行目：すべての検索条件を横並びに --}}
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    {{-- 運行日 --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運行日</span>
                        <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}"
                               class="form-control form-control-sm" style="width: 120px; border-color: #E5E7EB;">
                        <span class="mx-1">~</span>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}"
                               class="form-control form-control-sm" style="width: 120px; border-color: #E5E7EB;">
                    </div>

                    {{-- 当日/同日ラジオ --}}
                    <div class="d-flex align-items-center">
                        <div class="form-check form-check-inline me-1">
                            <input class="form-check-input" type="radio" name="date_type" id="date_type_today" value="today" {{ request('date_type') == 'today' ? 'checked' : '' }} style="transform: scale(0.8);">
                            <label class="form-check-label" for="date_type_today" style="font-size: 0.8rem;">当日</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="date_type" id="date_type_same" value="same" {{ request('date_type') == 'same' ? 'checked' : '' }} style="transform: scale(0.8);">
                            <label class="form-check-label" for="date_type_same" style="font-size: 0.8rem;">同日</label>
                        </div>
                    </div>

                    {{-- 予約ID --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">予約ID</span>
                        <input type="text" name="reservation_id" value="{{ request('reservation_id') }}"
                               class="form-control form-control-sm" style="width: 90px; border-color: #E5E7EB;">
                    </div>

                    {{-- 運行ID --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">運行ID</span>
                        <input type="text" name="operation_id" value="{{ request('operation_id') }}"
                               class="form-control form-control-sm" style="width: 90px; border-color: #E5E7EB;">
                    </div>

                    {{-- 営業所 --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">営業所</span>
                        <select name="branch_id" class="form-select form-select-sm" style="width: 100px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 車種 --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 30px;">車種</span>
                        <select name="vehicle_type_id" class="form-select form-select-sm" style="width: 90px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($vehicleTypes ?? [] as $type)
                                <option value="{{ $type->id }}" {{ request('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->type_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 車両名（下拉框） --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">車両名</span>
                        <select name="vehicle_id" class="form-select form-select-sm" style="width: 120px; border-color: #E5E7EB;">
                            <option value="">選択</option>
                            @foreach($vehicles ?? [] as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ request('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->registration_number }} 
                                    @if($vehicle->vehicleModel)
                                        ({{ $vehicle->vehicleModel->model_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 団体名 --}}
                    <div class="d-flex align-items-center">
                        <span class="me-1" style="font-size: 0.8rem; font-weight: 500; min-width: 45px;">団体名</span>
                        <input type="text" name="group_name" value="{{ request('group_name') }}"
                               class="form-control form-control-sm" style="width: 120px; border-color: #E5E7EB;" placeholder="団体名">
                    </div>

                    {{-- ボタン --}}
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm px-2"
                                style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.8rem;">
                            検索
                        </button>
                        <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-sm btn-outline-secondary px-2"
                           style="border-color: #E5E7EB; color: #374151; font-size: 0.8rem;">
                            クリア
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- 運行一覧テーブル --}}
    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" style="border-color: #E5E7EB; font-size: 0.75rem;">
            <thead>
                <tr>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 60px;">No.</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">運行期間</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">車両名<br>号車</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 80px;">運転手</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">予約ID<br>運行ID</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">開始時刻<br>開始場所</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 60px;">最終確認</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">業務分類<br>行程名</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 120px;">団体名<br>ステッカー</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 100px;">代理店名<br>国籍</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 70px;">予約状況</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; min-width: 80px;">請求額<br>未納額</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 50px;">立替</th>
                    <th class="text-center px-1 py-1" style="vertical-align: middle; background-color: #F3F4F6; color: #374151; font-weight: 500; width: 70px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $index => $assignment)
                <tr>
                    <td class="text-center px-1 py-1 align-middle">{{ $assignments->firstItem() + $index }}</td>
                    <td class="px-1 py-1 align-middle"><span>{!! nl2br(e($assignment->period_display)) !!}</span></td>
                    <td class="px-1 py-1 align-middle"><span>{!! nl2br(e($assignment->vehicle_display)) !!}</span></td>
                    <td class="px-1 py-1 align-middle">
                        @if($assignment->temporary_driver)
                            <span style="color: #f59e0b; font-weight: 600;">仮</span>
                            {{ $assignment->driver?->name ?? '---'  }}
                        @else
                            {{ $assignment->driver?->name ?? '---'  }}
                        @endif
                    </td>
                    <td class="px-1 py-1 align-middle text-center">
                        <span style="color: #2563eb;">{{ $assignment->group_info_id ?? '---' }}</span><br>
                        {{ $assignment->id }}
                    </td>
                    <td class="px-1 py-1 align-middle">{!! nl2br(e($assignment->start_info)) !!}</td>
                    <td class="text-center px-1 py-1 align-middle">
                        @php
                            $statusColor = '#fee2e2';
                            $statusText = '未確定';
                            if ($assignment->status_finalized) {
                                $statusColor = '#d1fae5';
                                $statusText = '最終確定';
                            } elseif ($assignment->status_sent) {
                                $statusColor = '#fef3c7';
                                $statusText = '送信済';
                            } elseif ($assignment->lock_arrangement) {
                                $statusColor = '#ffedd5';
                                $statusText = 'ロック中';
                            }
                        @endphp
                        <span style="background-color: {{ $statusColor }}; border-radius: 12px; padding: 2px 8px; font-size: 0.7rem; display: inline-block;">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        {{ $assignment->groupInfo?->business_category ?? '---' }}<br>
                        <small>{{ $assignment->groupInfo?->itinerary_name ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        <span class="fw-bold">{{ $assignment->groupInfo?->group_name ?? '---' }}</span><br>
                        <small class="text-muted">{{ $assignment->step_car ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">
                        {{ $assignment->groupInfo?->agency ?? '---' }}<br>
                        <small>{{ $assignment->groupInfo?->agency_country ?? '---' }}</small>
                    </td>
                    <td class="px-1 py-1 align-middle">{{ $assignment->groupInfo?->reservation_status ?? '---' }}</td>
                    <td class="text-center px-1 py-1 align-middle">--<br>--</td>
                    <td class="text-center px-1 py-1 align-middle">--</td>
                    <td class="text-center px-1 py-1 align-middle">
                        <div class="d-flex flex-column gap-1">
                            <a href="{{ route('masters.bus-assignments.show', $assignment->id) }}" style="color: #2563eb; text-decoration: none; font-size: 0.7rem;">詳細</a>
                            <a href="{{ route('masters.bus-assignments.edit', $assignment->id) }}" style="color: #2563eb; text-decoration: none; font-size: 0.7rem;">編集</a>
                            <a href="javascript:void(0);" onclick="confirmDelete('{{ $assignment->id }}', '{{ $assignment->groupInfo?->group_name ?? 'この割当' }}')" style="color: #dc3545; text-decoration: none; font-size: 0.7rem;">削除</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="14" class="text-center py-3" style="color: #9ca3af;">運行データがありません</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ページネーション --}}
    <div class="d-flex justify-content-between align-items-center mt-2">
        <div style="color: #6b7280; font-size: 0.875rem;">
            全 {{ $assignments->total() }} 件中 {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }} 件表示
        </div>
        <div>{{ $assignments->withQueryString()->links('pagination::bootstrap-4') }}</div>
    </div>
</div>

{{-- 削除フォーム --}}
<form id="deleteForm" method="POST" action="">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id, name) {
    if (confirm(name + ' を削除してもよろしいですか？')) {
        const form = document.getElementById('deleteForm');
        form.action = '{{ route('masters.bus-assignments.destroy', ':id') }}'.replace(':id', id);
        form.submit();
    }
}
</script>

<style>
.table td { vertical-align: middle; line-height: 1.3; }
.table hr { margin: 2px 0; opacity: 0.3; }
</style>
@endsection