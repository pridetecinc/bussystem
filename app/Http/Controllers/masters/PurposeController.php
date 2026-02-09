<?php

namespace App\Http\Controllers;

use App\Models\Purpose;
use Illuminate\Http\Request;

class PurposeController extends Controller
{
    public function index()
    {
        $purposes = Purpose::orderBy('display_order')->get();
        return view('masters.purposes.index', compact('purposes'));
    }

    public function create()
    {
        return view('masters.purposes.edit', ['purpose' => new Purpose()]);
    }

    public function store(Request $request)
    {
        Purpose::create($request->all());
        return redirect()->route('purposes.index')->with('success', '目的を登録しました。');
    }

    public function edit(Purpose $purpose)
    {
        return view('masters.purposes.edit', compact('purpose'));
    }

    public function update(Request $request, Purpose $purpose)
    {
        $purpose->update($request->all());
        return redirect()->route('purposes.index')->with('success', '目的を更新しました。');
    }
}