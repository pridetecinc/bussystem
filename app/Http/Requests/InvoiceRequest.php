<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // 判断是否为更新操作：如果路由中有 {invoice} 参数，则是 update
        $isUpdate = $this->route('invoice') !== null;
        $invoiceId = $isUpdate ? $this->route('invoice')->id : null;

        return [
           // 'group_id'         => 'required|exists:groups,id',
            'invoice_number'   => [
                'required',
                'string',
                'max:50',
                Rule::unique('invoices', 'invoice_number')->when($isUpdate, fn ($rule) => $rule->ignore($invoiceId)),
            ],
            'customer_id'      => 'required|exists:customers,id',
            'invoice_date'     => 'required|date',
            'due_date'         => 'required|date|after_or_equal:invoice_date',
            'billing_title'    => 'nullable|string|max:200',
            'subtotal_amount'  => 'required|numeric|min:0',
            'tax_amount'       => 'required|numeric|min:0',
            'total_amount'     => 'required|numeric|min:0',
            'tax_mode'         => ['required', Rule::in(['EXCLUSIVE', 'INCLUSIVE'])],
            'tax_rate_default' => 'nullable|numeric|between:0,100',
            'currency_code'    => 'required|string|max:10|exists:currencies,currency_code',
            'exchange_rate'    => 'nullable|numeric|min:0',
            'language_code'    => 'required|string|max:10|exists:languages,language_code',
            'status'           => ['required', Rule::in(['DRAFT', 'ISSUED', 'PAID', 'CANCELLED'])],
            'notes'            => 'nullable|string',

            // 明细项
            'items'                     => 'required|array|min:1',
            'items.*.description'       => 'required|string|max:300',
            'items.*.quantity'          => 'required|numeric|min:0.0001',
            'items.*.unit'              => 'nullable|string|max:50',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.amount'            => 'required|numeric|min:0',
            'items.*.tax_rate'          => 'required|numeric|between:0,100',
            'items.*.tax_included'      => 'required|boolean',

            // 税率汇总（可选）
            'taxes'                     => 'nullable|array',
            'taxes.*.tax_rate'          => 'required_with:taxes|numeric|between:0,100',
            'taxes.*.subtotal'          => 'required_with:taxes|numeric|min:0',
            'taxes.*.tax_amount'        => 'required_with:taxes|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'invoice_number.required'   => '請求書番号は必須です。',
            'invoice_number.unique'     => 'この請求書番号は既に使用されています。',
            'invoice_number.max'        => '請求書番号は50文字以内で入力してください。',
            'customer_id.required'      => '顧客は必須です。',
            'customer_id.exists'        => '選択された顧客は存在しません。',
            'invoice_date.required'     => '請求日は必須です。',
            'due_date.required'         => '支払期日は必須です。',
            'due_date.after_or_equal'   => '支払期日は請求日以降にしてください。',
            'subtotal_amount.required'  => '小計金額は必須です。',
            'tax_amount.required'       => '消費税額は必須です。',
            'total_amount.required'     => '合計金額は必須です。',
            'tax_mode.required'         => '課税モードは必須です。',
            'currency_code.required'    => '通貨コードは必須です。',
            'language_code.required'    => '表示言語は必須です。',
            'status.required'           => 'ステータスは必須です。',

            // 明细项
            'items.required'                    => '明細が1件以上必要です。',
            'items.*.description.required'      => '品名・内容は必須です。',
            'items.*.quantity.required'         => '数量は必須です。',
            'items.*.unit_price.required'       => '単価は必須です。',
            'items.*.amount.required'           => '金額は必須です。',
            'items.*.tax_rate.required'         => '税率は必須です。',
            'items.*.tax_included.required'     => '税込フラグは必須です。',
        ];
    }
}