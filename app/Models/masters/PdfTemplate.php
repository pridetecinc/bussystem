<?php

namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class PdfTemplate extends Model
{
    protected $fillable = [
        'template_name', 'language_code', 'template_file', 'sort'
    ];
        
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}