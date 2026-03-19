<!-- ========================================== -->
<!-- 1. 初始操作选择模态框 (Step 1) -->
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
                        <dt class="col-sm-3 text-primary fw-bold">「詳細入金」</dt>
                        <dd class="col-sm-9 mb-2">入金画面に切り替える。<br>入金額・銀行・備考などを個別設定可能。</dd>
                        <dt class="col-sm-3 text-primary fw-bold">「取消」</dt>
                        <dd class="col-sm-9">操作を中止する。</dd>
                    </dl>
                </div>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
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
<!-- 2. 详细编辑模态框 (Step 2 - 核心表单) -->
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
                <input type="hidden" name="remark" id="form-remark">
                <input type="hidden" name="customer_id" id="form-customer-id"> 

                <div class="modal-body bg-light pb-1">
                    
                    <!-- 【公共设置区】 -->
                    <div class="bg-white border rounded p-2 mb-2 shadow-sm d-flex align-items-end gap-3">
                        <!-- 1. 日期 -->
                        <div class="flex-grow-1" style="max-width: 160px;">
                            <label class="form-label mb-0 small fw-bold text-primary">入金日</label>
                            <input type="date" id="common-payment-date" class="form-control form-control-sm fw-bold" 
                                   value="{{ date('Y-m-d') }}" style="height: 31px;">
                        </div>
                        <div class="flex-grow-1" style="max-width: 200px;">
                            <label class="form-label mb-0 small fw-bold text-primary">振込銀行</label>
                            <select id="common-bank-id" name="bank_id" class="form-select form-select-sm" style="height: 31px;">
                                <option value="0">銀行を選択</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- 2. 备注 -->
                        <div class="flex-grow-1">
                            <label class="form-label mb-0 small fw-bold text-primary">備考</label>
                            <input type="text" id="common-remark" class="form-control form-control-sm" 
                                   placeholder="" style="height: 31px;">
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

                <!-- ✅ 底部统计栏：分两行布局，确保完美对齐 -->
                <div class="modal-footer bg-white border-top py-3">
                    <!-- 【第一行】统计信息：独占一行，宽度 100% -->
                    <div class="w-100 mb-2">
                        <div class="d-flex align-items-center w-100">
                            <!-- 1. 合計标签 (对应上方: 請求書番号 col-md-2) -->
                            <div class="col-md-2 col-lg-2 pe-2" style="padding-left: 0.75rem; padding-right: 0.75rem;">
                                <div class="text-muted fw-bold small text-nowrap" style="font-size: 0.875rem; line-height: 1.5;">
                                    合計 (<span id="reconcile-count-footer">0</span>件):
                                </div>
                            </div>

                            <!-- 2. 請求金額合計 (对应上方: 請求金額 col-md-2) -->
                            <div class="col-md-2 col-lg-2 text-center" style="padding-left: 0.75rem; padding-right: 0.75rem; border-right: 1px solid #dee2e6;">
                                <div class="fw-bold font-monospace text-muted" style="font-size: 0.875rem; line-height: 1.5;">
                                    <span id="footer-request-total">0</span>
                                </div>
                            </div>

                            <!-- 3. 未入金残高合計 (对应上方: 未入金残高 col-md-2) -->
                            <div class="col-md-2 col-lg-2 text-center" style="padding-left: 0.75rem; padding-right: 0.75rem; border-right: 1px solid #dee2e6;">
                                <div class="fw-bold font-monospace text-danger" style="font-size: 0.875rem; line-height: 1.5;">
                                    <span id="footer-balance-total">0</span>
                                </div>
                            </div>

                            <!-- 4. 入金額合計 (对应上方: 入金額 col-md-3) -->
                            <div class="col-md-3 col-lg-3" style="padding-left: 0.75rem; padding-right: 0.75rem;">
                                <div class="fw-bold font-monospace text-primary" style="font-size: 0.875rem; line-height: 1.5;">
                                    <span id="footer-payment-total">0</span> 
                                    <span id="footer-currency-label" class="small text-muted" style="font-size: 0.75rem;"></span>
                                </div>
                            </div>

                            <!-- 5. 占位列 (对应上方: 取引先 col-md-2) -->
                            <div class="col-md-2 col-lg-2 d-none d-md-block" style="padding-left: 0.75rem; padding-right: 0.75rem;"></div>
                        </div>
                    </div>

                    <!-- 【第二行】按钮组：独立一行，右对齐 -->
                    <div class="w-100 d-flex justify-content-end gap-2 mt-1">
                        <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">
                            キャンセル
                        </button>
                        <button type="submit" id="btn-submit-reconcile" class="btn btn-sm btn-success px-4 fw-bold">
                            <i class="bi bi-check-circle-fill"></i> 登録実行
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- 3. 隐藏表单：用于“全额入金”模式 (如有需要) -->
<!-- ========================================== -->
<form id="full-payment-form" action="{{ route('masters.payments.store') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="group_id" value="{{ request('group_id') }}">
    <input type="hidden" name="mode" value="full">
    <input type="hidden" name="payment_date" id="full-form-payment-date">
    <input type="hidden" name="remark" id="full-form-remark">
    <div id="full-payment-inputs"></div>
</form>

<!-- ========================================== -->
<!-- 4. 模板：带余额显示的明细行 -->
<!-- ========================================== -->
<template id="invoice-item-template">
    <div class="card mb-1 border-start border-3 border-primary shadow-sm invoice-item-card position-relative">
        <!-- 删除按钮 -->
        <button type="button" 
                class="btn-close position-absolute top-0 end-0 m-0 z-index-1 bg-danger bg-opacity-10" 
                aria-label="Remove"
                title="この行を削除"
                style="z-index: 10;">
        </button>

        <div class="card-body py-2">
            <div class="row g-2 align-items-center">
                <!-- 隐藏域 -->
                <input type="hidden" name="items[{index}][invoice_id]" class="item-invoice-id">
                <input type="hidden" name="items[{index}][original_amount]" class="item-original-amount">
                
                <!-- 1. 发票号 (col-md-2) -->
                <div class="col-md-2 col-lg-2">
                    <div class="small text-muted mb-0">請求書番号</div>
                    <div class="fw-bold text-dark item-invoice-no text-truncate" title=""></div>
                </div>
                
                <!-- 3. 请求金额 (col-md-2) -->
                <div class="col-md-2 col-lg-2 text-center">
                    <div class="small text-muted mb-0">請求金額</div>
                    <div class="fw-bold font-monospace item-request-amount"></div>
                </div>

                <!-- 4. 余额列 (col-md-2) -->
                <div class="col-md-2 col-lg-2 text-center">
                    <div class="small text-muted mb-0">未入金残高</div>
                    <div class="fw-bold font-monospace text-danger item-balance-amount"></div>
                </div>
                
                <!-- 5. 入金额 (col-md-3) -->
                <div class="col-md-3 col-lg-3">
                    <div class="small text-primary fw-bold mb-0">入金額</div>
                    <div class="input-group input-group-sm"> 
                        <input type="number" name="items[{index}][payment_amount]" 
                               class="form-control form-control-sm item-payment-amount fw-bold" 
                               step="0.01" min="0" required>
                        <span class="input-group-text bg-light item-currency-display"></span>
                    </div>
                </div>

                <!-- 2. 客户名 (col-md-2) - 调整顺序以匹配视觉习惯，或保持原样 -->
                <div class="col-md-2 col-lg-2  text-center">
                    <div class="small text-muted mb-0">請求先</div>
                    <div class="small text-muted item-customer-name text-truncate" title=""></div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- ========================================== -->
<!-- 5. JavaScript 逻辑 (数据对象化版本) -->
<!-- ========================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('💰 批量销账脚本已加载 (数据对象版)');

    // --- 1. 初始化变量 ---
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

    const commonDateInput = document.getElementById('common-payment-date');
    const commonRemarkInput = document.getElementById('common-remark');

    // ✅ 核心变量：存储纯净的数据对象数组
    let selectedInvoiceData = []; 

    if (!btnBulkReconcile) return;

    // --- 2. 工具函数：更新底部统计 ---
    function updateFooterStats() {
        let totalRequest = 0;
        let totalBalance = 0;
        let totalPayment = 0;
        let count = 0;
        let currentCurrency = 'JPY'; // 默认值

        document.querySelectorAll('.invoice-item-card').forEach((card, index) => {
            count++;
            
            // ✅ 获取第一行的货币作为基准（因为已知所有行货币一致）
            if (index === 0) {
                const firstCurrencySpan = card.querySelector('.item-currency-display');
                if (firstCurrencySpan) {
                    currentCurrency = firstCurrencySpan.textContent;
                }
            }

            // 计算逻辑...
            const requestVal = parseFloat(card.querySelector('.item-original-amount')?.value || '0');
            totalRequest += requestVal;

            const balanceText = card.querySelector('.item-balance-amount')?.textContent || '0';
            const balanceVal = parseFloat(balanceText.replace(/,/g, '')) || 0;
            totalBalance += balanceVal;

            const paymentInput = card.querySelector('.item-payment-amount');
            if (paymentInput && !paymentInput.disabled) {
                totalPayment += (parseFloat(paymentInput.value) || 0);
            }
        });

        // 更新数量显示
        ['reconcile-count', 'reconcile-count-footer'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.textContent = count;
        });

        // 更新金额显示
        const setTxt = (id, val) => {
            const el = document.getElementById(id);
            if(el) el.textContent = val.toLocaleString();
        };
        setTxt('footer-request-total', totalRequest);
        setTxt('footer-balance-total', totalBalance);
        setTxt('footer-payment-total', totalPayment);

        // ✅ 新增：更新底部的货币单位文字
        const footerCurrencyEl = document.getElementById('footer-currency-label');
        if (footerCurrencyEl) {
            footerCurrencyEl.textContent = currentCurrency;
        }
        
        // 按钮状态逻辑...
        const submitBtn = document.getElementById('btn-submit-reconcile');
        if (submitBtn) {
            submitBtn.disabled = (count === 0);
        }
    }

    // --- 3. 步骤 1: 点击主按钮 -> 提取数据对象 ---
    btnBulkReconcile.addEventListener('click', function () {
        const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('対象の請求書を選択してください。');
            return;
        }
        
        selectedInvoiceData = Array.from(checkedBoxes).map(cb => {
            const ds = cb.dataset; 
            return {
                id: cb.value,
                customer_id: cb.dataset.customerId || '',
                invoice_no: cb.dataset.invoiceNo || '',
                customer_name: cb.dataset.customerName || '-',
                currency_code: ds.currencyCode || '', 
                request_amount: parseFloat(cb.dataset.requestAmount) || 0,
                balance_amount: parseFloat(cb.dataset.balanceAmount) || 0
            };
        });

        // 填充公共隐藏域
        if (selectedInvoiceData.length > 0 && document.getElementById('form-customer-id')) {
            document.getElementById('form-customer-id').value = selectedInvoiceData[0].customer_id;
        }

        initialModal.show();
    });

    // --- 4. 步骤 2A: 全额入金 (如需保留) ---
    const btnFull = document.getElementById('btn-action-full');
    if(btnFull) {
        btnFull.addEventListener('click', function() {
            if(selectedInvoiceData.length === 0) return;
            
            fullPaymentFormInputs.innerHTML = '';
            document.getElementById('full-form-payment-date').value = commonDateInput.value;
            document.getElementById('full-form-remark').value = commonRemarkInput.value || '';

            selectedInvoiceData.forEach((item, index) => {
                const createInput = (name, val) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = name; inp.value = val;
                    fullPaymentFormInputs.appendChild(inp);
                };
                createInput(`items[${index}][invoice_id]`, item.id);
                createInput(`items[${index}][payment_amount]`, item.balance_amount.toFixed(2));
            });
            
            initialModal.hide();
            document.getElementById('full-payment-form').submit();
        });
    }

    // --- 5. 步骤 2B: 详细入金 -> 渲染列表 ---
    const btnDetail = document.getElementById('btn-action-detail');
    if(btnDetail && detailContainer && detailTemplate) {
        btnDetail.addEventListener('click', function() {
            if(selectedInvoiceData.length === 0) return;

            initialModal.hide();
            detailContainer.innerHTML = '';
            
            selectedInvoiceData.forEach((item, index) => {
                const clone = document.importNode(detailTemplate.content, true);
                
                // 填充数据
                clone.querySelector('.item-invoice-id').value = item.id;
                clone.querySelector('.item-original-amount').value = item.request_amount;
                clone.querySelector('.item-invoice-no').textContent = item.invoice_no;
                clone.querySelector('.item-customer-name').textContent = item.customer_name;
                clone.querySelector('.item-request-amount').textContent = item.request_amount.toLocaleString();

                const currencySpan = clone.querySelector('.item-currency-display');
                if(currencySpan) {
                    currencySpan.textContent = item.currency_code;
                }
                
                const balanceEl = clone.querySelector('.item-balance-amount');
                const amountInput = clone.querySelector('.item-payment-amount');
                
                // 逻辑判断
                if (item.balance_amount < 0) {
                    balanceEl.textContent = item.balance_amount.toLocaleString();
                    amountInput.value = "0.00";
                    amountInput.disabled = true;
                    amountInput.classList.add('bg-danger', 'text-white');
                    amountInput.setAttribute('data-max-amount', "0"); 
                } else if (item.balance_amount > 0.005) {
                    balanceEl.textContent = item.balance_amount.toLocaleString();
                    amountInput.value = item.balance_amount.toFixed(2);
                    amountInput.disabled = false;
                    amountInput.setAttribute('data-max-amount', item.balance_amount);
                    
                    amountInput.addEventListener('input', function() {
                        const val = parseFloat(this.value) || 0;
                        const max = parseFloat(this.getAttribute('data-max-amount')) || 0;
                        this.classList.toggle('is-invalid', val > max + 0.001);
                        updateFooterStats(); 
                    });
                } else {
                    balanceEl.textContent = "0";
                    balanceEl.className = 'fw-bold font-monospace text-secondary item-balance-amount';
                    amountInput.value = "0.00";
                    amountInput.disabled = true;
                    amountInput.classList.add('bg-light', 'text-muted');
                    amountInput.setAttribute('data-max-amount', "0");
                }

                // 替换 name 中的 {index}
                clone.querySelectorAll('input').forEach(el => {
                    const name = el.getAttribute('name');
                    if(name) el.setAttribute('name', name.replace('{index}', index));
                });

                // 绑定删除
                clone.querySelector('.btn-close').addEventListener('click', function() {
                    this.closest('.invoice-item-card').remove();
                    updateFooterStats();
                });

                detailContainer.appendChild(clone);
            });

            updateFooterStats();
            detailModal.show();
        });
    }

    // --- 6. 提交校验 ---
    const btnSubmit = document.getElementById('btn-submit-reconcile');
    const detailForm = document.getElementById('detail-form');
    
    if (btnSubmit && detailForm) {
        btnSubmit.addEventListener('click', function(e) {
            e.preventDefault();
            
            // 同步公共字段
            document.getElementById('form-payment-date').value = commonDateInput.value;
            document.getElementById('form-remark').value = commonRemarkInput.value || '';

            const paymentInputs = detailContainer.querySelectorAll('.item-payment-amount');
            let hasError = false;
            let firstErrorInput = null;
            let errorMsg = '';

            paymentInputs.forEach(input => {
                const max = parseFloat(input.getAttribute('data-max-amount')) || 0;
                const val = parseFloat(input.value) || 0;

                if (max <= 0.005) {
                    hasError = true; firstErrorInput = input; errorMsg = '残高 0 の項目が含まれています。';
                } else if (val > max + 0.001) {
                    hasError = true; if(!firstErrorInput) firstErrorInput = input; errorMsg = '入金額が残高を超えています。';
                } else if (val < 0.005) {
                    hasError = true; if(!firstErrorInput) firstErrorInput = input; errorMsg = '残高があるのに入力が 0 です。';
                }

                if(hasError && firstErrorInput) {
                    firstErrorInput.classList.add('is-invalid', 'border-danger');
                    firstErrorInput.style.borderColor = 'red';
                } else {
                    input.classList.remove('is-invalid', 'border-danger');
                    input.style.borderColor = '';
                }
            });

            if (hasError) {
                firstErrorInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorInput.focus();
                alert('⚠️ エラー：\n' + errorMsg);
                return;
            }

            detailForm.submit();
        });
    }
});

// --- 7. 单条销账兼容逻辑 ---
document.querySelectorAll('.btn-single-reconcile').forEach(btn => {
    btn.addEventListener('click', function() {
        const targetId = this.dataset.id;
        const targetCheckbox = document.querySelector(`.invoice-checkbox[value="${targetId}"]`);
        if (!targetCheckbox) return;

        document.querySelectorAll('.invoice-checkbox:checked').forEach(cb => cb.checked = false);
        targetCheckbox.checked = true;
        
        setTimeout(() => {
            document.getElementById('btn-bulk-reconcile')?.click();
        }, 50);
    });
});
</script>