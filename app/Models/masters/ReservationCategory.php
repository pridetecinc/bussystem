<?php

namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class ReservationCategory extends Model
{
    protected $fillable = ['category_code', 'category_name', 'color_code', 'display_order', 'is_active'];
}