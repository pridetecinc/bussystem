{{-- 
    组件：批量销账双步确认模态框 (带余额显示版)
    功能：
    1. 双步确认：先选模式（全额/详细），再操作。
    2. 公共设置：日期、银行、备注全局统一。
    3. 余额展示：明细行增加“未入金残高”列，直观展示待收金额。
--}}

<!-- ========================================== -->
<!-- 1. 触发按钮 -->
<!-- ========================================== -->
<button type="button" class="btn btn-primary" id="btn-bulk-reconcile">
    <i class="bi bi-cash-coin"></i> 批量销账 / 入金
</button>

<!-- ========================================== -->
<!-- 2. 初始选择模态框 (Step 1) -->
<!-- ========================================== -->
<div class="modal fade" id="initialActionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-cash-coin me-2"></i>入金操作選択</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-3 fs-5 fw-bold">以下の操作を選んでください。</p>
                <div class="alert alert-light border mb-4 p-3">
                    <dl class="row mb-0 small">
                        <!-- <dt class="col-sm-3 text-primary fw-bold">「全額入金」</dt>
                        <dd class="col-sm-9 mb-2">未入金が全額入金される。<br>入金日＝本日、金額＝請求額。</dd> -->
                        <dt class="col-sm-3 text-primary fw-bold">「詳細入金」</dt>
                        <dd class="col-sm-9 mb-2">入金画面に切り替える。<br>入金額・銀行・備考などを個別設定可能。</dd>
                        <dt class="col-sm-3 text-primary fw-bold">「取消」</dt>
                        <dd class="col-sm-9">操作を中止する。</dd>
                    </dl>
                </div>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <!-- <button type="button" class="btn btn-success btn-lg px-5 flex-grow-1" style="min-width: 180px;" id="btn-action-full">
                        <i class="bi bi-check-circle-fill me-2"></i>全額入金
                    </button> -->
                    <button type="button" class="btn btn-primary btn-lg px-5 flex-grow-1" style="min-width: 180px;" id="btn-action-detail">
                        <i class="bi bi-pencil-square me-2"></i>入金
                    </button>
                    <button type="button" class="btn btn-secondary btn-lg px-5 flex-grow-1" style="min-width: 180px;" data-bs-dismiss="modal">
                        取消
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. 详细编辑模态框 (Step 2 - 核心表单) -->
<!-- ========================================== -->
<div class="modal fade" id="bulkReconcileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl"> 
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h5 class="modal-title fs-6"><i class="bi bi-list-check me-2"></i>個別消し込み処理 (詳細)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="{{ route('masters.payments.store') }}" method="POST" id="detail-form">
                @csrf
                <input type="hidden" name="group_id" value="{{ request('group_id') }}">
                <input type="hidden" name="mode" value="detail">
                
                <!-- 【关键】顶层公共隐藏域 (提交时由 JS 填充) -->
                <input type="hidden" name="payment_date" id="form-payment-date">
                <input type="hidden" name="bank_id" id="form-bank-id">
                <input type="hidden" name="remark" id="form-remark">
                <input type="hidden" name="customer_id" id="form-customer-id"> 

                <div class="modal-body bg-light pb-1">
                    
                    <!-- 【公共设置区】 -->
                    <div class="bg-white border rounded p-2 mb-2 shadow-sm d-flex align-items-end gap-3">
                        <!-- 1. 日期 -->
                        <div class="flex-grow-1" style="max-width: 160px;">
                            <label class="form-label mb-0 small fw-bold text-primary">共通：入金日</label>
                            <input type="date" id="common-payment-date" class="form-control form-control-sm fw-bold" 
                                   value="{{ date('Y-m-d') }}" style="height: 31px;">
                        </div>
                        <!-- 2. 银行 -->
                        <div class="flex-grow-1" style="max-width: 160px;">
                            <label class="form-label mb-0 small fw-bold text-primary">共通：銀行</label>
                            <select id="common-bank-id" class="form-select form-select-sm fw-bold" style="height: 31px;" required>
                                <option value="">選択...</option>
                                <option value="1">三菱 UFJ</option>
                                <option value="2">三井住友</option>
                                <option value="3">みずほ</option>
                            </select>
                        </div>
                        <!-- 3. 备注 -->
                        <div class="flex-grow-1">
                            <label class="form-label mb-0 small fw-bold text-primary">共通：備考</label>
                            <input type="text" id="common-remark" class="form-control form-control-sm" 
                                   placeholder="例：3 月分一括入金" style="height: 31px;">
                        </div>
                        <div class="text-muted small mb-1 d-none d-md-block">
                            <i class="bi bi-info-circle"></i> 自動反映
                        </div>
                    </div>

                    <!-- 统计提示 -->
                    <div class="alert alert-warning d-flex align-items-center py-2 mb-2 small shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
                        <div>
                            選択された <strong id="reconcile-count" class="fw-bold">0</strong> 件。
                            <span class="text-dark">入金額のみ個別入力してください。</span>
                        </div>
                    </div>

                    <!-- 动态列表容器 -->
                    <div id="reconcile-items-container" class="space-y-2" style="max-height: 60vh; overflow-y: auto; padding-right: 5px;">
                        <!-- JS 将在此处插入卡片 -->
                    </div>
                </div>

                <div class="modal-footer bg-white border-top py-2">
                    <div class="me-auto text-muted small">
                        合計：<span id="modal-grand-total" class="fw-bold text-dark">0</span> JPY
                    </div>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    
                    <!-- 【修改点 1】添加 id="btn-submit-reconcile" -->
                    <!-- 【修改点 2】移除 onclick="return syncFinalValues()"，改用 JS 监听 -->
                    <button type="submit" id="btn-submit-reconcile" class="btn btn-sm btn-success px-3 fw-bold">
                        <i class="bi bi-check-circle-fill"></i> 登録実行
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 4. 隐藏表单：用于“全额入金”模式 -->
<!-- ========================================== -->
<form id="full-payment-form" action="{{ route('masters.payments.store') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
    <input type="hidden" name="mode" value="full">
    <input type="hidden" name="payment_date" id="full-form-payment-date">
    <input type="hidden" name="bank_id" id="full-form-bank-id">
    <input type="hidden" name="remark" id="full-form-remark">
    
    <div id="full-payment-inputs"></div>
</form>

<!-- ========================================== -->
<!-- 5. 模板：带余额显示的明细行 -->
<!-- ========================================== -->
<template id="invoice-item-template">
    <div class="card mb-1 border-start border-3 border-primary shadow-sm invoice-item-card">
        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <!-- 【仅保留】明细特有隐藏域 -->
                <input type="hidden" name="items[{index}][invoice_id]" class="item-invoice-id">
                <input type="hidden" name="items[{index}][original_amount]" class="item-original-amount">
                
                <!-- 1. 发票号 -->
                <div class="col-md-2 col-lg-2">
                    <div class="small text-muted mb-0">請求書番号</div>
                    <div class="fw-bold text-dark item-invoice-no text-truncate" title=""></div>
                </div>
                
                <!-- 2. 客户名 -->
                <div class="col-md-3 col-lg-3">
                    <div class="small text-muted mb-0">取引先</div>
                    <div class="small text-muted item-customer-name text-truncate" title=""></div>
                </div>
                
                <!-- 3. 请求金额 -->
                <div class="col-md-2 col-lg-2 text-center">
                    <div class="small text-muted mb-0">請求金額</div>
                    <div class="fw-bold font-monospace item-request-amount"></div>
                </div>

                <!-- 【新增】4. 余额列 (未入金残高) -->
                <div class="col-md-2 col-lg-2 text-center">
                    <div class="small text-muted mb-0">未入金残高</div>
                    <div class="fw-bold font-monospace text-danger item-balance-amount"></div>
                </div>
                
                <!-- 5. 入金额 (唯一输入项) -->
                <div class="col-md-3 col-lg-3">
                    <div class="small text-primary fw-bold mb-0">入金額</div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light">JPY</span>
                        <input type="number" name="items[{index}][payment_amount]" 
                               class="form-control form-control-sm item-payment-amount fw-bold" 
                               step="0.01" min="0" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- ========================================== -->
<!-- 6. JavaScript 逻辑 -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('💰 批量销账脚本已加载');

    const btnBulkReconcile = document.getElementById('btn-bulk-reconcile');
    const initialModalEl = document.getElementById('initialActionModal');
    const detailModalEl = document.getElementById('bulkReconcileModal');
    
    if (!initialModalEl || !window.bootstrap) {
        console.error('❌ Bootstrap 未加载或模态框元素缺失');
        return;
    }
    
    const initialModal = new bootstrap.Modal(initialModalEl);
    const detailModal = detailModalEl ? new bootstrap.Modal(detailModalEl) : null;

    const detailContainer = document.getElementById('reconcile-items-container');
    const detailTemplate = document.getElementById('invoice-item-template');
    const fullPaymentFormInputs = document.getElementById('full-payment-inputs');

    // 公共控件
    const commonDateInput = document.getElementById('common-payment-date');
    const commonBankSelect = document.getElementById('common-bank-id');
    const commonRemarkInput = document.getElementById('common-remark');

    if (!btnBulkReconcile) return;

    // --- 步骤 1: 点击主按钮 ---
    btnBulkReconcile.addEventListener('click', function () {
        const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('対象の請求書を選択してください。');
            return;
        }
        window.selectedInvoices = Array.from(checkedBoxes).map(cb => cb.closest('tr'));
        
        const firstRow = window.selectedInvoices[0];
        const customerId = firstRow.querySelector('.invoice-checkbox')?.dataset?.customerId || ''; 
        if(customerId && document.getElementById('form-customer-id')) {
            document.getElementById('form-customer-id').value = customerId;
        }

        initialModal.show();
    });

    // --- 步骤 2A: 全额入金逻辑 ---
    const btnFull = document.getElementById('btn-action-full');
    if(btnFull) {
        btnFull.addEventListener('click', function() {
            if(!window.selectedInvoices || window.selectedInvoices.length === 0) return;
            
            fullPaymentFormInputs.innerHTML = '';
            const defaultDate = commonDateInput.value;
            const defaultBank = commonBankSelect.value;
            const defaultRemark = commonRemarkInput ? commonRemarkInput.value : '';

            document.getElementById('full-form-payment-date').value = defaultDate;
            document.getElementById('full-form-bank-id').value = defaultBank;
            document.getElementById('full-form-remark').value = defaultRemark;

            window.selectedInvoices.forEach((row, index) => {
                try {
                    const id = row.querySelector('.invoice-checkbox').value;
                    const amountStr = row.cells[8]?.innerText.replace(/,/g, '').trim() || '0';
                    
                    const createInput = (name, val) => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden'; inp.name = name; inp.value = val;
                        fullPaymentFormInputs.appendChild(inp);
                    };
                    
                    createInput(`items[${index}][invoice_id]`, id);
                    createInput(`items[${index}][payment_amount]`, amountStr);
                } catch (err) {
                    console.error('全额入金行处理错误:', err);
                }
            });
            
            initialModal.hide();
            console.log('🚀 提交全额入金表单...');
            document.getElementById('full-payment-form').submit();
        });
    }

    // --- 步骤 2B: 详细入金逻辑 ---
    const btnDetail = document.getElementById('btn-action-detail');
    if(btnDetail && detailContainer && detailTemplate) {
        btnDetail.addEventListener('click', function() {
            if(!window.selectedInvoices || window.selectedInvoices.length === 0) return;

            initialModal.hide();
            detailContainer.innerHTML = '';
            let grandTotal = 0;

            window.selectedInvoices.forEach((row, index) => {
                try {
                    const checkbox = row.querySelector('.invoice-checkbox');
                    const id = checkbox.value;
                    
                    const invoiceNo   = row.cells[3]?.innerText.trim() || '';
                    const customerName= row.cells[4]?.innerText.trim() || '不明';
                    
                    const amountColIndex = 8; 
                    const amountStr = row.cells[amountColIndex]?.innerText.replace(/,/g, '').trim() || '0';
                    const requestAmount = parseFloat(amountStr) || 0;

                    const balanceColIndex = 9; 
                    let balanceStr = row.cells[balanceColIndex]?.innerText.replace(/,/g, '').trim() || '0';
                    const balanceAmount = parseFloat(balanceStr) || 0;

                    grandTotal += (balanceAmount > 0 ? balanceAmount : 0); 

                    const clone = document.importNode(detailTemplate.content, true);
                    
                    clone.querySelector('.item-invoice-id').value = id;
                    clone.querySelector('.item-original-amount').value = requestAmount;
                    clone.querySelector('.item-invoice-no').textContent = invoiceNo;
                    clone.querySelector('.item-customer-name').textContent = customerName;
                    clone.querySelector('.item-request-amount').textContent = requestAmount.toLocaleString();
                    
                    const balanceEl = clone.querySelector('.item-balance-amount');
                    const amountInput = clone.querySelector('.item-payment-amount');

                    // 【关键修复】处理负数余额
                    if (balanceAmount < 0) {
                        balanceEl.textContent = balanceAmount.toLocaleString();
                        balanceEl.className = 'fw-bold font-monospace text-danger item-balance-amount';
                        amountInput.value = "0.00";
                        amountInput.disabled = true;
                        amountInput.classList.add('bg-danger', 'text-white');
                        amountInput.title = "データ異常：過入金状態";
                        // 负数不计入 data-max 校验，因为不允许输入
                        amountInput.setAttribute('data-max-amount', "0"); 
                    } else if (balanceAmount > 0.005) {
                        balanceEl.textContent = balanceAmount.toLocaleString();
                        balanceEl.className = 'fw-bold font-monospace text-danger item-balance-amount';
                        amountInput.value = balanceAmount.toFixed(2);
                        amountInput.disabled = false;
                        amountInput.classList.remove('bg-danger', 'text-white');
                        amountInput.setAttribute('data-max-amount', balanceAmount);
                        
                        amountInput.addEventListener('input', function() {
                            const val = parseFloat(this.value) || 0;
                            const max = parseFloat(this.getAttribute('data-max-amount')) || 0;
                            if (val > max + 0.001) {
                                this.classList.add('is-invalid');
                            } else {
                                this.classList.remove('is-invalid');
                            }
                            recalculateTotal();
                        });
                    } else {
                        balanceEl.textContent = "0";
                        balanceEl.className = 'fw-bold font-monospace text-secondary item-balance-amount';
                        amountInput.value = "0.00";
                        amountInput.disabled = true;
                        amountInput.classList.add('bg-light', 'text-muted');
                        amountInput.setAttribute('data-max-amount', "0");
                    }

                    const inputs = clone.querySelectorAll('input, select');
                    inputs.forEach(el => {
                        const nameAttr = el.getAttribute('name');
                        if(nameAttr) el.setAttribute('name', nameAttr.replace('{index}', index));
                    });

                    detailContainer.appendChild(clone);
                } catch (err) {
                    console.error('详细入金行处理错误:', err, row);
                }
            });

            document.getElementById('reconcile-count').textContent = window.selectedInvoices.length;
            document.getElementById('modal-grand-total').textContent = grandTotal.toLocaleString();
            detailModal.show();
        });
    }

    function recalculateTotal() {
        let total = 0;
        document.querySelectorAll('.item-payment-amount').forEach(input => {
            if (!input.disabled) {
                const val = parseFloat(input.value);
                if (!isNaN(val)) total += val;
            }
        });
        document.getElementById('modal-grand-total').textContent = total.toLocaleString();
    }

    // --- 【核心修复】提交按钮逻辑 (含余额为 0 校验) ---
    const btnSubmit = document.getElementById('btn-submit-reconcile');
    const detailForm = document.getElementById('detail-form');
    
    if (btnSubmit && detailForm) {
        btnSubmit.addEventListener('click', function(e) {
            // 1. 【绝对优先】阻止默认提交行为
            e.preventDefault();
            e.stopPropagation();
            console.log('🛑 默认提交已拦截，开始严格校验...');

            try {
                // 2. 同步公共字段
                const pDate = document.getElementById('common-payment-date').value;
                const pBank = document.getElementById('common-bank-id').value;
                const pRemark = document.getElementById('common-remark').value;

                document.getElementById('form-payment-date').value = pDate;
                document.getElementById('form-bank-id').value = pBank;
                document.getElementById('form-remark').value = pRemark;

                if (!pBank) {
                    alert('⚠️ エラー：銀行を選択してください。');
                    commonBankSelect.focus();
                    return false;
                }

                // 3. 严格校验逻辑
                const paymentInputs = detailContainer.querySelectorAll('.item-payment-amount');
                let hasError = false;
                let firstErrorInput = null;
                let errorMessage = '';
                let errorType = ''; // 用于区分错误类型

                paymentInputs.forEach(input => {
                    // 获取该行的原始余额 (data-max-amount)
                    const maxAllowed = parseFloat(input.getAttribute('data-max-amount')) || 0;
                    const enteredValue = parseFloat(input.value) || 0;

                    // 【规则 A】核心需求：如果余额为 0，直接报错 (不允许混入列表)
                    // 即使输入框被 disabled 了，只要它在列表里且余额为 0，就是用户的选择不当
                    if (maxAllowed <= 0.005) {
                        hasError = true;
                        firstErrorInput = input;
                        errorType = 'ZERO_BALANCE';
                        // 视觉标记：给输入框或其父容器加红色边框
                        input.classList.add('is-invalid', 'border-danger');
                        input.style.borderColor = 'red';
                        // 如果是 disabled 的，可能需要标记其所在的 card
                        if(input.disabled) {
                            input.closest('.card')?.classList.add('border-danger', 'bg-danger', 'bg-opacity-10');
                        }
                        return; // 发现一个错误即可，后续可继续遍历标记所有错误，但这里先记录第一个
                    }

                    // 【规则 B】输入金额不能超过余额
                    if (enteredValue > maxAllowed + 0.001) {
                        hasError = true;
                        if (!firstErrorInput) { // 保留第一个错误（通常是余额为0的那个）
                            firstErrorInput = input;
                            errorType = 'OVER_AMOUNT';
                        }
                        input.classList.add('is-invalid', 'border-danger');
                        input.style.borderColor = 'red';
                    } 
                    // 【规则 C】余额>0 时，输入金额必须 > 0 (防止选了但不付钱)
                    else if (maxAllowed > 0.005 && enteredValue < 0.005) {
                         hasError = true;
                         if (!firstErrorInput) {
                            firstErrorInput = input;
                            errorType = 'ZERO_INPUT';
                         }
                         input.classList.add('is-invalid', 'border-danger');
                         input.style.borderColor = 'red';
                    }
                    else {
                        // 校验通过，清除样式
                        input.classList.remove('is-invalid', 'border-danger');
                        input.style.borderColor = '';
                        input.closest('.card')?.classList.remove('border-danger', 'bg-danger', 'bg-opacity-10');
                    }
                });

                // 4. 处理错误反馈
                if (hasError) {
                    if (firstErrorInput) {
                        firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstErrorInput.focus();
                    }
                    
                    // 根据错误类型显示不同的日文提示
                    if (errorType === 'ZERO_BALANCE') {
                        errorMessage = 'エラー：未入金残高が 0 の請求書が含まれています。\n\n残高がある請求書のみを選択し直してください。';
                    } else if (errorType === 'OVER_AMOUNT') {
                        errorMessage = 'エラー：入金額が「未入金残高」を超えている行があります。';
                    } else if (errorType === 'ZERO_INPUT') {
                        errorMessage = 'エラー：残高があるのに入金額が 0 の行があります。';
                    } else {
                        errorMessage = '入力内容を確認してください。';
                    }

                    alert('⚠️ 登録できません！\n\n' + errorMessage);
                    return false;
                }

                // 5. 校验通过，手动提交
                console.log('✅ 严格校验通过，正在提交表单...');
                detailForm.submit(); 

            } catch (error) {
                console.error('💥 提交过程中发生严重错误:', error);
                alert('システムエラーが発生しました。コンソールログを確認してください。');
            }
        });
    }

});
document.querySelectorAll('.btn-single-reconcile').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.dataset.id;
        
        // 1. 找到对应的复选框
        const targetCheckbox = document.querySelector(`.invoice-checkbox[value="${targetId}"]`);
        
        if (!targetCheckbox) {
            console.error('未找到对应的复选框:', targetId);
            return;
        }

        // 2. 清除当前所有其他选中项 (确保只处理这一条)
        document.querySelectorAll('.invoice-checkbox:checked').forEach(cb => {
            if (cb.value !== targetId) {
                cb.checked = false;
            }
        });

        // 3. 勾选目标项
        targetCheckbox.checked = true;

        // 4. 手动触发 change 事件，更新全选框状态和工具栏显示
        targetCheckbox.dispatchEvent(new Event('change'));
        
        // 此时 updateBulkActionBar 应该已经运行，工具栏已显示，且计数为 1

        // 5. 延迟一小会儿 (确保 UI 更新完成)，然后触发批量销账按钮的点击
        setTimeout(() => {
            const bulkReconcileBtn = document.getElementById('btn-bulk-reconcile');
            if (bulkReconcileBtn) {
                // 触发点击事件，直接走原有的批量逻辑
                bulkReconcileBtn.click(); 
            } else {
                alert('系统错误：找不到批量销账按钮 (btn-bulk-reconcile)');
            }
        }, 50); // 50ms 延迟足够让 UI 刷新
    });
});
</script>