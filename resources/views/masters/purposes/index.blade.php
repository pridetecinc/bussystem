@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-flag-fill me-2"></i>目的マスター</h3>
        <a href="{{ route('purposes.create') }}" class="btn btn-primary shadow-sm">新規登録</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th style="width: 20%">コード</th>
                        <th style="width: 40%" class="text-start ps-4">目的名</th>
                        <th style="width: 20%">区分</th>
                        <th style="width: 20%">操作</th>
                    </tr>
                </thead>
                <tbody class="text-center">
                    @foreach($purposes as $purpose)
                    <tr>
                        <td class="fw-bold">{{ $purpose->purpose_code }}</td>
                        <td class="text-start ps-4">{{ $purpose->purpose_name }}</td>
                        <td><span class="badge bg-info-subtle text-info-emphasis">{{ $purpose->category }}</span></td>
                        <td>
                            <a href="{{ route('purposes.edit', $purpose->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection