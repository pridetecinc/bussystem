<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::orderBy('display_order')->orderBy('location_code')->get();
        return view('masters.locations.index', compact('locations'));
    }

    public function create()
    {
        return view('masters.locations.edit', ['location' => new Location()]);
    }

    public function store(Request $request)
    {
        Location::create($request->all());
        return redirect()->route('locations.index')->with('success', '地名を登録しました。');
    }

    public function edit(Location $location)
    {
        return view('masters.locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $location->update($request->all());
        return redirect()->route('locations.index')->with('success', '地名を更新しました。');
    }
}