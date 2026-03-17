<?php $__env->startSection('title', '車両種類マスター'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-truck me-2"></i>車両種類マスター</h4>
        <a href="<?php echo e(route('masters.vehicle-types.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('masters.vehicle-types.index')); ?>" class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="車両種類名で検索"
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    <?php if(request('search')): ?>
                        <a href="<?php echo e(route('masters.vehicle-types.index')); ?>" class="btn btn-outline-secondary">
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
            <?php if($vehicleTypes->count() > 0): ?>
                - <?php echo e($vehicleTypes->total()); ?>件の結果が見つかりました
            <?php else: ?>
                - 該当する車両種類が見つかりませんでした
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary align-middle">
                    <tr>
                        <th>車両種類名</th>
                        <th width="150" class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $vehicleTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicleType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($vehicleType->type_name); ?></td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?php echo e(route('masters.vehicle-types.edit', $vehicleType)); ?>" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当にこの車両種類を削除しますか？\nこの操作は元に戻せません。');
                                }
                                </script>
                                <form action="<?php echo e(route('masters.vehicle-types.destroy', $vehicleType)); ?>" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('<?php echo e($vehicleType->type_name); ?>')">
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
                        <td colspan="2" class="text-center py-4">
                            <?php if(request('search')): ?>
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する車両種類が見つかりませんでした</p>
                                    <p class="small">検索キーワードを変更してお試しください</p>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="bi bi-truck display-6 mb-2"></i>
                                    <p class="mb-0">車両種類データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の車両種類を登録してください</p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if($vehicleTypes->hasPages()): ?>
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo e($vehicleTypes->onFirstPage() ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo e($vehicleTypes->previousPageUrl()); ?>">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php
                        $current = $vehicleTypes->currentPage();
                        $last = $vehicleTypes->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    ?>

                    <?php if($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($vehicleTypes->url(1)); ?>">1</a>
                        </li>
                        <?php if($start > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                            <a class="page-link" href="<?php echo e($vehicleTypes->url($i)); ?>"><?php echo e($i); ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if($end < $last): ?>
                        <?php if($end < $last - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($vehicleTypes->url($last)); ?>"><?php echo e($last); ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?php echo e(!$vehicleTypes->hasMorePages() ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo e($vehicleTypes->nextPageUrl()); ?>">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: <?php echo e($vehicleTypes->firstItem() ?? 0); ?> - <?php echo e($vehicleTypes->lastItem() ?? 0); ?> / 全 <?php echo e($vehicleTypes->total()); ?> 件
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/vehicle-types/index.blade.php ENDPATH**/ ?>