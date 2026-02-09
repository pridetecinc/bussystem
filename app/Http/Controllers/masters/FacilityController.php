<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index(Request $request)
    {
        $query = Facility::query();
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        $facilities = $query->orderBy('facility_code')->get();
        
        return view('masters.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('masters.facilities.edit', ['facility' => new Facility()]);
    }

    public function store(Request $request)
    {
        Facility::create($request->all());
        return redirect()->route('facilities.index')->with('success', '施設を登録しました。');
    }
}