@extends('layouts.app')

@section('title', 'テンプレート詳細: ' . $pdfTemplate->template_name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- 面包屑导航 -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('masters.home') }}">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('masters.pdf_templates.index') }}">PDFテンプレート管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">詳細: {{ $pdfTemplate->template_name }}</li>
                </ol>
            </nav>
            
            <!-- 成功消息 -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif

            <!-- 错误消息 -->
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            @endif
            
            <!-- 详情卡片 -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-pdf-fill"></i> テンプレート詳細
                    </h5>
                    <div>
                        <a href="{{ route('masters.pdf_templates.edit', $pdfTemplate) }}" class="btn btn-light btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                        <a href="{{ route('masters.pdf_templates.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-list"></i> 一覧に戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- 左侧：基本情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">基本情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">ID</dt>
                                <dd class="col-sm-8">
                                    <span class="text-muted small">#{{ $pdfTemplate->id }}</span>
                                </dd>

                                <dt class="col-sm-4">テンプレート名</dt>
                                <dd class="col-sm-8 fw-bold">{{ $pdfTemplate->template_name }}</dd>
                                
                                <dt class="col-sm-4">対応言語</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-secondary">{{ $pdfTemplate->language_code ?? '未設定' }}</span>
                                </dd>
                                
                                <dt class="col-sm-4">ソート順</dt>
                                <dd class="col-sm-8">
                                    <span class="badge bg-info text-dark">{{ $pdfTemplate->sort }}</span>
                                </dd>
                                

                            </dl>
                        </div>
                        
                        <!-- 右侧：ファイル情報 -->
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">ファイル情報</h6>
                            <dl class="row">
                                <dt class="col-sm-4">ファイルパス</dt>
                                <dd class="col-sm-8">
                                    @if($pdfTemplate->template_file)
                                        <a href="{{ $pdfTemplate->template_file }}" target="_blank">
                                            下载
                                        </a>
                                    @else
                                        <span class="text-muted">未設定</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 底部：システム情報 -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h6 class="border-bottom pb-2 mb-3">システム情報</h6>
                            <dl class="row">
                                <dt class="col-sm-2">登録日時</dt>
                                <dd class="col-sm-4">{{ $pdfTemplate->created_at->format('Y/m/d H:i') }}</dd>
                                
                                <dt class="col-sm-2">最終更新日時</dt>
                                <dd class="col-sm-4">{{ $pdfTemplate->updated_at->format('Y/m/d H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    <!-- 操作按钮区域 -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('masters.pdf_templates.edit', $pdfTemplate) }}" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> 編集する
                            </a>
                            
                            <a href="{{ route('masters.pdf_templates.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> 一覧に戻る
                            </a>
                            
                            <script>
                            function confirmDelete(name) {
                                return confirm(`本当にテンプレート「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                            }
                            </script>
                            <form action="{{ route('masters.pdf_templates.destroy', $pdfTemplate) }}" method="POST" 
                                  class="d-inline" onsubmit="return confirmDelete('{{ $pdfTemplate->template_name }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bi bi-trash"></i> 削除
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge {
    font-size: 0.85em;
}

.d-flex.gap-2 > * {
    margin-right: 0.5rem;
}
.d-flex.gap-2 > *:last-child {
    margin-right: 0;
}

/* 文件路径长文本换行支持 */
.text-break {
    word-wrap: break-word;
    word-break: break-all;
}
</style>
@endpush