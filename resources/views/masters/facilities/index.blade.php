@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-building-check me-2"></i>施設マスター</h3>
        <div class="d-flex gap-2">
            <form action="{{ route('facilities.index') }}" method="GET" class="d-flex">
                <select name="category" class="form-select form-select-sm me-2" onchange="this.form.submit()">
                    <option value="">全カテゴリー</option>
                    <option value="ホテル" {{ request('category') == 'ホテル' ? 'selected' : '' }}>ホテル</option>
                    <option value="観光施設" {{ request('category') == '観光施設' ? 'selected' : '' }}>観光施設</option>
                    <option value="食事所" {{ request('category') == '食事所' ? 'selected' : '' }}>食事所</option>
                    <option value="駐車場" {{ request('category') == '駐車場' ? 'selected' : '' }}>駐車場</option>
                </select>
            </form>
            <a href="{{ route('facilities.create') }}" class="btn btn-primary btn-sm px-3 shadow-sm">新規登録</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">コード</th>
                        <th>カテゴリー</th>
                        <th>施設名</th>
                        <th>電話番号</th>
                        <th>バス駐車</th>
                        <th>所在地</th>
                        <th class="text-center pe-4">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facilities as $facility)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $facility->facility_code }}</td>
                        <td><span class="badge bg-secondary">{{ $facility->category }}</span></td>
                        <td>
                            <div class="fw-bold">{{ $facility->facility_name }}</div>
                            <small class="text-muted">{{ $facility->facility_kana }}</small>
                        </td>
                        <td>{{ $facility->phone_number }}</td>
                        <td class="text-center">
                            @if($facility->bus_parking_available)
                                <i class="bi bi-p-circle-fill text-primary fs-5" title="駐車可"></i>
                            @else
                                <span class="text-muted">---</span>
                            @endif
                        </td>
                        <td><small>{{ $facility->address }}</small></td>
                        <td class="text-center pe-4">
                            <a href="#" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection