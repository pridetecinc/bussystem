@extends('layouts.app')

@section('title', '行程編集')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.itineraries.index') }}">行程管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">行程編集</li>
                </ol>
            </nav>
            
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
                </h5>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-map"></i> 行程編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('masters.itineraries.update', $itinerary) }}" method="POST" id="itineraryForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="itinerary_code" class="form-label required">行程コード</label>
                                <input type="text" class="form-control @error('itinerary_code') is-invalid @enderror" 
                                       id="itinerary_code" name="itinerary_code" 
                                       value="{{ old('itinerary_code', $itinerary->itinerary_code) }}" 
                                       required maxlength="20" placeholder="例: IT001">
                                @error('itinerary_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 必須、20文字以内、他と重複不可</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="itinerary_name" class="form-label required">行程名</label>
                                <input type="text" class="form-control @error('itinerary_name') is-invalid @enderror" 
                                       id="itinerary_name" name="itinerary_name" 
                                       value="{{ old('itinerary_name', $itinerary->itinerary_name) }}" 
                                       required maxlength="100" placeholder="例: 東京一日観光コース">
                                @error('itinerary_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">※ 必須、100文字以内</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">カテゴリー</label>
                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                       id="category" name="category" 
                                       value="{{ old('category', $itinerary->category) }}"
                                       maxlength="50" placeholder="例: 観光、ビジネス、教育">
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">50文字以内</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="remarks" class="form-label">備考</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="4"
                                      maxlength="500" placeholder="例: 行程の詳細説明、注意事項など">{{ old('remarks', $itinerary->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">500文字以内</small>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="{{ route('masters.itineraries.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="if(confirm('本当にこの行程を削除しますか？\\nこの操作は元に戻せません。')) { document.getElementById('deleteForm').submit(); }">
                                    <i class="bi bi-trash"></i> 削除
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <form id="deleteForm" action="{{ route('masters.itineraries.destroy', $itinerary) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
</style>
@endpush