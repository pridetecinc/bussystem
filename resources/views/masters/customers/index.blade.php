@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-person-lines-fill me-2"></i>顧客マスター</h3>
        <div class="d-flex gap-2">
            <form action="{{ route('customers.index') }}" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="コード・氏名検索" value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-secondary">検索</button>
            </form>
            <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm shadow-sm px-3">
                <i class="bi bi-plus-lg"></i> 新規追加
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">コード</th>
                        <th>区分</th>
                        <th>顧客名</th>
                        <th>所在地</th>
                        <th>担当者</th>
                        <th>支払方法</th>
                        <th class="text-center pe-4">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td class="ps-4 fw-bold text-primary">{{ $customer->customer_code }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $customer->customer_type }}</span></td>
                        <td>
                            <div class="fw-bold">{{ $customer->customer_name }}</div>
                            <small class="text-muted">{{ $customer->customer_name_kana }}</small>
                        </td>
                        <td><small>{{ $customer->address }}</small></td>
                        <td>{{ $customer->manager_name }}</td>
                        <td>{{ $customer->payment_method }}</td>
                        <td class="text-center pe-4">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top">
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection