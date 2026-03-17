<?php $__env->startSection('title', '車両管理'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="bi bi-truck"></i>車両管理</h4>
        <a href="<?php echo e(route('masters.vehicles.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
    </div>
    
    <div class="row">
        <div class="col-md-12">
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
                    <form method="GET" action="<?php echo e(route('masters.vehicles.index')); ?>" class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="車両コード・登録番号・車種・営業所で検索"
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(route('masters.vehicles.index')); ?>" class="btn btn-outline-secondary">
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
                    <?php if($vehicles->count() > 0): ?>
                        - <?php echo e($vehicles->total()); ?>件の結果が見つかりました
                    <?php else: ?>
                        - 該当する車両が見つかりませんでした
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>車両コード</th>
                                <th>登録番号</th>
                                <th>車種</th>
                                <th>モデル</th>
                                <th>所属営業所</th>
                                <th>乗車定員</th>
                                <th>所有形態</th>
                                <th>車検満了日</th>
                                <th>状態</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $vehicles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vehicle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><code><?php echo e($vehicle->vehicle_code); ?></code></td>
                                <td><?php echo e($vehicle->registration_number); ?></td>
                                <td>
                                    <?php if($vehicle->vehicleType): ?>
                                        <?php echo e($vehicle->vehicleType->type_name); ?>

                                    <?php else: ?>
                                        <span class="text-muted">未設定</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vehicle->vehicleModel): ?>
                                        <?php echo e($vehicle->vehicleModel->model_name); ?>

                                        <?php if($vehicle->vehicleModel->maker): ?>
                                            <small class="text-muted">(<?php echo e($vehicle->vehicleModel->maker); ?>)</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">未設定</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vehicle->branch): ?>
                                        <?php echo e($vehicle->branch->branch_name); ?>

                                    <?php else: ?>
                                        <span class="text-muted">未設定</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($vehicle->seating_capacity); ?>名</td>
                                <td>
                                    <?php
                                        $ownershipTypes = [
                                            'company' => '会社所有',
                                            'rental' => 'レンタル',
                                            'personal' => '個人所有'
                                        ];
                                    ?>
                                    <span class="badge bg-info"><?php echo e($ownershipTypes[$vehicle->ownership_type] ?? $vehicle->ownership_type); ?></span>
                                </td>
                                <td>
                                    <?php
                                        $date = $vehicle->inspection_expiration_date;
                                        if ($date instanceof \Carbon\Carbon) {
                                            $formattedDate = $date->format('Y/m/d');
                                            $daysRemaining = now()->startOfDay()->diffInDays($date->startOfDay(), false);
                                        } else {
                                            try {
                                                $carbonDate = \Carbon\Carbon::parse($date)->startOfDay();
                                                $formattedDate = $carbonDate->format('Y/m/d');
                                                $daysRemaining = now()->startOfDay()->diffInDays($carbonDate, false);
                                            } catch (Exception $e) {
                                                $formattedDate = $date;
                                                $daysRemaining = null;
                                            }
                                        }
                                    ?>
                                    <?php echo e($formattedDate); ?>

                                    <?php if(isset($daysRemaining)): ?>
                                        <?php if($daysRemaining < 0): ?>
                                            <span class="badge bg-danger ms-1">切れ</span>
                                        <?php elseif($daysRemaining <= 30 && $daysRemaining >= 0): ?>
                                            <span class="badge bg-warning ms-1">残<?php echo e($daysRemaining); ?>日</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($vehicle->is_active): ?>
                                        <span class="badge bg-success">有効</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">無効</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?php echo e(route('masters.vehicles.show', $vehicle)); ?>" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('masters.vehicles.edit', $vehicle)); ?>" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(vehicleInfo) {
                                            return confirm(`以下の車両を削除しますか？\n\n${vehicleInfo}\n\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="<?php echo e(route('masters.vehicles.destroy', $vehicle)); ?>" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('車両コード: <?php echo e($vehicle->vehicle_code); ?>')">
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
                                <td colspan="10" class="text-center py-4">
                                    <?php if(request('search')): ?>
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致する車両が見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="bi bi-truck display-6 mb-2"></i>
                                            <p class="mb-0">車両データが登録されていません</p>
                                            <p class="small">「新規登録」ボタンから最初の車両を登録してください</p>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <?php if($vehicles->hasPages()): ?>
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo e($vehicles->onFirstPage() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($vehicles->previousPageUrl()); ?>">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                <?php
                                    $current = $vehicles->currentPage();
                                    $last = $vehicles->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                ?>
            
                                <?php if($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($vehicles->url(1)); ?>">1</a>
                                    </li>
                                    <?php if($start > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
            
                                <?php for($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                                        <a class="page-link" href="<?php echo e($vehicles->url($i)); ?>"><?php echo e($i); ?></a>
                                    </li>
                                <?php endfor; ?>
            
                                <?php if($end < $last): ?>
                                    <?php if($end < $last - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($vehicles->url($last)); ?>"><?php echo e($last); ?></a>
                                    </li>
                                <?php endif; ?>
            
                                <li class="page-item <?php echo e(!$vehicles->hasMorePages() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($vehicles->nextPageUrl()); ?>">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: <?php echo e($vehicles->firstItem() ?? 0); ?> - <?php echo e($vehicles->lastItem() ?? 0); ?> / 全 <?php echo e($vehicles->total()); ?> 件
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/vehicles/index.blade.php ENDPATH**/ ?>