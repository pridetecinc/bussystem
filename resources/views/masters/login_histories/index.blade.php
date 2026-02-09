@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="bi bi-shield-lock me-2"></i>ログイン履歴</h3>
        <span class="text-muted small">※直近のアクセス20件を表示しています</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">日時</th>
                        <th>スタッフ名</th>
                        <th>営業所</th>
                        <th>ログインID</th>
                        <th>IPアドレス</th>
                        <th class="text-center">状態</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($histories as $history)
                    <tr>
                        <td class="ps-4">{{ $history->logged_at }}</td>
                        <td>{{ $history->staff->name ?? '---' }}</td>
                        <td>{{ $history->staff->branch->branch_name ?? '---' }}</td>
                        <td><code>{{ $history->login_id }}</code></td>
                        <td><small>{{ $history->ip_address }}</small></td>
                        <td class="text-center">
                            @if($history->status == '成功')
                                <span class="badge bg-success-subtle text-success border border-success px-3">成功</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger px-3">失敗</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">
            {{ $histories->links() }}
        </div>
    </div>
</div>
@endsection