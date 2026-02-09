<?php

namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class Remark extends Model
{
    protected $fillable = ['remark_code', 'title', 'content', 'category', 'display_order'];
}