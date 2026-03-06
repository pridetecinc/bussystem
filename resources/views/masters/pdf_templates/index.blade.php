@extends('layouts.app')

@section('title', 'PDFテンプレートマスター')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-file-earmark-pdf me-2 text-danger"></i>PDFテンプレートマスター</h4>
        <a href="{{ route('masters.pdf_templates.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> 新規追加
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <!-- 搜索区域 -->
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('masters.pdf_templates.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" 
                           placeholder="テンプレート名検索"
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    @if(request('search'))
                        <a href="{{ route('masters.pdf_templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> クリア
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
    
    <!-- 搜索结果提示 -->
    @if(request('search'))
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: "{{ request('search') }}" 
            @if($templates->count() > 0)
                - {{ $templates->total() }}件の結果が見つかりました
            @else
                - 該当するテンプレートが見つかりませんでした
            @endif
        </div>
    @endif

    <!-- 表格区域 -->
    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-center" style="width: 80px;">ID</th>
                        <th>テンプレート名</th>
                        <th class="text-center" style="width: 100px;">対応言語</th>
                        <th class="text-center" style="width: 100px;">ソート順</th>
                        <th>ファイルパス</th>
                        <th class="text-center" style="width: 150px;">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                    <tr>
                        <td class="text-center text-muted small">{{ $template->id }}</td>
                        <td class="fw-bold">{{ $template->template_name }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $template->language_code ?? '--' }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $template->sort }}</span>
                        </td>
                        <td>
                            <div class="text-truncate">
                                <i class="bi bi-folder2-open me-1 text-warning"></i>{{ $template->template_file }}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="{{ route('masters.pdf_templates.show', $template) }}" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('masters.pdf_templates.edit', $template) }}" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこのテンプレート「' + name + '」を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="{{ route('masters.pdf_templates.destroy', $template) }}" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('{{ $template->template_name }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="削除">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            @if(request('search'))
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致するテンプレートが見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="bi bi-file-earmark-pdf display-6 mb-2"></i>
                                    <p class="mb-0">PDFテンプレートデータが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初のテンプレートを登録してください</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- 分页区域 (完全复用原有逻辑) -->
    @if($templates->hasPages())
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item {{ $templates->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $templates->previousPageUrl() }}">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    @php
                        $current = $templates->currentPage();
                        $last = $templates->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    @endphp

                    @if($start > 1)
                        <li class="page-item">
                            <a class="page-link" href="{{ $templates->url(1) }}">1</a>
                        </li>
                        @if($start > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                    @endif

                    @for($i = $start; $i <= $end; $i++)
                        <li class="page-item {{ $i == $current ? 'active' : '' }}">
                            <a class="page-link" href="{{ $templates->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if($end < $last)
                        @if($end < $last - 1)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        @endif
                        <li class="page-item">
                            <a class="page-link" href="{{ $templates->url($last) }}">{{ $last }}</a>
                        </li>
                    @endif

                    <li class="page-item {{ !$templates->hasMorePages() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $templates->nextPageUrl() }}">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: {{ $templates->firstItem() ?? 0 }} - {{ $templates->lastItem() ?? 0 }} / 全 {{ $templates->total() }} 件
            </div>
        </div>
    @endif
</div>
@endsection