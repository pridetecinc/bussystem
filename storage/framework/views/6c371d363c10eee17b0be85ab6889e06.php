<?php $__env->startSection('title', '行程マスター'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-map me-2"></i>行程マスター</h4>
        <a href="<?php echo e(route('masters.itineraries.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('masters.itineraries.index')); ?>" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="行程コード、行程名、カテゴリーで検索"
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    <?php if(request('search')): ?>
                        <a href="<?php echo e(route('masters.itineraries.index')); ?>" class="btn btn-outline-secondary">
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
            <?php if($itineraries->count() > 0): ?>
                - <?php echo e($itineraries->total()); ?>件の結果が見つかりました
            <?php else: ?>
                - 該当する行程が見つかりませんでした
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th width="100">コード</th>
                        <th width="120">区分</th>
                        <th>行程名</th>
                        <th>備考</th>
                        <th width="150" class="text-center">最終更新</th>
                        <th width="140" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $itineraries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itinerary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td>
                            <span class="badge bg-secondary"><?php echo e($itinerary->itinerary_code); ?></span>
                        </td>
                        <td>
                            <?php if($itinerary->category): ?>
                                <span class="badge bg-info"><?php echo e($itinerary->category); ?></span>
                            <?php else: ?>
                                <span class="text-muted">--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo e($itinerary->itinerary_name); ?></strong>
                        </td>
                        <td>
                            <?php echo e(\Illuminate\Support\Str::limit($itinerary->remarks, 50)); ?>

                        </td>
                        <td class="text-center">
                            <?php if($itinerary->updated_at): ?>
                                <span class="small text-muted"><?php echo e($itinerary->updated_at->format('Y/m/d H:i')); ?></span>
                            <?php else: ?>
                                <span class="text-muted small">--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <!--<a href="<?php echo e(route('masters.itineraries.show', $itinerary)); ?>" -->
                                <!--   class="btn btn-sm btn-outline-info" title="詳細">-->
                                <!--    <i class="bi bi-eye"></i>-->
                                <!--</a>-->
                                <a href="<?php echo e(route('masters.itineraries.edit', $itinerary)); ?>" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこの行程を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="<?php echo e(route('masters.itineraries.destroy', $itinerary)); ?>" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('<?php echo e($itinerary->itinerary_name); ?>')">
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
                        <td colspan="6" class="text-center py-4">
                            <?php if(request('search')): ?>
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する行程が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="bi bi-map display-6 mb-2"></i>
                                    <p class="mb-0">行程データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の行程を登録してください</p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if($itineraries->hasPages()): ?>
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo e($itineraries->onFirstPage() ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo e($itineraries->previousPageUrl()); ?>">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php
                        $current = $itineraries->currentPage();
                        $last = $itineraries->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    ?>

                    <?php if($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($itineraries->url(1)); ?>">1</a>
                        </li>
                        <?php if($start > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                            <a class="page-link" href="<?php echo e($itineraries->url($i)); ?>"><?php echo e($i); ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if($end < $last): ?>
                        <?php if($end < $last - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($itineraries->url($last)); ?>"><?php echo e($last); ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?php echo e(!$itineraries->hasMorePages() ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo e($itineraries->nextPageUrl()); ?>">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: <?php echo e($itineraries->firstItem() ?? 0); ?> - <?php echo e($itineraries->lastItem() ?? 0); ?> / 全 <?php echo e($itineraries->total()); ?> 件
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.badge {
    font-size: 0.85em;
}

.table th {
    font-weight: 600;
}
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/itineraries/index.blade.php ENDPATH**/ ?>