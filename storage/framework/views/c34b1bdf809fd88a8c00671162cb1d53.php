<?php $__env->startSection('title', '車両編集'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('masters.home')); ?>">ホーム</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo e(route('masters.vehicles.index')); ?>">車両管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">車両編集</li>
                </ol>
            </nav>
            
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle"></i> <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            <?php endif; ?>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading">
                        <i class="bi bi-exclamation-triangle"></i> 入力エラーがあります
                    </h5>
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="閉じる"></button>
                </div>
            <?php endif; ?>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-car-front"></i> 車両編集
                    </h5>
                </div>
                
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('masters.vehicles.update', $vehicle)); ?>" id="vehicleForm">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="branch_id" class="form-label required">所属営業所</label>
                                <select name="branch_id" id="branch_id" 
                                        class="form-select <?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">選択してください</option>
                                    <?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($branch->id); ?>" 
                                            <?php echo e(old('branch_id', $vehicle->branch_id) == $branch->id ? 'selected' : ''); ?>>
                                            <?php echo e($branch->branch_code); ?> - <?php echo e($branch->branch_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vehicle_code" class="form-label required">車両コード</label>
                                <input type="text" name="vehicle_code" id="vehicle_code" 
                                       class="form-control <?php $__errorArgs = ['vehicle_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('vehicle_code', $vehicle->vehicle_code)); ?>" 
                                       required maxlength="20" placeholder="例: V001">
                                <?php $__errorArgs = ['vehicle_code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">※ 20文字以内、他と重複不可</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="vehicle_type_id" class="form-label required">車両種類</label>
                                <select name="vehicle_type_id" id="vehicle_type_id" 
                                        class="form-select <?php $__errorArgs = ['vehicle_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">選択してください</option>
                                    <?php $__currentLoopData = $vehicleTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($type->id); ?>" 
                                            <?php echo e(old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : ''); ?>

                                            data-models='<?php echo json_encode($type->models, 15, 512) ?>'>
                                            <?php echo e($type->type_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <?php $__errorArgs = ['vehicle_type_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_model_id" class="form-label required">モデル</label>
                                <select name="vehicle_model_id" id="vehicle_model_id" 
                                        class="form-select <?php $__errorArgs = ['vehicle_model_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">先に車両種類を選択してください</option>
                                </select>
                                <?php $__errorArgs = ['vehicle_model_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="registration_number" class="form-label required">登録番号</label>
                                <input type="text" name="registration_number" id="registration_number" 
                                       class="form-control <?php $__errorArgs = ['registration_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('registration_number', $vehicle->registration_number)); ?>" 
                                       required maxlength="20" placeholder="例: 品川300あ1234">
                                <?php $__errorArgs = ['registration_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="seating_capacity" class="form-label required">乗車定員</label>
                                <div class="input-group">
                                    <input type="number" name="seating_capacity" id="seating_capacity" 
                                           class="form-control <?php $__errorArgs = ['seating_capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           value="<?php echo e(old('seating_capacity', $vehicle->seating_capacity)); ?>" 
                                           required min="1" max="100">
                                    <span class="input-group-text">名</span>
                                </div>
                                <?php $__errorArgs = ['seating_capacity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="ownership_type" class="form-label required">所有形態</label>
                                <select name="ownership_type" id="ownership_type" 
                                        class="form-select <?php $__errorArgs = ['ownership_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                    <option value="">選択してください</option>
                                    <option value="company" <?php echo e(old('ownership_type', $vehicle->ownership_type) == 'company' ? 'selected' : ''); ?>>会社所有</option>
                                    <option value="rental" <?php echo e(old('ownership_type', $vehicle->ownership_type) == 'rental' ? 'selected' : ''); ?>>レンタル</option>
                                    <option value="personal" <?php echo e(old('ownership_type', $vehicle->ownership_type) == 'personal' ? 'selected' : ''); ?>>個人所有</option>
                                </select>
                                <?php $__errorArgs = ['ownership_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="inspection_expiration_date" class="form-label required">車検満了日</label>
                                <input type="date" name="inspection_expiration_date" id="inspection_expiration_date" 
                                       class="form-control <?php $__errorArgs = ['inspection_expiration_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       value="<?php echo e(old('inspection_expiration_date', $vehicle->inspection_expiration_date)); ?>" required>
                                <?php $__errorArgs = ['inspection_expiration_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="display_order" class="form-label">表示順序</label>
                                <input type="number" class="form-control <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                       id="display_order" name="display_order" 
                                       value="<?php echo e(old('display_order', $vehicle->display_order)); ?>" 
                                       min="0" placeholder="例: 10">
                                <?php $__errorArgs = ['display_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">※ 数値を入力</small>
                            </div>
                        
                            <div class="col-md-12 mb-3">
                                <label for="remarks" class="form-label">備考</label>
                                <textarea class="form-control <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                          id="remarks" name="remarks" 
                                          rows="3" maxlength="500"><?php echo e(old('remarks', $vehicle->remarks)); ?></textarea>
                                <?php $__errorArgs = ['remarks'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="invalid-feedback"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted">※ 500文字以内</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required">ステータス</label>
                                <div class="mt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_active" id="is_active_true" 
                                               value="1" <?php echo e(old('is_active', $vehicle->is_active) == 1 ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_active_true">有効</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="is_active" id="is_active_false" 
                                               value="0" <?php echo e(old('is_active', $vehicle->is_active) == 0 ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="is_active_false">無効</label>
                                    </div>
                                </div>
                                <?php $__errorArgs = ['is_active'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="text-danger small mt-1"><?php echo e($message); ?></div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <small class="form-text text-muted d-block">
                                    ※ 無効にするとこの車両は選択できなくなります
                                </small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> 更新する
                                </button>
                                <a href="<?php echo e(route('masters.vehicles.index')); ?>" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> キャンセル
                                </a>
                            </div>
                            
                            <div>
                                <a href="<?php echo e(route('masters.vehicles.show', $vehicle)); ?>" class="btn btn-info">
                                    <i class="bi bi-eye"></i> 詳細を見る
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
.required::after {
    content: " *";
    color: #dc3545;
}
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const vehicleTypeSelect = document.getElementById('vehicle_type_id');
    const vehicleModelSelect = document.getElementById('vehicle_model_id');
    const currentModelId = <?php echo e(old('vehicle_model_id', $vehicle->vehicle_model_id ?? 'null')); ?>;
    
    function updateModelDropdown(selectedTypeId = null) {
        const selectedOption = vehicleTypeSelect.options[vehicleTypeSelect.selectedIndex];
        vehicleModelSelect.innerHTML = '';
        
        if (!selectedOption || selectedOption.value === '') {
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '先に車両種類を選択してください';
            vehicleModelSelect.appendChild(defaultOption);
            vehicleModelSelect.disabled = true;
            return;
        }
        
        let models = [];
        try {
            const modelsData = selectedOption.getAttribute('data-models');
            if (modelsData) {
                models = JSON.parse(modelsData);
            }
        } catch (e) {
            console.error('モデルデータの解析に失敗しました:', e);
        }
        
        if (models.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'モデルが登録されていません';
            vehicleModelSelect.appendChild(option);
            vehicleModelSelect.disabled = true;
            return;
        }
        
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'モデルを選択してください';
        vehicleModelSelect.appendChild(defaultOption);
        
        models.sort((a, b) => (a.display_order || 0) - (b.display_order || 0));
        
        models.forEach(model => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.model_name + (model.maker ? ` (${model.maker})` : '');
            
            if (model.remarks) {
                option.title = model.remarks;
            }
            
            vehicleModelSelect.appendChild(option);
        });
        
        vehicleModelSelect.disabled = false;
        
        if (currentModelId && selectedTypeId) {
            const modelExists = models.some(model => model.id == currentModelId);
            if (modelExists) {
                vehicleModelSelect.value = currentModelId;
            }
        }
    }
    
    vehicleTypeSelect.addEventListener('change', function() {
        updateModelDropdown(this.value);
    });
    
    if (vehicleTypeSelect.value) {
        updateModelDropdown(vehicleTypeSelect.value);
    } else {
        vehicleModelSelect.disabled = true;
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /www/wwwroot/default/resources/views/masters/vehicles/edit.blade.php ENDPATH**/ ?>