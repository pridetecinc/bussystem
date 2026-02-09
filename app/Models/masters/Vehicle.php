<?php


namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vehicle extends Model
{
    protected $fillable = [
        'branch_id', 'vehicle_code', 'registration_number', 
        'vehicle_type', 'seating_capacity', 'ownership_type', 
        'inspection_expiration_date', 'is_active', 
        'display_order', 'remarks'
    ];

    protected $dates = ['inspection_expiration_date'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    
    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
}