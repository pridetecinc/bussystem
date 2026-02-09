<?php

namespace App\Http\Controllers;

use App\Models\Remark;
use Illuminate\Http\Request;

class RemarkController extends Controller
{
    public function index()
    {
        $remarks = Remark::orderBy('display_order')->get();
        return view('masters.remarks.index', compact('remarks'));
    }

    public function create()
    {
        return view('masters.remarks.edit', ['remark' => new Remark()]);
    }

    public function store(Request $request)
    {
        Remark::create($request->all());
        return redirect()->route('remarks.index')->with('success', '定型備考を登録しました。');
    }

    public function edit(Remark $remark)
    {
        return view('masters.remarks.edit', compact('remark'));
    }

    public function update(Request $request, Remark $remark)
    {
        $remark->update($request->all());
        return redirect()->route('remarks.index')->with('success', '定型備考を更新しました。');
    }
}