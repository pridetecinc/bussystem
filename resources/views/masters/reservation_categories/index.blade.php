@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-tags-fill me-2"></i>予約分類マスター</h3>
        <a href="{{ route('reservation_categories.create') }}" class="btn btn-primary shadow-sm">新規登録</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 20%">コード</th>
                        <th style="width: 40%" class="text-start ps-4">分類名</th>
                        <th style="width: 20%">表示ラベル</th>
                        <th style="width: 20%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                    <tr>
                        <td class="fw-bold">{{ $cat->category_code }}</td>
                        <td class="text-start ps-4">{{ $cat->category_name }}</td>
                        <td>
                            <span class="badge" style="background-color: {{ $cat->color_code }}; color: #fff; padding: 0.5rem 1rem;">
                                {{ $cat->category_name }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('reservation_categories.edit', $cat->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection