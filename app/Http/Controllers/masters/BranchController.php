<?php

namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller;
use App\Models\masters\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('branch_code', 'like', "%{$search}%")
                  ->orWhere('branch_name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('manager_name', 'like', "%{$search}%");
            });
        }
        
        $branches = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $branches->appends(['search' => $request->search]);
        }
        
        return view('masters.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('masters.branches.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'branch_code' => 'required|string|max:20|unique:branches,branch_code',
            'branch_name' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0|max:999',
        ];

        $messages = [
            'branch_code.required' => '営業所コードは必須です。',
            'branch_code.unique' => 'この営業所コードは既に使用されています。',
            'branch_code.max' => '営業所コードは20文字以内で入力してください。',
            'branch_name.required' => '営業所名は必須です。',
            'branch_name.max' => '営業所名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
            'display_order.max' => '表示順は999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === '' || $validated['display_order'] === null) {
            $validated['display_order'] = 0;
        } else {
            $validated['display_order'] = (int)$validated['display_order'];
        }

        try {
            Branch::create($validated);
            return redirect()->route('masters.branches.index')
                ->with([
                    'success' => '営業所を登録しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '登録に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function show(Branch $branch)
    {
        return view('masters.branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        return view('masters.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $rules = [
            'branch_code' => 'required|string|max:20|unique:branches,branch_code,' . $branch->id,
            'branch_name' => 'required|string|max:100',
            'postal_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:200',
            'phone_number' => 'nullable|string|max:20',
            'fax_number' => 'nullable|string|max:20',
            'manager_name' => 'nullable|string|max:50',
            'display_order' => 'nullable|integer|min:0|max:999',
        ];

        $messages = [
            'branch_code.required' => '営業所コードは必須です。',
            'branch_code.unique' => 'この営業所コードは既に使用されています。',
            'branch_code.max' => '営業所コードは20文字以内で入力してください。',
            'branch_name.required' => '営業所名は必須です。',
            'branch_name.max' => '営業所名は100文字以内で入力してください。',
            'postal_code.max' => '郵便番号は10文字以内で入力してください。',
            'address.max' => '住所は200文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'fax_number.max' => 'FAX番号は20文字以内で入力してください。',
            'manager_name.max' => '担当者名は50文字以内で入力してください。',
            'display_order.integer' => '表示順は整数で入力してください。',
            'display_order.min' => '表示順は0以上で入力してください。',
            'display_order.max' => '表示順は999以下で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        if (!isset($validated['display_order']) || $validated['display_order'] === '' || $validated['display_order'] === null) {
            $validated['display_order'] = 0;
        } else {
            $validated['display_order'] = (int)$validated['display_order'];
        }

        try {
            $branch->update($validated);
            
            return redirect()->route('masters.branches.index')
                ->with([
                    'success' => '営業所を更新しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function destroy(Branch $branch)
    {
        try {
            $branch->delete();
            
            return redirect()->route('masters.branches.index')
                ->with([
                    'success' => '営業所を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.branches.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    // public function apiIndex()
    // {
    //     $branches = Branch::orderBy('display_order', 'asc')
    //                      ->orderBy('branch_name', 'asc')
    //                      ->get(['id', 'branch_code', 'branch_name']);
        
    //     return response()->json($branches);
    // }

    // public function checkBranchCode(Request $request)
    // {
    //     $branchCode = $request->input('branch_code');
    //     $excludeId = $request->input('exclude_id');
        
    //     $query = Branch::where('branch_code', $branchCode);
        
    //     if ($excludeId) {
    //         $query->where('id', '!=', $excludeId);
    //     }
        
    //     $exists = $query->exists();
        
    //     return response()->json([
    //         'available' => !$exists,
    //         'message' => $exists ? 'この営業所コードは既に使用されています。' : 'このコードは使用できます。'
    //     ]);
    // }
}