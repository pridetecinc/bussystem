<?php
namespace App\Models\masters;

use Illuminate\Database\Eloquent\Model;

class Itinerary extends Model
{
    protected $fillable = ['itinerary_code', 'itinerary_name', 'category', 'remarks'];
}