<?php

namespace App\Http\Controllers;

use App\Models\Itinerary;
use App\Models\ItineraryDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItineraryController extends Controller
{
    public function index()
    {
        $itineraries = Itinerary::withCount('details')->get();
        return view('masters.itineraries.index', compact('itineraries'));
    }

    public function create()
    {
        return view('masters.itineraries.edit', ['itinerary' => new Itinerary()]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $itinerary = Itinerary::create($request->only(['itinerary_code', 'itinerary_name', 'category', 'remarks']));
            
            // 行程詳細の保存
            foreach ($request->details as $index => $detail) {
                $itinerary->details()->create([
                    'display_order' => $index + 1,
                    'location_name' => $detail['location_name'],
                    'arrival_time'  => $detail['arrival_time'],
                    'departure_time'=> $detail['departure_time'],
                ]);
            }
        });

        return redirect()->route('itineraries.index')->with('success', '行程マスターを登録しました。');
    }
}