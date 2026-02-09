<?php


namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller;
use App\Models\masters\Vehicle;
use App\Models\masters\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;


class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $query = Vehicle::query()->with('branch');
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('vehicle_code', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_type', 'like', "%{$search}%")
                  ->orWhereHas('branch', function ($branchQuery) use ($search) {
                      $branchQuery->where('branch_name', 'like', "%{$search}%")
                                 ->orWhere('branch_code', 'like', "%{$search}%");
                  });
            });
        }
        
        $vehicles = $query->orderBy('display_order')->paginate(20);
        
        if ($request->has('search')) {
            $vehicles->appends(['search' => $request->search]);
        }
        
        return view('masters.vehicles.index', compact('vehicles'));
    }
    
    public function create(): View
    {
        $branches = Branch::orderBy('branch_name')->get();
        return view('masters.vehicles.create', compact('branches'));
    }
    
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'vehicle_code' => 'required|unique:vehicles|max:50',
            'registration_number' => 'required|unique:vehicles|max:20',
            'vehicle_type' => 'required|max:50',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'ownership_type' => 'required|in:company,rental,personal',
            'inspection_expiration_date' => 'required|date',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
        ], [
            'branch_id.required' => '所属営業所を選択してください',
            'vehicle_code.required' => '車両コードを入力してください',
            'vehicle_code.unique' => 'この車両コードは既に登録されています',
            'registration_number.required' => '登録番号を入力してください',
            'registration_number.unique' => 'この登録番号は既に登録されています',
            'vehicle_type.required' => '車種を入力してください',
            'seating_capacity.required' => '乗車定員を入力してください',
            'ownership_type.required' => '所有形態を選択してください',
            'inspection_expiration_date.required' => '車検満了日を入力してください',
            'is_active.required' => 'ステータスを選択してください',
            'remarks.max' => '備考は500文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ]);

        if (!isset($validated['display_order']) || $validated['display_order'] === null) {
            $maxOrder = Vehicle::max('display_order');
            $validated['display_order'] = ($maxOrder !== null) ? $maxOrder + 1 : 1;
        }

        Vehicle::create($validated);

        return redirect()->route('masters.vehicles.index')
            ->with('success', '車両が登録されました。');
    }
    
    public function show(Vehicle $vehicle): View
    {
        return view('masters.vehicles.show', compact('vehicle'));
    }
    
    public function edit(Vehicle $vehicle): View
    {
        $branches = Branch::orderBy('branch_name')->get();
        return view('masters.vehicles.edit', compact('vehicle', 'branches'));
    }
    
    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'vehicle_code' => 'required|max:50|unique:vehicles,vehicle_code,' . $vehicle->id,
            'registration_number' => 'required|max:20|unique:vehicles,registration_number,' . $vehicle->id,
            'vehicle_type' => 'required|max:50',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'ownership_type' => 'required|in:company,rental,personal',
            'inspection_expiration_date' => 'required|date',
            'is_active' => 'required|boolean',
            'remarks' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
        ], [
            'branch_id.required' => '所属営業所を選択してください',
            'vehicle_code.required' => '車両コードを入力してください',
            'vehicle_code.unique' => 'この車両コードは既に登録されています',
            'registration_number.required' => '登録番号を入力してください',
            'registration_number.unique' => 'この登録番号は既に登録されています',
            'vehicle_type.required' => '車種を入力してください',
            'seating_capacity.required' => '乗車定員を入力してください',
            'ownership_type.required' => '所有形態を選択してください',
            'inspection_expiration_date.required' => '車検満了日を入力してください',
            'is_active.required' => 'ステータスを選択してください',
            'remarks.max' => '備考は500文字以内で入力してください。',
            'display_order.integer' => '表示順序は数値で入力してください。',
            'display_order.min' => '表示順序は0以上の数値で入力してください。',
        ]);

        $vehicle->update($validated);

        return redirect()->route('masters.vehicles.index')
            ->with('success', '車両情報が更新されました。');
    }
    
    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();

        return redirect()->route('masters.vehicles.index')
            ->with('success', '車両が削除されました。');
    }
}