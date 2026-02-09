<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fees = Fee::orderBy('display_order')->get();
        return view('masters.fees.index', compact('fees'));
    }

    public function create()
    {
        return view('masters.fees.edit', ['fee' => new Fee()]);
    }

    public function store(Request $request)
    {
        Fee::create($request->all());
        return redirect()->route('fees.index')->with('success', '料金項目を登録しました。');
    }

    public function edit(Fee $fee)
    {
        return view('masters.fees.edit', compact('fee'));
    }

    public function update(Request $request, Fee $fee)
    {
        $fee->update($request->all());
        return redirect()->route('fees.index')->with('success', '料金項目を更新しました。');
    }
}