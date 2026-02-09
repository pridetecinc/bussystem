<?php

namespace App\Http\Controllers;

use App\Models\ReservationCategory;
use Illuminate\Http\Request;

class ReservationCategoryController extends Controller
{
    public function index()
    {
        $categories = ReservationCategory::orderBy('display_order')->get();
        return view('masters.reservation_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('masters.reservation_categories.edit', ['category' => new ReservationCategory()]);
    }

    public function store(Request $request)
    {
        ReservationCategory::create($request->all());
        return redirect()->route('reservation_categories.index')->with('success', '予約分類を登録しました。');
    }

    public function edit(ReservationCategory $reservation_category)
    {
        return view('masters.reservation_categories.edit', ['category' => $reservation_category]);
    }

    public function update(Request $request, ReservationCategory $reservation_category)
    {
        $reservation_category->update($request->all());
        return redirect()->route('reservation_categories.index')->with('success', '予約分類を更新しました。');
    }
}