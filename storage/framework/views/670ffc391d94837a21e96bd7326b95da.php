<?php $__env->startSection('title', '車両詳細'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('masters.home')); ?>">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('masters.vehicles.index')); ?>">車両管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">車両詳細</li>
                </ol>
            </nav>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-truck"></i> 車両詳細情報
                    </h5>
                    <div class="d-flex gap-1">
                        <a href="<?php echo e(route('masters.vehicles.index')); ?>" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> 一覧に戻る
                        </a>
                        <a href="<?php echo e(route('masters.vehicles.edit', $vehicle)); ?>" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> 編集
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light text-white">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> 基本情報</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">車両コード</div>
                                        <div class="col-md-8">
                                            <code><?php echo e($vehicle->vehicle_code); ?></code>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">登録番号</div>
                                        <div class="col-md-8"><?php echo e($vehicle->registration_number); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">車両種類</div>
                                        <div class="col-md-8">
                                            <?php if($vehicle->vehicleType): ?>
                                                <?php echo e($vehicle->vehicleType->type_name); ?>

                                            <?php else: ?>
                                                <span class="text-muted">未設定</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">モデル</div>
                                        <div class="col-md-8">
                                            <?php if($vehicle->vehicleModel): ?>
                                                <?php echo e($vehicle->vehicleModel->model_name); ?>

                                                <?php if($vehicle->vehicleModel->maker): ?>
                                                    <small class="text-muted">(<?php echo e($vehicle->vehicleModel->maker); ?>)</small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">未設定</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">所属営業所</div>
                                        <div class="col-md-8">
                                            <?php if($vehicle->branch): ?>
                                                <?php echo e($vehicle->branch->branch_code); ?> - <?php echo e($vehicle->branch->branch_name); ?>

                                            <?php else: ?>
                                                <span class="text-muted">未設定</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">乗車定員</div>
                                        <div class="col-md-8"><?php echo e($vehicle->seating_capacity); ?>名</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light text-white">
                                    <h6 class="mb-0"><i class="bi bi-gear"></i> 管理情報</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">所有形態</div>
                                        <div class="col-md-8">
                                            <?php
                                                $ownershipTypes = [
                                                    'company' => '会社所有',
                                                    'rental' => 'レンタル',
                                                    'personal' => '個人所有'
                                                ];
                                            ?>
                                            <span class="badge bg-info"><?php echo e($ownershipTypes[$vehicle->ownership_type] ?? $vehicle->ownership_type); ?></span>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">車検満了日</div>
                                        <div class="col-md-8">
                                            <?php
                                                $date = $vehicle->inspection_expiration_date;
                                                if ($date instanceof \Carbon\Carbon) {
                                                    $formattedDate = $date->format('Y年m月d日');
                                                    $daysRemaining = now()->startOfDay()->diffInDays($date->startOfDay(), false);
                                                } else {
                                                    try {
                                                        $carbonDate = \Carbon\Carbon::parse($date)->startOfDay();
                                                        $formattedDate = $carbonDate->format('Y年m月d日');
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
                                                    <span class="badge bg-danger ms-2">期限切れ</span>
                                                <?php elseif($daysRemaining <= 30 && $daysRemaining >= 0): ?>
                                                    <span class="badge bg-warning ms-2">残り<?php echo e($daysRemaining); ?>日</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success ms-2">有効</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">ステータス</div>
                                        <div class="col-md-8">
                                            <?php if($vehicle->is_active): ?>
                                                <span class="badge bg-success">有効</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">無効</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">表示順序</div>
                                        <div class="col-md-8"><?php echo e($vehicle->display_order ?? '未設定'); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">登録日時</div>
                                        <div class="col-md-8">
                                            <?php echo e($vehicle->created_at->format('Y年m月d日 H:i')); ?>

                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 fw-bold">更新日時</div>
                                        <div class="col-md-8">
                                            <?php echo e($vehicle->updated_at->format('Y年m月d日 H:i')); ?>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($vehicle->remarks): ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="info-section">
                                <h6 class="border-bottom pb-2 mb-3">備考</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <?php echo e($vehicle->remarks); ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?php echo e(route('masters.vehicles.index')); ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> 一覧に戻る
                        </a>
                        <a href="<?php echo e(route('masters.vehicles.edit', $vehicle)); ?>" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> 編集する
                        </a>
                        <form action="<?php echo e(route('masters.vehicles.destroy', $vehicle)); ?>" method="POST" class="d-inline">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('本当に削除しますか？')">
                                <i class="bi bi-trash"></i> 削除する
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/vehicles/show.blade.php ENDPATH**/ ?>