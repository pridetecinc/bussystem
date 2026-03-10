<?php

namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $table = 'invoices';

    protected $fillable = [
        'group_id',
        'invoice_number',
        'customer_id',
        'invoice_date',
        'due_date',
        'billing_title',
        'subtotal_amount',
        'tax_amount',
        'total_amount',
        'tax_mode',
        'tax_rate_default',
        'currency_code',
        'exchange_rate',
        'language_code',
        'pdf_template_id',
        'pdf_file_path',
        'status',
        'notes',
        // created_at / updated_at 由 DB 自动处理，不列入 fillable
    ];

    // 关联：客户
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // 关联：明细项
    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    // 关联：税率汇总
    public function taxSummaries()
    {
        return $this->hasMany(InvoiceTaxSummary::class, 'invoice_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}