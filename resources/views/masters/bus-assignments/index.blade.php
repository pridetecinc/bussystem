@extends('layouts.app')

@section('title', '運行割当一覧')

@section('content')
<div class="container-fluid px-4 py-0">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h5 class="mb-0" style="color: #374151; font-size: 1.25rem;">運行割当一覧</h5>
        <a href="{{ route('masters.bus-assignments.create') }}" class="btn btn-primary btn-sm px-3 py-1"
           style="background-color: #2563eb; border-color: #2563eb; font-size: 0.875rem;">
            新規作成
        </a>
    </div>

    <div class="bg-light p-2 mb-2 rounded" style="background-color: #F3F4F6 !important; border: 1px solid #E5E7EB;">
        <form method="GET" action="{{ route('masters.bus-assignments.index') }}" class="row g-2">
            <div class="col-auto">
                <input type="date" name="start_date" value="{{ request('start_date', $startDate) }}"
                       class="form-control form-control-sm" style="width: 140px; border-color: #E5E7EB;" placeholder="開始日">
            </div>
            <div class="col-auto">
                <input type="date" name="end_date" value="{{ request('end_date', $endDate) }}"
                       class="form-control form-control-sm" style="width: 140px; border-color: #E5E7EB;" placeholder="終了日">
            </div>
            <div class="col">
                <input type="text" name="group_name" value="{{ request('group_name') }}"
                       class="form-control form-control-sm" style="border-color: #E5E7EB;" placeholder="団体名...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm px-3"
                        style="background-color: #2563eb; color: white; border-color: #2563eb; font-size: 0.875rem;">
                    検索
                </button>
            </div>
            <div class="col-auto">
                <a href="{{ route('masters.bus-assignments.index') }}" class="btn btn-sm btn-outline-secondary px-3"
                   style="border-color: #E5E7EB; color: #374151; font-size: 0.875rem;">
                    クリア
                </a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-bordered mb-0" style="border-color: #E5E7EB; font-size: 0.875rem;">
            <thead style="background-color: #F3F4F6;">
                <tr>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 50px;">No.</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px;">車両No</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">団体名</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">状態</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">車両</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">運転手</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500;">ガイド</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 80px;">期間</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 60px;">人数</th>
                    <th class="text-center px-2 py-1" style="color: #374151; font-weight: 500; width: 100px;">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $index => $assignment)
                <tr>
                    <td class="text-center px-2 py-1 align-middle">
                        {{ $assignments->firstItem() + $index }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle" style="background: #fff3cd; font-weight: 600;">
                        {{ sprintf('%02d', $assignment->vehicle_index ?? 1) }}
                    </td>
                    <td class="px-2 py-1 align-middle">
                        {{ $assignment->groupInfo?->group_name ?? '---' }}
                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                            {{ substr($assignment->key_uuid ?? $assignment->id, 0, 8) }}
                        </small>
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @php
                            $statusColor = '#fee2e2'; // 未確定
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
                        <span style="background-color: {{ $statusColor }}; border-radius: 12px; padding: 2px 10px; font-size: 0.75rem;">
                            {{ $statusText }}
                        </span>
                    </td>
                    <td class="px-2 py-1 align-middle">
                        @if($assignment->vehicle)
                            <span style="color: #2563eb;">{{ $assignment->vehicle->registration_number ?? '' }}</span>
                        @else
                            <span style="color: #9ca3af;">{{ $assignment->vehicle_number ? '号車 ' . $assignment->vehicle_number : '未設定' }}</span>
                        @endif
                    </td>
                    <td class="px-2 py-1 align-middle">
                        {{ $assignment->driver?->name ?? ($assignment->temporary_driver ? '<span style="color: #f59e0b;">仮</span>' : '---') }}
                    </td>
                    <td class="px-2 py-1 align-middle">
                        {{ $assignment->guide?->name ?? '---' }}
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @if(is_string($assignment->start_date))
                            {{ date('m/d', strtotime($assignment->start_date)) }}
                        @elseif($assignment->start_date)
                            {{ $assignment->start_date->format('m/d') }}
                        @else
                            ---
                        @endif
                        @if($assignment->start_time) - {{ substr($assignment->start_time, 0, 5) }} @endif
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        @php
                            $totalPax = ($assignment->adult_count ?? 0) +
                                       ($assignment->child_count ?? 0) +
                                       ($assignment->guide_count ?? 0);
                        @endphp
                        <span style="font-weight: 600; color: #374151;">{{ $totalPax }}</span>
                    </td>
                    <td class="text-center px-2 py-1 align-middle">
                        <a href="{{ route('masters.bus-assignments.show', $assignment->id) }}"
                           style="color: #2563eb; text-decoration: none; margin-right: 8px;">
                            詳細
                        </a>
                        <a href="{{ route('masters.bus-assignments.edit', $assignment->id) }}"
                           style="color: #2563eb; text-decoration: none; margin-right: 8px;">
                            編集
                        </a>
                        <a href="javascript:void(0);" onclick="confirmDelete('{{ $assignment->id }}', '{{ $assignment->groupInfo?->group_name ?? 'この割当' }}')"
                           style="color: #dc3545; text-decoration: none;">
                            削除
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-3" style="color: #9ca3af;">
                        運行割当データがありません
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
        <div style="color: #6b7280; font-size: 0.875rem;">
            全 {{ $assignments->total() }} 件中
            {{ $assignments->firstItem() ?? 0 }} - {{ $assignments->lastItem() ?? 0 }} 件表示
        </div>
        <div>
            {{ $assignments->withQueryString()->links('pagination::bootstrap-4') }}
        </div>
    </div>

    {{-- 集計情報 --}}
    <div class="d-flex justify-content-between align-items-center mt-3 p-2 rounded" style="background-color: #F3F4F6; border: 1px solid #E5E7EB;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <span style="color: #374151; font-size: 0.875rem;">
                <strong>大人:</strong> {{ $totalAdult }} 人
            </span>
            <span style="color: #374151; font-size: 0.875rem;">
                <strong>子供:</strong> {{ $totalChild }} 人
            </span>
            <span style="color: #374151; font-size: 0.875rem;">
                <strong>ガイド:</strong> {{ $totalGuide }} 人
            </span>
        </div>
        <div style="color: #374151; font-size: 0.875rem;">
            <strong>合計金額:</strong> ¥{{ number_format($totalAmount) }}
        </div>
    </div>
</div>

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
@endsection
