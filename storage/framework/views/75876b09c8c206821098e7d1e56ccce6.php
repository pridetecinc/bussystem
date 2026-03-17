<?php $__env->startSection('title', '運転手マスター'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-person-badge me-2"></i>運転手マスター</h4>
        <a href="<?php echo e(route('masters.drivers.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('masters.drivers.index')); ?>" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="コード・氏名・免許種類で検索"
                           value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i> 検索
                    </button>
                    <?php if(request()->hasAny(['search', 'branch_id', 'is_active', 'license_expiring'])): ?>
                        <a href="<?php echo e(route('masters.drivers.index')); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> クリア
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <?php if(request()->hasAny(['search', 'branch_id', 'is_active', 'license_expiring'])): ?>
        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            検索条件: 
            <?php
                $filters = [];
                if(request('search')) $filters[] = 'キーワード: "' . request('search') . '"';
                if(request('branch_id')) {
                    $branch = $branches->firstWhere('id', request('branch_id'));
                    if($branch) $filters[] = '支店: ' . $branch->branch_name;
                }
                if(request('is_active') !== '') {
                    $filters[] = '状態: ' . (request('is_active') ? '有効' : '無効');
                }
                if(request('license_expiring')) {
                    $filters[] = '免許期限間近のみ';
                }
            ?>
            <?php echo e(implode('、', $filters)); ?>

            
            <?php if($drivers->count() > 0): ?>
                - <?php echo e($drivers->total()); ?>件の結果が見つかりました
            <?php else: ?>
                - 該当する運転手が見つかりませんでした
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="mb-3">
        <div class="table-responsive">
            <table class="table table-bordered mb-0 table-striped">
                <thead class="table-secondary">
                    <tr>
                        <th>コード</th>
                        <th>氏名</th>
                        <th>支店</th>
                        <th>電話番号</th>
                        <th>免許種類</th>
                        <th>免許有効期限</th>
                        <th>状態</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $drivers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $driver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($driver->driver_code); ?></td>
                        <td>
                            <div><?php echo e($driver->name); ?></div>
                            <div class="small text-muted"><?php echo e($driver->name_kana); ?></div>
                        </td>
                        <td>
                            <?php if($driver->branch): ?>
                                <div class="small text-muted"><?php echo e($driver->branch->branch_code); ?></div>
                                <div><?php echo e($driver->branch->branch_name); ?></div>
                            <?php else: ?>
                                <span class="text-muted">未設定</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo e($driver->phone_number); ?></td>
                        <td><?php echo e($driver->license_type); ?></td>
                        <td>
                            <?php
                                $expirationDate = \Carbon\Carbon::parse($driver->license_expiration_date);
                                $daysRemaining = now()->diffInDays($expirationDate, false);
                                $daysRemainingInt = (int)round($daysRemaining);
                                $isExpiring = $daysRemainingInt <= 30 && $daysRemainingInt >= 0;
                                $isExpired = $daysRemainingInt < 0;
                            ?>
                            <div class="<?php echo e($isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : '')); ?>">
                                <?php echo e($expirationDate->format('Y-m-d')); ?>

                                <?php if($driver->is_active): ?>
                                    <?php if($isExpired): ?>
                                        <span class="badge bg-danger">期限切れ</span>
                                    <?php elseif($isExpiring): ?>
                                        <span class="badge bg-warning">間近</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if($driver->is_active): ?>
                                <span class="badge bg-success">有効</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">無効</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="<?php echo e(route('masters.drivers.show', $driver)); ?>" 
                                   class="btn btn-sm btn-outline-info" title="詳細">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?php echo e(route('masters.drivers.edit', $driver)); ?>" 
                                   class="btn btn-sm btn-outline-primary" title="編集">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <script>
                                function confirmDelete(name) {
                                    return confirm('本当に削除しますか？この操作は元に戻せません。');
                                }
                                </script>
                                <form action="<?php echo e(route('masters.drivers.destroy', $driver)); ?>" method="POST" 
                                      class="d-inline" 
                                      onsubmit="return confirmDelete('<?php echo e($driver->name); ?>')">
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
                        <td colspan="8" class="text-center py-4">
                            <?php if(request()->hasAny(['search', 'branch_id', 'is_active', 'license_expiring'])): ?>
                                <div class="text-muted">
                                    <i class="bi bi-search display-6 mb-2"></i>
                                    <p class="mb-0">検索条件に一致する運転手が見つかりませんでした</p>
                                    <p class="small">検索条件を変更してお試しください</p>
                                </div>
                            <?php else: ?>
                                <div class="text-muted">
                                    <i class="bi bi-person display-6 mb-2"></i>
                                    <p class="mb-0">運転手データが登録されていません</p>
                                    <p class="small">「新規追加」ボタンから最初の運転手を登録してください</p>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php if($drivers->hasPages()): ?>
        <div class="mt-3">
            <nav>
                <ul class="pagination justify-content-center mb-0">
                    <li class="page-item <?php echo e($drivers->onFirstPage() ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo e($drivers->previousPageUrl()); ?>">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php
                        $current = $drivers->currentPage();
                        $last = $drivers->lastPage();
                        $start = max(1, $current - 2);
                        $end = min($last, $current + 2);
                    ?>

                    <?php if($start > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($drivers->url(1)); ?>">1</a>
                        </li>
                        <?php if($start > 2): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                            <a class="page-link" href="<?php echo e($drivers->url($i)); ?>"><?php echo e($i); ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if($end < $last): ?>
                        <?php if($end < $last - 1): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo e($drivers->url($last)); ?>"><?php echo e($last); ?></a>
                        </li>
                    <?php endif; ?>

                    <li class="page-item <?php echo e(!$drivers->hasMorePages() ? 'disabled' : ''); ?>">
                        <a class="page-link" href="<?php echo e($drivers->nextPageUrl()); ?>">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <div class="text-center text-muted small mt-2">
                表示中: <?php echo e($drivers->firstItem() ?? 0); ?> - <?php echo e($drivers->lastItem() ?? 0); ?> / 全 <?php echo e($drivers->total()); ?> 件
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/drivers/index.blade.php ENDPATH**/ ?>