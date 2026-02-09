@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-map me-2"></i>行程マスター</h3>
        <a href="{{ route('itineraries.create') }}" class="btn btn-primary shadow-sm">新規作成</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">コード</th>
                        <th>区分</th>
                        <th>行程名</th>
                        <th>経由地数</th>
                        <th>最終更新</th>
                        <th class="text-center pe-4">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($itineraries as $itinerary)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $itinerary->itinerary_code }}</td>
                        <td><span class="badge bg-info text-dark">{{ $itinerary->category }}</span></td>
                        <td>{{ $itinerary->itinerary_name }}</td>
                        <td><span class="badge rounded-pill bg-secondary">{{ $itinerary->details_count }} 地点</span></td>
                        <td>{{ $itinerary->updated_at->format('Y/m/d') }}</td>
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