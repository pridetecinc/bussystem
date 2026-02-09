<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCategory;
use Illuminate\Http\Request;

class AttendanceCategoryController extends Controller
{
    public function index()
    {
        $categories = AttendanceCategory::orderBy('display_order')->get();
        return view('masters.attendance_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('masters.attendance_categories.edit', ['category' => new AttendanceCategory()]);
    }

    public function store(Request $request)
    {
        AttendanceCategory::create($request->all());
        return redirect()->route('attendance_categories.index')->with('success', '勤怠分類を登録しました。');
    }

    public function edit(AttendanceCategory $attendance_category)
    {
        return view('masters.attendance_categories.edit', ['category' => $attendance_category]);
    }

    public function update(Request $request, AttendanceCategory $attendance_category)
    {
        $attendance_category->update($request->all());
        return redirect()->route('attendance_categories.index')->with('success', '勤怠分類を更新しました。');
    }
}