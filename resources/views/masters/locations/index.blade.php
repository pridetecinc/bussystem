@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-geo-alt-fill me-2"></i>地名マスター</h3>
        <a href="{{ route('locations.create') }}" class="btn btn-primary shadow-sm">新規追加</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 15%">コード</th>
                        <th style="width: 15%">区分</th>
                        <th style="width: 20%">都道府県</th>
                        <th style="width: 30%" class="text-start">地名</th>
                        <th style="width: 20%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locations as $location)
                    <tr>
                        <td class="fw-bold">{{ $location->location_code }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $location->area_type }}</span></td>
                        <td>{{ $location->prefecture }}</td>
                        <td class="text-start">
                            <div class="fw-bold">{{ $location->location_name }}</div>
                            <small class="text-muted">{{ $location->location_kana }}</small>
                        </td>
                        <td>
                            <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection