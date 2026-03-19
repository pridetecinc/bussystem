<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
// 如果 Customer 和 Bank 模型存在，请取消下面两行的注释并修正命名空间
// use App\Models\masters\Customer; 
// use App\Models\masters\Bank; 

class PaymentHeader extends Model
{
    use SoftDeletes;

    protected $table = 'payment_headers';

    /**
     * 可批量赋值的字段
     * 注意：id, created_at, updated_at, deleted_at, is_deleted 由框架或数据库自动处理，通常不放入 fillable
     */
    protected $fillable = [
        'group_id',
        'customer_id',
        'currency_code',
        'total_amount',
        'bank_id',
        'payment_date',
        'staff_id',
        'remark',
        'batch_token',
        'created_by',
        'notes',
    ];

    /**
     * 需要转换为原生类型的字段
     */
    protected $casts = [
        'id' => 'integer',
        'group_id' => 'integer',
        'customer_id' => 'integer',
        'bank_id' => 'integer',
        'total_amount' => 'decimal:2',
        'payment_date' => 'date',
        'is_deleted' => 'boolean',
        'deleted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 关联：所属客户
     * 注意：由于没有外键约束，这纯粹是逻辑关联，需确保数据一致性由代码保证
     */
    public function customer()
    {
        // 请根据实际 Customer 模型的路径调整
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function Staff()
    {
        // 请根据实际 Customer 模型的路径调整
        return $this->belongsTo(Staff::class, 'staff_id');
    }


    /**
     * 关联：入金明细列表 (一对多)
     */
    public function details(): HasMany
    {
        // 请根据实际 PaymentDetail 模型的路径调整
        return $this->hasMany(PaymentDetail::class, 'payment_header_id', 'id');
    }
    
    /**
     * 作用域：仅查询未删除的数据
     * SoftDeletes trait 会自动处理 deleted_at IS NULL
     * 如果你的业务逻辑强依赖 is_deleted 字段，可以添加此 Scope
     */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }
}