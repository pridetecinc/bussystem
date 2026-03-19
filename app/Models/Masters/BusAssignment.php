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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->key_uuid)) {
                $model->key_uuid = (string) Str::uuid();
            }
        });
    }

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

    protected $dates = [
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

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

    public function groupInfo(): BelongsTo
    {
        return $this->belongsTo(GroupInfo::class, 'group_info_id', 'id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id', 'id');
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class, 'guide_id', 'id');
    }

    public function dailyItineraries(): HasMany
    {
        return $this->hasMany(DailyItinerary::class, 'bus_assignment_id', 'id');
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
        $start = $this->start_date
            ? (is_string($this->start_date) ? date('Y/m/d', strtotime($this->start_date)) : $this->start_date->format('Y/m/d'))
            : '';
        $end = $this->end_date
            ? (is_string($this->end_date) ? date('Y/m/d', strtotime($this->end_date)) : $this->end_date->format('Y/m/d'))
            : '';

        if ($start && $end) {
            return $start . ' 〜 ' . $end;
        } elseif ($start) {
            return $start . ' 〜';
        } elseif ($end) {
            return '〜 ' . $end;
        }
        return '';
    }

    public function getVehicleDisplayAttribute(): string
    {
        if ($this->vehicle) {
            return $this->vehicle->registration_number . 
                   ($this->vehicle->vehicleModel ? ' (' . $this->vehicle->vehicleModel->model_name . ')' : '');
        }
        return $this->vehicle_number ? '号車 ' . $this->vehicle_number : '未設定';
    }

    public function getFormattedVehicleIndexAttribute(): string
    {
        return sprintf('%02d', $this->vehicle_index ?? 1);
    }

    public function getRemarksArrayAttribute(): array
    {
        return [
            'basic' => $this->operation_basic_remarks,
            'doc' => $this->doc_remarks,
            'history' => $this->history_remarks,
        ];
    }

    public function getTotalPassengersAttribute(): int
    {
        return ($this->adult_count ?? 0) + 
               ($this->child_count ?? 0) + 
               ($this->guide_count ?? 0) + 
               ($this->other_count ?? 0);
    }

    public function scopeOrderByVehicleIndex($query)
    {
        return $query->orderBy('vehicle_index', 'asc');
    }

    public function scopeForGroup($query, $groupInfoId)
    {
        return $query->where('group_info_id', $groupInfoId)
                     ->orderBy('vehicle_index', 'asc');
    }
}