<?php

namespace App\Http\Controllers\masters;

use App\Http\Controllers\Controller;
use App\Models\masters\Itinerary;
use Illuminate\Http\Request;

class ItineraryController extends Controller
{
    public function index(Request $request)
    {
        $query = Itinerary::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('itinerary_code', 'like', "%{$search}%")
                  ->orWhere('itinerary_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('remarks', 'like', "%{$search}%");
            });
        }
        
        $itineraries = $query->orderBy('itinerary_code')->paginate(20);
        
        if ($request->has('search')) {
            $itineraries->appends(['search' => $request->search]);
        }
        
        return view('masters.itineraries.index', compact('itineraries'));
    }

    public function create()
    {
        return view('masters.itineraries.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'itinerary_code' => 'required|string|max:20|unique:itineraries,itinerary_code',
            'itinerary_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'itinerary_code.required' => '旅程コードは必須です。',
            'itinerary_code.unique' => 'この旅程コードは既に使用されています。',
            'itinerary_code.max' => '旅程コードは20文字以内で入力してください。',
            'itinerary_name.required' => '旅程名は必須です。',
            'itinerary_name.max' => '旅程名は100文字以内で入力してください。',
            'category.max' => 'カテゴリーは50文字以内で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            Itinerary::create($validated);
            return redirect()->route('masters.itineraries.index')
                ->with([
                    'success' => '旅程を登録しました。',
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

    public function show(Itinerary $itinerary)
    {
        return view('masters.itineraries.show', compact('itinerary'));
    }

    public function edit(Itinerary $itinerary)
    {
        return view('masters.itineraries.edit', compact('itinerary'));
    }

    public function update(Request $request, Itinerary $itinerary)
    {
        $rules = [
            'itinerary_code' => 'required|string|max:20|unique:itineraries,itinerary_code,' . $itinerary->id,
            'itinerary_name' => 'required|string|max:100',
            'category' => 'nullable|string|max:50',
            'remarks' => 'nullable|string|max:500',
        ];

        $messages = [
            'itinerary_code.required' => '旅程コードは必須です。',
            'itinerary_code.unique' => 'この旅程コードは既に使用されています。',
            'itinerary_code.max' => '旅程コードは20文字以内で入力してください。',
            'itinerary_name.required' => '旅程名は必須です。',
            'itinerary_name.max' => '旅程名は100文字以内で入力してください。',
            'category.max' => 'カテゴリーは50文字以内で入力してください。',
            'remarks.max' => '備考は500文字以内で入力してください。',
        ];

        $validated = $request->validate($rules, $messages);

        try {
            $itinerary->update($validated);
            
            return redirect()->route('masters.itineraries.index')
                ->with([
                    'success' => '旅程を更新しました。',
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

    public function destroy(Itinerary $itinerary)
    {
        try {
            $itinerary->delete();
            
            return redirect()->route('masters.itineraries.index')
                ->with([
                    'success' => '旅程を削除しました。',
                    'alert-type' => 'success'
                ]);
                
        } catch (\Exception $e) {
            return redirect()->route('masters.itineraries.index')
                ->with([
                    'error' => '削除に失敗しました。システムエラーが発生しました。',
                    'alert-type' => 'danger'
                ]);
        }
    }
}