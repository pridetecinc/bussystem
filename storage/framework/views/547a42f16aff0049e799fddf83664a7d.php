<?php $__env->startSection('title', 'ログイン履歴'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-clock-history"></i>ログイン履歴</h4>
            </div>
            
            <div class="mb-3">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('masters.login-histories.index')); ?>" class="row g-2">
                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="ログインID・IPアドレス・スタッフ名で検索"
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="staff_id" class="form-control">
                                <option value="">スタッフを選択</option>
                                <?php $__currentLoopData = $staffList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($id); ?>" <?php echo e(request('staff_id') == $id ? 'selected' : ''); ?>><?php echo e($name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">ステータス</option>
                                <option value="success" <?php echo e(request('status') == 'success' ? 'selected' : ''); ?>>成功</option>
                                <option value="failed" <?php echo e(request('status') == 'failed' ? 'selected' : ''); ?>>失敗</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="start_date" class="form-control" 
                                   value="<?php echo e(request('start_date')); ?>" 
                                   placeholder="開始日">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="end_date" class="form-control" 
                                   value="<?php echo e(request('end_date')); ?>" 
                                   placeholder="終了日">
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search"></i> 検索
                            </button>
                        </div>
                    
                        <?php if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date'])): ?>
                            <div class="col-md-1">
                                <a href="<?php echo e(route('masters.login-histories.index')); ?>" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center px-2" style="height:100%;">
                                    <i class="bi bi-x-circle me-1"></i> クリア
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <?php if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date'])): ?>
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    検索結果: <?php echo e($loginHistories->total()); ?>件
                    <?php if(request('search')): ?> - 検索キーワード: "<?php echo e(request('search')); ?>" <?php endif; ?>
                    <?php if(request('staff_id')): ?> - スタッフ: <?php echo e($staffList[request('staff_id')] ?? ''); ?> <?php endif; ?>
                    <?php if(request('status')): ?> - ステータス: <?php echo e(request('status') == 'success' ? '成功' : '失敗'); ?> <?php endif; ?>
                    <?php if(request('start_date')): ?> - 期間: <?php echo e(request('start_date')); ?> <?php endif; ?>
                    <?php if(request('end_date')): ?> ~ <?php echo e(request('end_date')); ?> <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th width="80">ID</th>
                                <th>スタッフ</th>
                                <th>ログインID</th>
                                <th>日時</th>
                                <th>IPアドレス</th>
                                <th>ステータス</th>
                                <th>ユーザーエージェント</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $loginHistories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="text-center"><?php echo e($history->id); ?></td>
                                <td>
                                    <?php if($history->staff): ?>
                                        <?php echo e($history->staff->name); ?>

                                    <?php else: ?>
                                        <span class="text-muted">削除済み</span>
                                    <?php endif; ?>
                                </td>
                                <td><code><?php echo e($history->login_id); ?></code></td>
                                <td><?php echo e($history->logged_at ? date('Y/m/d H:i:s', strtotime($history->logged_at)) : '-'); ?></td>
                                <td><code><?php echo e($history->ip_address); ?></code></td>
                                <td>
                                    <?php if($history->status == 'success'): ?>
                                        <span class="badge bg-success">成功</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">失敗</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo e(Str::limit($history->user_agent, 50)); ?></small>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <?php if(request()->anyFilled(['search', 'staff_id', 'status', 'start_date', 'end_date'])): ?>
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致するログイン履歴が見つかりませんでした</p>
                                            <p class="small">検索条件を変更してお試しください</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="bi bi-clock-history display-6 mb-2"></i>
                                            <p class="mb-0">ログイン履歴がありません</p>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if($loginHistories->hasPages()): ?>
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo e($loginHistories->onFirstPage() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($loginHistories->previousPageUrl()); ?>">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                <?php
                                    $current = $loginHistories->currentPage();
                                    $last = $loginHistories->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                ?>
            
                                <?php if($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($loginHistories->url(1)); ?>">1</a>
                                    </li>
                                    <?php if($start > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
            
                                <?php for($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                                        <a class="page-link" href="<?php echo e($loginHistories->url($i)); ?>"><?php echo e($i); ?></a>
                                    </li>
                                <?php endfor; ?>
            
                                <?php if($end < $last): ?>
                                    <?php if($end < $last - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($loginHistories->url($last)); ?>"><?php echo e($last); ?></a>
                                    </li>
                                <?php endif; ?>
            
                                <li class="page-item <?php echo e(!$loginHistories->hasMorePages() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($loginHistories->nextPageUrl()); ?>">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: <?php echo e($loginHistories->firstItem() ?? 0); ?> - <?php echo e($loginHistories->lastItem() ?? 0); ?> / 全 <?php echo e($loginHistories->total()); ?> 件
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/login-histories/index.blade.php ENDPATH**/ ?>