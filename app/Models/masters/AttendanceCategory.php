<?php

namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class AttendanceCategory extends Model
{
    protected $fillable = ['attendance_code', 'attendance_name', 'is_work_day', 'color_code', 'display_order'];
}