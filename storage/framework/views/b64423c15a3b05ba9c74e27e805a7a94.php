<?php $__env->startSection('title', 'ガイドマスター'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="bi bi-person-video3"></i>ガイドマスター</h4>
                <a href="<?php echo e(route('masters.guides.create')); ?>" class="btn btn-primary"><i class="bi bi-plus-lg"></i> 新規追加</a>
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
                    <form method="GET" action="<?php echo e(route('masters.guides.index')); ?>" class="row g-2">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="ガイドコード・氏名で検索"
                                   value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-auto">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> 検索
                            </button>
                            <?php if(request('search')): ?>
                                <a href="<?php echo e(route('masters.guides.index')); ?>" class="btn btn-outline-secondary">
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
                    <?php if($guides->count() > 0): ?>
                        - <?php echo e($guides->total()); ?>件の結果が見つかりました
                    <?php else: ?>
                        - 該当するガイドが見つかりませんでした
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 table-striped">
                        <thead class="table-secondary">
                            <tr>
                                <th>ガイドコード</th>
                                <th>氏名</th>
                                <th>所属営業所</th>
                                <th>電話番号</th>
                                <th>雇用区分</th>
                                <th>状態</th>
                                <th width="150">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $guides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guide): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><code><?php echo e($guide->guide_code); ?></code></td>
                                <td>
                                    <div><?php echo e($guide->name); ?></div>
                                    <div class="small text-muted"><?php echo e($guide->name_kana); ?></div>
                                </td>
                                <td>
                                    <?php if($guide->branch): ?>
                                        <?php echo e($guide->branch->branch_name); ?>

                                    <?php else: ?>
                                        <span class="text-muted">未設定</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($guide->phone_number); ?></td>
                                <td>
                                    <?php
                                        $employmentLabels = [
                                            '自社' => ['label' => '自社', 'class' => 'badge bg-primary'],
                                            '契約' => ['label' => '契約', 'class' => 'badge bg-warning'],
                                            '業務委託' => ['label' => '業務委託', 'class' => 'badge bg-info'],
                                        ];
                                        $employment = $employmentLabels[$guide->employment_type] ?? ['label' => '不明', 'class' => 'badge bg-secondary'];
                                    ?>
                                    <span class="<?php echo e($employment['class']); ?>"><?php echo e($employment['label']); ?></span>
                                </td>
                                <td>
                                    <?php if($guide->is_active): ?>
                                        <span class="badge bg-success">稼働中</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">停止</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="<?php echo e(route('masters.guides.show', $guide)); ?>" 
                                           class="btn btn-sm btn-outline-info" title="詳細">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?php echo e(route('masters.guides.edit', $guide)); ?>" 
                                           class="btn btn-sm btn-outline-primary" title="編集">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        
                                        <script>
                                        function confirmDelete(name) {
                                            return confirm(`本当に「${name}」を削除しますか？\nこの操作は元に戻せません。`);
                                        }
                                        </script>
                                        <form action="<?php echo e(route('masters.guides.destroy', $guide)); ?>" method="POST" 
                                              class="d-inline" 
                                              onsubmit="return confirmDelete('<?php echo e($guide->name); ?>')">
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
                                <td colspan="7" class="text-center py-4">
                                    <?php if(request('search')): ?>
                                        <div class="text-muted">
                                            <i class="bi bi-search display-6 mb-2"></i>
                                            <p class="mb-0">検索条件に一致するガイドが見つかりませんでした</p>
                                            <p class="small">検索キーワードを変更してお試しください</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">
                                            <i class="bi bi-person-video3 display-6 mb-2"></i>
                                            <p class="mb-0">ガイドデータが登録されていません</p>
                                            <p class="small">「新規追加」ボタンから最初のガイドを登録してください</p>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <?php if($guides->hasPages()): ?>
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item <?php echo e($guides->onFirstPage() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($guides->previousPageUrl()); ?>">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
            
                                <?php
                                    $current = $guides->currentPage();
                                    $last = $guides->lastPage();
                                    $start = max(1, $current - 2);
                                    $end = min($last, $current + 2);
                                ?>
            
                                <?php if($start > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($guides->url(1)); ?>">1</a>
                                    </li>
                                    <?php if($start > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endif; ?>
            
                                <?php for($i = $start; $i <= $end; $i++): ?>
                                    <li class="page-item <?php echo e($i == $current ? 'active' : ''); ?>">
                                        <a class="page-link" href="<?php echo e($guides->url($i)); ?>"><?php echo e($i); ?></a>
                                    </li>
                                <?php endfor; ?>
            
                                <?php if($end < $last): ?>
                                    <?php if($end < $last - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?php echo e($guides->url($last)); ?>"><?php echo e($last); ?></a>
                                    </li>
                                <?php endif; ?>
            
                                <li class="page-item <?php echo e(!$guides->hasMorePages() ? 'disabled' : ''); ?>">
                                    <a class="page-link" href="<?php echo e($guides->nextPageUrl()); ?>">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            表示中: <?php echo e($guides->firstItem() ?? 0); ?> - <?php echo e($guides->lastItem() ?? 0); ?> / 全 <?php echo e($guides->total()); ?> 件
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/guides/index.blade.php ENDPATH**/ ?>