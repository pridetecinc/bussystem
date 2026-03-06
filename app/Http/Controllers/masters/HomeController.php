<?php

namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller; // これが必要な場合があります

class HomeController extends Controller
{
    public function index()
    {
        return view("home");
    }
}