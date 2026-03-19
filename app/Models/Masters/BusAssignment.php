<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

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

    /**
     * モデルの「起動」メソッド
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->key_uuid)) {
                $model->key_uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'group_info_id',
        'vehicle_id',
        'driver_id',
        'guide_id',
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
        'vehicle_number',
        'step_car',
        'adult_count',
        'child_count',
        'guide_count',
        'other_count',
        'luggage_count',
        'vehicle_type_spec_check',
        'temporary_driver',
        'representative',
        'representative_phone',
        'attention',
        'operation_remarks',
        'operation_memo',
        'operation_basic_remarks',
        'doc_remarks',
        'history_remarks',
        'vehicle_index',
        'ignore_operation',
        'ignore_driver',
    ];

    /**
     * 日付として扱う属性
     */
    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    /**
     * 型キャスト
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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
        'vehicle_type_spec_check' => 'boolean',
        'temporary_driver' => 'boolean',
        'adult_count' => 'integer',
        'child_count' => 'integer',
        'guide_count' => 'integer',
        'other_count' => 'integer',
        'luggage_count' => 'integer',
        'ignore_operation' => 'boolean',
        'ignore_driver' => 'boolean',
    ];

    /* === リレーション ============================================= */

    /**
     * グループ情報
     */
    public function groupInfo(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'group_info_id', 'id');
    }

    /**
     * 車両
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    /**
     * 運転手
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    /**
     * ガイド
     */
    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class, 'guide_id', 'id');
    }

    /**
     * 日次行程（複数）
     */
    public function dailyItineraries(): HasMany
    {
        return $this->hasMany(DailyItinerary::class, 'bus_assignment_id', 'id')
                    ->orderBy('date', 'asc')
                    ->orderBy('time_start', 'asc');
    }

    /* === アクセサ ================================================ */

    /**
     * 状態表示
     */
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

    /**
     * 運行期間表示（一覧画面用）
     */
    public function getPeriodDisplayAttribute(): string
    {
        $start = $this->start_date
            ? (is_string($this->start_date) ? date('m/d', strtotime($this->start_date)) : $this->start_date->format('m/d'))
            : '';
        $end = $this->end_date
            ? (is_string($this->end_date) ? date('m/d', strtotime($this->end_date)) : $this->end_date->format('m/d'))
            : '';

        if ($start && $end) {
            return $start . ' ' . ($this->start_time ? substr($this->start_time, 0, 5) : '') . "\n" . $end . ' ' . ($this->end_time ? substr($this->end_time, 0, 5) : '');
        } elseif ($start) {
            return $start . ' ' . ($this->start_time ? substr($this->start_time, 0, 5) : '') . "\n---";
        } elseif ($end) {
            return "---\n" . $end . ' ' . ($this->end_time ? substr($this->end_time, 0, 5) : '');
        }
        return "---\n---";
    }

    /**
     * 車両表示（車種指定アイコン付き）
     */
    public function getVehicleDisplayAttribute(): string
    {
        if ($this->vehicle) {
            $modelName = $this->vehicle->vehicleModel ? $this->vehicle->vehicleModel->model_name : '';
            $icon = $this->vehicle_type_spec_check ? '⭐ ' : '';
            return $icon . $this->vehicle->registration_number . ($modelName ? "\n" . $modelName : '');
        }
        return $this->vehicle_number ? '号車 ' . $this->vehicle_number : '---';
    }

    /**
     * 運転手表示（仮マーク付き）
     */
    public function getDriverDisplayAttribute(): string
    {
        return $this->driver?->name ?? '---';
    }

    /**
     * 予約ID/運行ID表示
     */
    public function getIdDisplayAttribute(): string
    {
        $reservationId = substr($this->key_uuid ?? $this->id, 0, 8);
        return $reservationId . "\n" . '運行:' . $this->id;
    }

    /**
     * 開始時刻/開始場所（最初の日次行程から）
     */
    public function getStartInfoAttribute(): string
    {
        $firstDay = $this->dailyItineraries->first();
        if ($firstDay) {
            $date = $firstDay->date instanceof \Carbon\Carbon 
                ? $firstDay->date->format('m/d') 
                : date('m/d', strtotime($firstDay->date));
            $time = $firstDay->time_start ? substr($firstDay->time_start, 0, 5) : '';
            return $date . ' ' . $time . "\n" . ($firstDay->start_location ?? '---');
        }
        
        // 日次行程がない場合
        $startDate = $this->start_date
            ? (is_string($this->start_date) ? date('m/d', strtotime($this->start_date)) : $this->start_date->format('m/d'))
            : '---';
        $startTime = $this->start_time ? substr($this->start_time, 0, 5) : '';
        return $startDate . ' ' . $startTime . "\n---";
    }

    /**
     * 団体名/ステッカー表示
     */
    public function getGroupStickerAttribute(): string
    {
        $groupName = $this->groupInfo?->group_name ?? '---';
        $sticker = $this->step_car ?? '---';
        return $groupName . "\n" . $sticker;
    }

    /**
     * 代理店名/国籍表示
     */
    public function getAgencyCountryAttribute(): string
    {
        $agency = $this->groupInfo?->agency ?? '---';
        $country = $this->groupInfo?->agency_country ?? '---';
        return $agency . "\n" . $country;
    }

    /**
     * 業務分類/行程名表示
     */
    public function getBusinessItineraryAttribute(): string
    {
        $business = $this->groupInfo?->business_category ?? '---';
        $itinerary = $this->groupInfo?->itinerary_name ?? '---';
        return $business . "\n" . $itinerary;
    }

    /**
     * 予約状況表示
     */
    public function getReservationStatusDisplayAttribute(): string
    {
        return $this->groupInfo?->reservation_status ?? '---';
    }

    /**
     * 請求額/未納額表示（仮）
     */
    public function getBillingDisplayAttribute(): string
    {
        return "--\n--";
    }

    /**
     * 立替表示（仮）
     */
    public function getAdvanceDisplayAttribute(): string
    {
        return '--';
    }

    /**
     * フォーマット済み車輛インデックス
     */
    public function getFormattedVehicleIndexAttribute(): string
    {
        return sprintf('%02d', $this->vehicle_index ?? 1);
    }

    /**
     * 備考配列（基本/DOC/履歴）
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
     * 合計人数
     */
    public function getTotalPassengersAttribute(): int
    {
        return ($this->adult_count ?? 0) + 
               ($this->child_count ?? 0) + 
               ($this->guide_count ?? 0) + 
               ($this->other_count ?? 0);
    }

    /* === スコープ ================================================= */

    /**
     * 車輛インデックス順
     */
    public function scopeOrderByVehicleIndex($query)
    {
        return $query->orderBy('vehicle_index', 'asc');
    }

    /**
     * 特定グループの運行割当
     */
    public function scopeForGroup($query, $groupInfoId)
    {
        return $query->where('group_info_id', $groupInfoId)
                     ->orderBy('vehicle_index', 'asc');
    }
}