@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-calendar-check me-2"></i>勤怠分類マスター</h3>
        <a href="{{ route('attendance_categories.create') }}" class="btn btn-primary shadow-sm">新規登録</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 20%">コード</th>
                        <th style="width: 30%" class="text-start ps-4">分類名</th>
                        <th style="width: 20%">出勤扱い</th>
                        <th style="width: 15%">色</th>
                        <th style="width: 15%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td class="fw-bold">{{ $cat->attendance_code }}</td>
                        <td class="text-start ps-4">{{ $cat->attendance_name }}</td>
                        <td>
                            @if($cat->is_work_day)
                                <span class="text-primary fw-bold">○</span>
                            @else
                                <span class="text-muted">×</span>
                            @endif
                        </td>
                        <td>
                            <div style="width: 30px; height: 30px; background-color: {{ $cat->color_code }}; margin: auto; border: 1px solid #ddd; border-radius: 4px;"></div>
                        </td>
                        <td>
                            <a href="{{ route('attendance_categories.edit', $cat->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection