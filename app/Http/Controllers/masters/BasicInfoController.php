<?php

namespace App\Http\Controllers;

use App\Models\BasicInfo;
use Illuminate\Http\Request;

class BasicInfoController extends Controller
{
    public function index()
    {
        $info = BasicInfo::first() ?: new BasicInfo();
        return view('masters.basic_info', compact('info'));
    }

    public function update(Request $request)
    {
        $info = BasicInfo::first() ?: new BasicInfo();
        $info->fill($request->all());
        $info->save();

        return redirect()->back()->with('success', '基本情報を更新しました。');
    }
}