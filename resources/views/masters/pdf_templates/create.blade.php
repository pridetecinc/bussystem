@extends('layouts.app')

@section('title', '新規テンプレート作成')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.pdf_templates.index') }}">PDF テンプレート管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">新規作成</li>
                </ol>
            </nav>
            
            <!-- 成功消息 -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- 错误消息 (Session) -->
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- 验证错误列表 -->
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
            
            <!-- 卡片表单 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <!-- 图标改为 Word 图标 -->
                        <i class="bi bi-file-earmark-word-fill"></i> 新規テンプレート作成
                    </h5>
                </div>
                
                <div class="card-body">
                    <!-- 重要：添加 enctype="multipart/form-data" -->
                    <form action="{{ route('masters.pdf_templates.store') }}" method="POST" id="templateForm" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- テンプレート名 -->
                            <div class="col-md-6 mb-3">
                                <label for="template_name" class="form-label required">テンプレート名</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('template_name') is-invalid @enderror" 
                                       id="template_name" name="template_name" 
                                       value="{{ old('template_name') }}" 
                                       required maxlength="100" placeholder="">
                                @error('template_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <!-- 対応言語 -->
                            <div class="col-md-6 mb-3">
                                <label for="language_code" class="form-label required">対応言語</label><span class="text-danger">*</span>
                                <input type="text" class="form-control @error('language_code') is-invalid @enderror" 
                                       id="language_code" name="language_code" 
                                       value="" 
                                       required maxlength="10" placeholder="例：ja, en, zh">
                                @error('language_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- ソート順 -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="sort" class="form-label">ソート順 (表示優先度)</label><span class="text-danger">*</span>
                                <input type="number" class="form-control @error('sort') is-invalid @enderror" 
                                       id="sort" name="sort" 
                                       value="{{ old('sort', 1) }}"
                                       min="0" max="9999" step="1">
                                @error('sort')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- 【修改点】ファイルアップロード (Word) -->
                        <div class="mb-3">
                            <label for="template_file" class="form-label required">テンプレートファイル ( Word ファイルをアップロードしてください)</label><span class="text-danger">*</span>
                            <!-- 类型改为 file，添加 accept 限制，移除 maxlength -->
                            <input type="file" class="form-control @error('template_file') is-invalid @enderror" 
                                   id="template_file" name="template_file" 
                                   accept=".doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                   required>
                            @error('template_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        

                        
                        <!-- 按钮区域 -->
                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 作成する
                                </button>
                                <a href="{{ route('masters.pdf_templates.index') }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            <div class="text-muted small align-self-center">
                                <span class="text-danger">*</span> は必須項目です
                            </div>
                        </div>
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