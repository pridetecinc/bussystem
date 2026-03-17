<?php $__env->startSection('title', '勤怠分類マスター'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-calendar-check"></i>勤怠分類マスター</h4>
                <a href="<?php echo e(route('masters.attendance-categories.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
            </div>
            
            <?php if(session('success')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo e(session('success')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if(session('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo e(session('error')); ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('masters.attendance-categories.index')); ?>" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="コード・勤怠名で検索"
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(route('masters.attendance-categories.index')); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i> クリア
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php if(request('search')): ?>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    検索条件: "<?php echo e(request('search')); ?>" 
                    <?php if($categories->count() > 0): ?>
                        - <?php echo e($categories->total()); ?>件の結果が見つかりました
                    <?php else: ?>
                        - 該当する勤怠分類が見つかりませんでした
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>コード</th>
                                <th>勤怠名</th>
                                <th>表示ラベル</th>
                                <th width="100">勤務日</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><code><?php echo e($category->attendance_code); ?></code></td>
                                <td><?php echo e($category->attendance_name); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo e($category->color_code); ?>; color: white; padding: 0.35em 0.65em;">
                                        <?php echo e($category->attendance_name); ?>

                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if($category->is_work_day): ?>
                                        <span class="badge bg-success">✓</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">✗</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?php echo e(route('masters.attendance-categories.show', $category)); ?>" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('masters.attendance-categories.edit', $category)); ?>" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="<?php echo e(route('masters.attendance-categories.destroy', $category)); ?>" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('<?php echo e($category->attendance_name); ?>')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="削除">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <?php if(request('search')): ?>
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致する勤怠分類が見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="bi bi-calendar-check display-6 mb-2"></i>
                                            <p class="mb-0">勤怠分類データが登録されていません</p>
                                            <p class="small">「新規登録」ボタンから最初の勤怠分類を登録してください</p>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <?php if($categories->hasPages()): ?>
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo e($categories->onFirstPage() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($categories->previousPageUrl()); ?>">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                <?php
                                    $current = $categories->currentPage();
                                    $last = $categories->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                ?>
            
                                <?php if($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($categories->url(1)); ?>">1</a>
                                    </li>
                                    <?php if($start > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
            
                                <?php for($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                                        <a class="page-link" href="<?php echo e($categories->url($i)); ?>"><?php echo e($i); ?></a>
                                    </li>
                                <?php endfor; ?>
            
                                <?php if($end < $last): ?>
                                    <?php if($end < $last - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($categories->url($last)); ?>"><?php echo e($last); ?></a>
                                    </li>
                                <?php endif; ?>
            
                                <li class="page-item <?php echo e(!$categories->hasMorePages() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($categories->nextPageUrl()); ?>">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: <?php echo e($categories->firstItem() ?? 0); ?> - <?php echo e($categories->lastItem() ?? 0); ?> / 全 <?php echo e($categories->total()); ?> 件
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/attendance-categories/index.blade.php ENDPATH**/ ?>