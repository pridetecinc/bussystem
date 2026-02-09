<?php

namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [
        'location_code', 'location_name', 'location_kana', 
        'prefecture', 'area_type', 'display_order'
    ];
}