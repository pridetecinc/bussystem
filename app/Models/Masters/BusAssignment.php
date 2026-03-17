<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusAssignment extends Model
{
    use HasFactory;

    protected $table = 'bus_assignment';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'key_uuid',
        'yoyaku_uuid',
        'group_id',
        'daily_itinerary_id',
        'vehicle_id',
        'driver_id',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'lock_arrangement',
        'status_sent',
        'status_finalized',
        'count_daily',
        'created_by',
        'updated_by',
        // 新增字段 - 車輛運行詳細信息
        'vehicle_number',           // 号車
        'step_car',                 // ステップカー情報
        'adult_count',              // 大人人数（車輛別）
        'child_count',              // 小人人数（車輛別）
        'guide_count',              // ガイド人数（車輛別）
        'other_count',              // その他人数（車輛別）
        'luggage_count',            // 荷物数（車輛別）
        'vehicle_type_spec_check',  // 車種指定チェック
        'temporary_driver',         // 仮ドライバーチェック
        'accompanying',             // 添乗者
        'representative',           // 代表者名
        'representative_phone',     // 代表者電話番号
        'attention',                // 注意
        'operation_remarks',        // 備考（指示書表示用）
        'operation_memo',           // 手配メモ
        'operation_basic_remarks',  // 基本タブ備考
        'doc_remarks',              // DOCタブ備考
        'history_remarks',          // 履歴タブ備考
        'vehicle_index',            // 車輛索引（01,02,03...）
    ];

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
        'lock_arrangement' => 'boolean',
        'status_sent' => 'boolean',
        'status_finalized' => 'boolean',
        'count_daily' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        // 新增字段类型转换
        'vehicle_type_spec_check' => 'boolean',
        'temporary_driver' => 'boolean',
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'guide_count' => 'integer',
        'other_count' => 'integer',
        'luggage_count' => 'integer',
    ];

    public function groupInfo(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'yoyaku_uuid', 'key_uuid');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function dailyItineraries(): HasMany
    {
        return $this->hasMany(DailyItinerary::class, 'bus_ass_uuid', 'key_uuid');
    }

    public function dailyItinerary(): BelongsTo
    {
        return $this->belongsTo(DailyItinerary::class, 'daily_itinerary_id', 'id');
    }

    public function getStatusDisplayAttribute(): string
    {
        if ($this->status_finalized) {
            return '最終確定';
        } elseif ($this->status_sent) {
            return '送信済';
        } elseif ($this->lock_arrangement) {
            return 'ロック中';
        }
        return '未確定';
    }

    public function getPeriodDisplayAttribute(): string
    {
        $start = $this->start_date ? $this->start_date->format('Y/m/d') : '';
        $end = $this->end_date ? $this->end_date->format('Y/m/d') : '';
        
        if ($start && $end) {
            return $start . ' 〜 ' . $end;
        } elseif ($start) {
            return $start . ' 〜';
        } elseif ($end) {
            return '〜 ' . $end;
        }
        return '';
    }

    /**
     * 获取完整车辆显示名称
     */
    public function getVehicleDisplayAttribute(): string
    {
        if ($this->vehicle) {
            return $this->vehicle->registration_number . 
                   ($this->vehicle->vehicleModel ? ' (' . $this->vehicle->vehicleModel->model_name . ')' : '');
        }
        return $this->vehicle_number ? '号車 ' . $this->vehicle_number : '未設定';
    }

    /**
     * 获取格式化后的车辆索引
     */
    public function getFormattedVehicleIndexAttribute(): string
    {
        return sprintf('%02d', $this->vehicle_index ?? 1);
    }

    /**
     * 获取所有备注信息的数组
     */
    public function getRemarksArrayAttribute(): array
    {
        return [
            'basic' => $this->operation_basic_remarks,
            'doc' => $this->doc_remarks,
            'history' => $this->history_remarks,
        ];
    }

    /**
     * 获取人数总计
     */
    public function getTotalPassengersAttribute(): int
    {
        return ($this->adult_count ?? 0) + 
               ($this->child_count ?? 0) + 
               ($this->guide_count ?? 0) + 
               ($this->other_count ?? 0);
    }

    /**
     * 范围查询：按车辆索引排序
     */
    public function scopeOrderByVehicleIndex($query)
    {
        return $query->orderBy('vehicle_index', 'asc');
    }

    /**
     * 范围查询：获取指定团体的所有车辆分配
     */
    public function scopeForGroup($query, $yoyakuUuid)
    {
        return $query->where('yoyaku_uuid', $yoyakuUuid)
                     ->orderBy('vehicle_index', 'asc');
    }
}