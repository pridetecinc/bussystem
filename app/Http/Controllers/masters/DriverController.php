<?php


namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller;
use App\Models\masters\Driver;
use App\Models\masters\Branch;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index(Request $request)
    {
        $query = Driver::with('branch');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_kana', 'like', "%{$search}%")
                  ->orWhere('driver_code', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('license_type', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('branch_id') && $request->branch_id != '') {
            $query->where('branch_id', $request->branch_id);
        }
        
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }
        
        $drivers = $query->orderBy('display_order')->orderBy('driver_code')->paginate(20);
        
        $branches = Branch::orderBy('branch_code')->get(['id', 'branch_code', 'branch_name']);
        
        if ($request->has('search')) {
            $drivers->appends(['search' => $request->search]);
        }
        if ($request->has('branch_id')) {
            $drivers->appends(['branch_id' => $request->branch_id]);
        }
        if ($request->has('is_active')) {
            $drivers->appends(['is_active' => $request->is_active]);
        }
        if ($request->has('license_expiring')) {
            $drivers->appends(['license_expiring' => $request->license_expiring]);
        }
        
        return view('masters.drivers.index', compact('drivers', 'branches'));
    }

    public function create()
    {
        $branches = Branch::orderBy('branch_code')->get(['id', 'branch_code', 'branch_name']);
        return view('masters.drivers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'driver_code' => 'required|string|max:20|unique:drivers,driver_code',
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'license_type' => 'required|string|max:50',
            'license_expiration_date' => 'required|date|after:today',
            'email' => 'nullable|email|max:100',
            'display_order' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        $messages = [
            'branch_id.required' => '支店は必須です。',
            'branch_id.exists' => '選択された支店は有効ではありません。',
            'driver_code.required' => 'ドライバーコードは必須です。',
            'driver_code.unique' => 'このドライバーコードは既に使用されています。',
            'driver_code.max' => 'ドライバーコードは20文字以内で入力してください。',
            'name.required' => '氏名は必須です。',
            'name.max' => '氏名は100文字以内で入力してください。',
            'name_kana.max' => '氏名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'birth_date.date' => '生年月日は有効な日付を入力してください。',
            'hire_date.required' => '入社日は必須です。',
            'hire_date.date' => '入社日は有効な日付を入力してください。',
            'license_type.required' => '免許種類は必須です。',
            'license_type.max' => '免許種類は50文字以内で入力してください。',
            'license_expiration_date.required' => '免許有効期限は必須です。',
            'license_expiration_date.date' => '免許有効期限は有効な日付を入力してください。',
            'license_expiration_date.after' => '免許有効期限は今日以降の日付を入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Driver::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        try {
            Driver::create($validated);
            return redirect()->route('masters.drivers.index')
                ->with([
                    'success' => 'ドライバーを登録しました。',
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

    public function show(Driver $driver)
    {
        $driver->load('branch');
        return view('masters.drivers.show', compact('driver'));
    }

    public function edit(Driver $driver)
    {
        $branches = Branch::orderBy('branch_code')->get(['id', 'branch_code', 'branch_name']);
        return view('masters.drivers.edit', compact('driver', 'branches'));
    }

    public function update(Request $request, Driver $driver)
    {
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'driver_code' => 'required|string|max:20|unique:drivers,driver_code,' . $driver->id,
            'name' => 'required|string|max:100',
            'name_kana' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date',
            'hire_date' => 'required|date',
            'license_type' => 'required|string|max:50',
            'license_expiration_date' => 'required|date',
            'email' => 'nullable|email|max:100',
            'display_order' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ];

        $messages = [
            'branch_id.required' => '支店は必須です。',
            'branch_id.exists' => '選択された支店は有効ではありません。',
            'driver_code.required' => 'ドライバーコードは必須です。',
            'driver_code.unique' => 'このドライバーコードは既に使用されています。',
            'driver_code.max' => 'ドライバーコードは20文字以内で入力してください。',
            'name.required' => '氏名は必須です。',
            'name.max' => '氏名は100文字以内で入力してください。',
            'name_kana.max' => '氏名（カナ）は100文字以内で入力してください。',
            'phone_number.max' => '電話番号は20文字以内で入力してください。',
            'birth_date.date' => '生年月日は有効な日付を入力してください。',
            'hire_date.required' => '入社日は必須です。',
            'hire_date.date' => '入社日は有効な日付を入力してください。',
            'license_type.required' => '免許種類は必須です。',
            'license_type.max' => '免許種類は50文字以内で入力してください。',
            'license_expiration_date.required' => '免許有効期限は必須です。',
            'license_expiration_date.date' => '免許有効期限は有効な日付を入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.max' => 'メールアドレスは100文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);
        
        $validated['is_active'] = $request->has('is_active') ? true : false;

        try {
            $driver->update($validated);
            
            return redirect()->route('masters.drivers.index')
                ->with([
                    'success' => 'ドライバーを更新しました。',
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

    public function destroy(Driver $driver)
    {
        try {
            $driver->delete();
            
            return redirect()->route('masters.drivers.index')
                ->with([
                    'success' => 'ドライバーを削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.drivers.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }

    // public function apiIndex(Request $request)
    // {
    //     $query = Driver::query();
        
    //     if ($request->has('active_only') && $request->active_only) {
    //         $query->where('is_active', true);
    //     }
        
    //     if ($request->has('branch_id') && $request->branch_id != '') {
    //         $query->where('branch_id', $request->branch_id);
    //     }
        
    //     $drivers = $query->orderBy('driver_code')
    //                      ->get(['id', 'driver_code', 'name', 'branch_id', 'license_type']);
        
    //     return response()->json($drivers);
    // }

    // public function statistics()
    // {
    //     $total = Driver::count();
    //     $active = Driver::where('is_active', true)->count();
    //     $inactive = Driver::where('is_active', false)->count();
        
    //     $expiringSoon = Driver::where('license_expiration_date', '<=', now()->addDays(30))
    //                          ->where('license_expiration_date', '>=', now())
    //                          ->where('is_active', true)
    //                          ->count();
        
    //     $expired = Driver::where('license_expiration_date', '<', now())
    //                     ->where('is_active', true)
    //                     ->count();
        
    //     return response()->json([
    //         'total' => $total,
    //         'active' => $active,
    //         'inactive' => $inactive,
    //         'expiring_soon' => $expiringSoon,
    //         'expired' => $expired,
    //     ]);
    // }
}