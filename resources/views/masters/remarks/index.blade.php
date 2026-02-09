@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-chat-left-text me-2"></i>備考マスター</h3>
        <a href="{{ route('remarks.create') }}" class="btn btn-primary shadow-sm">新規登録</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4" style="width: 10%">コード</th>
                        <th style="width: 15%">カテゴリー</th>
                        <th style="width: 20%">タイトル</th>
                        <th style="width: 45%">備考本文（定型文）</th>
                        <th class="text-center" style="width: 10%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($remarks as $remark)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $remark->remark_code }}</td>
                        <td><span class="badge bg-secondary">{{ $remark->category }}</span></td>
                        <td class="fw-bold">{{ $remark->title }}</td>
                        <td class="text-truncate" style="max-width: 300px;">
                            <small class="text-muted">{{ $remark->content }}</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('remarks.edit', $remark->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection