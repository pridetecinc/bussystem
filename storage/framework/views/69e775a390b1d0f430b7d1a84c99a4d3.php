<?php $__env->startSection('title', 'バス割当詳細'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="bi bi-person-badge me-2"></i>バス割当詳細
                    </h6>
                </div>
                
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <?php
                                $statusColor = $busAssignment->status_finalized ? 'success' : 
                                              ($busAssignment->status_sent ? 'info' : 
                                              ($busAssignment->lock_arrangement ? 'warning' : 'secondary'));
                                $badgeColor = $busAssignment->status_finalized ? 'success' : 
                                             ($busAssignment->status_sent ? 'primary' : 
                                             ($busAssignment->lock_arrangement ? 'warning' : 'secondary'));
                                $statusIcon = $busAssignment->status_finalized ? 'fa-check-circle' : 
                                            ($busAssignment->status_sent ? 'fa-paper-plane' : 
                                            ($busAssignment->lock_arrangement ? 'fa-lock' : 'fa-pen'));
                            ?>
                            
                            <div class="alert alert-<?php echo e($statusColor); ?> py-2 mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <strong class="me-2">ステータス:</strong>
                                        <span class="badge bg-<?php echo e($badgeColor); ?> py-1 px-3">
                                            <i class="fas <?php echo e($statusIcon); ?> me-1"></i>
                                            <?php echo e($busAssignment->status_display); ?>

                                        </span>
                                    </div>
                                    <div class="d-flex gap-3">
                                        <span class="d-flex align-items-center">
                                            <i class="fas <?php echo e($busAssignment->lock_arrangement ? 'fa-lock text-warning' : 'fa-lock-open text-secondary'); ?> me-1"></i>
                                            <?php echo e($busAssignment->lock_arrangement ? 'ロック中' : 'ロック解除'); ?>

                                        </span>
                                        <span class="d-flex align-items-center">
                                            <i class="fas <?php echo e($busAssignment->status_sent ? 'fa-paper-plane text-primary' : 'fa-clock text-secondary'); ?> me-1"></i>
                                            <?php echo e($busAssignment->status_sent ? '送信済' : '未送信'); ?>

                                        </span>
                                        <span class="d-flex align-items-center">
                                            <i class="fas <?php echo e($busAssignment->status_finalized ? 'fa-check-circle text-success' : 'fa-circle text-secondary'); ?> me-1"></i>
                                            <?php echo e($busAssignment->status_finalized ? '最終確定' : '未確定'); ?>

                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <table class="table table-sm table-bordered mb-0">
                                <tr>
                                    <th class="bg-light align-middle" style="width: 15%;">Key UUID</th>
                                    <td class="align-middle" colspan="3">
                                        <code class="bg-light p-1 rounded"><?php echo e($busAssignment->key_uuid); ?></code>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light align-middle">団体/予約</th>
                                    <td class="align-middle" style="width: 35%;">
                                        <?php if($busAssignment->groupInfo): ?>
                                            <div class="d-flex flex-column">
                                                <a href="<?php echo e(route('masters.group-infos.edit', $busAssignment->groupInfo->id)); ?>" class="fw-bold text-decoration-none">
                                                    <?php echo e($busAssignment->groupInfo->group_name); ?>

                                                </a>
                                                <small class="text-muted">
                                                    <?php echo e($busAssignment->groupInfo->start_date ? \Carbon\Carbon::parse($busAssignment->groupInfo->start_date)->format('Y/m/d') : '---'); ?> 
                                                    〜 
                                                    <?php echo e($busAssignment->groupInfo->end_date ? \Carbon\Carbon::parse($busAssignment->groupInfo->end_date)->format('Y/m/d') : '---'); ?>

                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">---</span>
                                        <?php endif; ?>
                                    </td>
                                    <th class="bg-light align-middle" style="width: 15%;">車両</th>
                                    <td class="align-middle">
                                        <?php if($busAssignment->vehicle): ?>
                                            <div class="d-flex flex-column">
                                                <a href="<?php echo e(route('masters.vehicles.show', $busAssignment->vehicle->id)); ?>" class="fw-bold text-decoration-none">
                                                    <?php echo e($busAssignment->vehicle->registration_number); ?>

                                                </a>
                                                <small class="text-muted">
                                                    <?php echo e($busAssignment->vehicle->vehicleModel->model_name ?? '不明'); ?> / 
                                                    <?php echo e($busAssignment->vehicle->seating_capacity); ?>名 / 
                                                    <?php echo e($busAssignment->vehicle->branch->branch_name ?? '不明'); ?>

                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">未割当</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light align-middle">日次カウント</th>
                                    <td class="align-middle">
                                        <span class="badge bg-info py-1 px-2">
                                            <?php echo e($busAssignment->count_daily ?? 0); ?>

                                        </span>
                                        <small class="text-muted ms-2">件の日別旅程</small>
                                    </td>
                                    <th class="bg-light align-middle">ドライバー</th>
                                    <td class="align-middle">
                                        <?php if($busAssignment->driver): ?>
                                            <div class="d-flex align-items-center">
                                                <a href="<?php echo e(route('masters.drivers.show', $busAssignment->driver->id)); ?>" class="fw-bold text-decoration-none">
                                                    <?php echo e($busAssignment->driver->name); ?>

                                                </a>
                                                <small class="text-muted ms-2">
                                                    (<?php echo e($busAssignment->driver->branch->branch_name ?? '不明'); ?>)
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic">未割当</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <?php if($busAssignment->dailyItineraries && $busAssignment->dailyItineraries->count() > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="text-center" style="width: 100px;">日付</th>
                                                <th class="text-center" style="width: 120px;">時間</th>
                                                <th>行程</th>
                                                <th style="width: 150px;">開始場所</th>
                                                <th style="width: 150px;">終了場所</th>
                                                <th style="width: 120px;">宿泊</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $busAssignment->dailyItineraries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itinerary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <?php echo e($itinerary->date ? \Carbon\Carbon::parse($itinerary->date)->format('Y/m/d') : '---'); ?>

                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <?php if($itinerary->time_start || $itinerary->time_end): ?>
                                                            <span class="text-nowrap"><?php echo e($itinerary->time_start ?? '---'); ?>〜<?php echo e($itinerary->time_end ?? '---'); ?></span>
                                                        <?php else: ?>
                                                            ---
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="align-middle">
                                                        <div class="text-truncate" style="max-width: 300px;" title="<?php echo e($itinerary->itinerary); ?>">
                                                            <?php echo e($itinerary->itinerary ?: '---'); ?>

                                                        </div>
                                                    </td>
                                                    <td class="align-middle"><?php echo e($itinerary->start_location ?: '---'); ?></td>
                                                    <td class="align-middle"><?php echo e($itinerary->end_location ?: '---'); ?></td>
                                                    <td class="align-middle"><?php echo e($itinerary->accommodation ?: '---'); ?></td>
                                                </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4 border rounded">
                                    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-2"></i>
                                    <span class="text-muted">関連する日別旅程はありません。</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .card-body {
        font-size: 0.825rem;
    }
    
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-weight: 500;
    }
    
    .btn-outline-info {
        border-width: 1px;
        padding: 0.2rem 0.5rem;
    }
    
    .btn-outline-info:hover {
        background-color: #0dcaf0;
        color: white;
    }
    
    .text-truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .alert {
        border-left-width: 4px;
    }
    
    .alert-success { border-left-color: #198754; }
    .alert-info { border-left-color: #0dcaf0; }
    .alert-warning { border-left-color: #ffc107; }
    .alert-secondary { border-left-color: #6c757d; }
    
    .table a {
        color: #0d6efd;
        transition: color 0.2s;
    }
    
    .table a:hover {
        color: #0a58ca;
        text-decoration: underline !important;
    }
    
    @media (max-width: 768px) {
        .d-flex.gap-3 {
            flex-direction: column;
            gap: 0.5rem !important;
        }
        
        .table th {
            width: auto !important;
        }
    }
</style>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/bus-assignments/show.blade.php ENDPATH**/ ?>