@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 900px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="bi bi-currency-yen me-2"></i>料金マスター</h3>
        <a href="{{ route('fees.create') }}" class="btn btn-primary shadow-sm">新規登録</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead class="table-dark">
                    <tr>
                        <th style="width: 15%">コード</th>
                        <th style="width: 30%" class="text-start ps-4">項目名</th>
                        <th style="width: 15%">区分</th>
                        <th style="width: 10%">税率</th>
                        <th style="width: 15%" class="text-end pe-4">標準単価</th>
                        <th style="width: 15%">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fees as $fee)
                    <tr>
                        <td class="fw-bold">{{ $fee->fee_code }}</td>
                        <td class="text-start ps-4">{{ $fee->fee_name }}</td>
                        <td>
                            @if($fee->fee_category == '売上')
                                <span class="badge bg-primary">売上</span>
                            @elseif($fee->fee_category == '立替金')
                                <span class="badge bg-warning text-dark">立替金</span>
                            @else
                                <span class="badge bg-danger">値引</span>
                            @endif
                        </td>
                        <td>{{ $fee->tax_rate }}%</td>
                        <td class="text-end pe-4">{{ number_format($fee->default_amount) }}</td>
                        <td>
                            <a href="{{ route('fees.edit', $fee->id) }}" class="btn btn-sm btn-outline-primary px-3">編集</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection