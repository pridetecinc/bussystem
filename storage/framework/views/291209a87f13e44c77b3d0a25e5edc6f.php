<?php $__env->startSection('title', 'ホーム '); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <?php
            $role = session('role', '');
            $isAdmin = $role === 'admin';
            $isOperationsManager = $role === 'operations_manager';
            $isCoordinator = $role === 'coordinator';
            $isManager = $role === 'manager';
            $isDriver = $role === 'driver';
            
            $canViewOperations = $isAdmin || $isOperationsManager || $isCoordinator || $isManager;
            $canViewSales = $isAdmin || $isOperationsManager || $isManager;
            $canViewResults = $isAdmin || $isOperationsManager || $isManager;
            $canViewMaster = $isAdmin || $isOperationsManager;
        ?>

        <?php if($canViewOperations): ?>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-truck me-1"></i>運行管理</h6>
                </div>
                <div class="card-body p-2">
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <a href="<?php echo e(route('masters.itineraries.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">運行台帳</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <a href="<?php echo e(route('masters.drivers.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手台帳</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <a href="#" class="btn btn-outline-secondary btn-sm w-100 mb-1">運行一覧</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <a href="<?php echo e(route('masters.group-infos.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">予約一覧</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <a href="<?php echo e(route('masters.daily-itineraries.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">日次一覧</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <a href="<?php echo e(route('masters.drivers.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">乗務指示一覧</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager || $isCoordinator || $isManager): ?>
                    <div class="dropdown-divider my-1"></div>
                    <a href="<?php echo e(route('masters.basicinfo.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">デジタコデータアップロード</a>
                    <a href="<?php echo e(route('masters.basicinfo.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">アップロード履歴</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($canViewSales): ?>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-cash-stack me-1"></i>売上管理</h6>
                </div>
                <div class="card-body p-2">
                    <?php if($isAdmin || $isOperationsManager || $isManager): ?>
                    <a href="<?php echo e(route('masters.fees.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">請求管理</a>
                    <a href="<?php echo e(route('masters.fees.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">請求詳細</a>
                    <a href="<?php echo e(route('masters.fees.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">入金管理</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($canViewResults): ?>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-graph-up-arrow me-1"></i>実績集計</h6>
                </div>
                <div class="card-body p-2">
                    <?php if($isAdmin || $isOperationsManager || $isManager): ?>
                    <a href="<?php echo e(route('masters.basicinfo.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">輸送実績一覧</a>
                    <a href="<?php echo e(route('masters.drivers.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">乗務実績一覧</a>
                    <a href="<?php echo e(route('masters.fees.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">売上集計</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if($canViewMaster): ?>
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white py-2">
                    <h6 class="mb-0"><i class="bi bi-database-gear me-1"></i>マスター管理</h6>
                </div>
                <div class="card-body p-2">
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.user-company-info.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">基本情報</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.branches.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">営業所</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.staffs.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">スタッフ</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.vehicles.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">車両</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.drivers.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">運転手</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.guides.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">ガイド</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.agencies.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">代理店</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.partners.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">取引先</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.itineraries.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">行程</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.reservation-categories.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">予約分類</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.attendance-categories.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">勤怠分類</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.remarks.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">備考</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.banks.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">Bank</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <a href="<?php echo e(route('masters.vehicle-types.index')); ?>" class="btn btn-outline-secondary btn-sm w-100 mb-1">車両種類</a>
                    <?php endif; ?>
                    
                    <?php if($isAdmin || $isOperationsManager): ?>
                    <div class="dropdown-divider my-1"></div>
                    <a href="<?php echo e(route('masters.login-histories.index')); ?>" class="btn btn-outline-secondary btn-sm w-100">ログイン履歴</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($isDriver && !$canViewOperations && !$canViewSales && !$canViewResults && !$canViewMaster): ?>
        <div class="col-12">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 運転手メニューは現在準備中です。
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .btn-outline-secondary.opacity-75 {
        opacity: 0.75;
    }
    .btn-outline-secondary.opacity-75:hover {
        opacity: 1;
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/home/index.blade.php ENDPATH**/ ?>