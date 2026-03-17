<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BusAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = BusAssignment::with(['groupInfo', 'vehicle', 'driver']);
        
        if ($request->filled('yoyaku_uuid')) {
            $query->where('yoyaku_uuid', 'like', '%' . $request->yoyaku_uuid . '%');
        }
        
        if ($request->filled('start_date_from')) {
            $query->where('start_date', '>=', $request->start_date_from);
        }
        
        if ($request->filled('start_date_to')) {
            $query->where('start_date', '<=', $request->start_date_to);
        }
        
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->driver_id);
        }
        
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'finalized':
                    $query->where('status_finalized', 1);
                    break;
                case 'sent':
                    $query->where('status_sent', 1)->where('status_finalized', 0);
                    break;
                case 'locked':
                    $query->where('lock_arrangement', 1)->where('status_sent', 0)->where('status_finalized', 0);
                    break;
                case 'draft':
                    $query->where('lock_arrangement', 0)->where('status_sent', 0)->where('status_finalized', 0);
                    break;
            }
        }
        
        $busAssignments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $busAssignments
            ]);
        }
        
        return view('masters.bus-assignments.index', compact('busAssignments'));
    }

    public function create(Request $request)
    {
        $groupInfo = null;
        if ($request->filled('group_id')) {
            $groupInfo = GroupInfo::find($request->group_id);
        }
        
        $vehicles = Vehicle::with('vehicleModel', 'branch')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
        $drivers = Driver::with('branch')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();
        $groupInfos = GroupInfo::orderBy('start_date', 'desc')->get();
        $dailyItineraries = collect();
        
        return view('masters.bus-assignments.create', compact('groupInfo', 'vehicles', 'drivers', 'groupInfos', 'dailyItineraries'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'yoyaku_uuid' => 'required|string|max:36',
                'daily_itinerary_id' => 'nullable|exists:daily_itinerary,id',
                'vehicle_id' => 'nullable|integer|exists:vehicles,id',
                'driver_id' => 'nullable|integer|exists:drivers,id',
                'start_date' => 'required|date',
                'start_time' => 'nullable',
                'end_date' => 'required|date|after_or_equal:start_date',
                'end_time' => 'nullable',
                'count_daily' => 'nullable|integer|min:0',
            ]);
            
            $data = $request->all();
            
            if (empty($data['key_uuid'])) {
                $data['key_uuid'] = (string) Str::uuid();
            }
            
            $data['created_by'] = Auth::id() ?? 0;
            $data['updated_by'] = Auth::id() ?? 0;
            
            $data['lock_arrangement'] = $request->has('lock_arrangement') ? 1 : 0;
            $data['status_sent'] = $request->has('status_sent') ? 1 : 0;
            $data['status_finalized'] = $request->has('status_finalized') ? 1 : 0;
            
            if ($data['status_finalized']) {
                $data['status_sent'] = 1;
                $data['lock_arrangement'] = 1;
            } elseif ($data['status_sent']) {
                $data['lock_arrangement'] = 1;
            }
            
            $data['count_daily'] = $data['count_daily'] ?? 0;
            
            $busAssignment = BusAssignment::create($data);
            
            if ($request->filled('update_daily_itineraries') && $request->update_daily_itineraries) {
                DailyItinerary::where('yoyaku_uuid', $data['yoyaku_uuid'])
                    ->update(['bus_ass_uuid' => $data['key_uuid']]);
                
                $dailyCount = DailyItinerary::where('bus_ass_uuid', $data['key_uuid'])->count();
                $busAssignment->count_daily = $dailyCount;
                $busAssignment->save();
            }
            
            if ($request->input('iframe') == '1') {
                return response()->json(['success' => true, 'id' => $busAssignment->id, 'uuid' => $busAssignment->key_uuid]);
            }
            
            return redirect()->route('masters.bus-assignments.index')
                ->with('success', '車両割当が正常に作成されました。');
                
        } catch (\Exception $e) {
            if ($request->input('iframe') == '1') {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return back()->withInput()
                ->with('error', '保存に失敗しました: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $busAssignment = BusAssignment::with([
            'groupInfo', 
            'vehicle', 
            'driver', 
            'dailyItineraries' => function($query) {
                $query->orderBy('date', 'asc')
                      ->orderBy('time_start', 'asc');
            }
        ])
        ->where('id', $id)
        ->orWhere('key_uuid', $id)
        ->firstOrFail();
            
        return view('masters.bus-assignments.show', compact('busAssignment'));
    }

    public function edit(string $id)
    {
        $busAssignment = BusAssignment::with(['groupInfo', 'vehicle', 'driver', 'dailyItinerary'])
            ->where('id', $id)
            ->orWhere('key_uuid', $id)
            ->firstOrFail();
        
        $groupInfos = GroupInfo::orderBy('start_date', 'desc')->get();
        
        $dailyItineraries = DailyItinerary::with('groupInfo')
            ->orderBy('date', 'asc')
            ->orderBy('time_start')
            ->get();
        
        $vehicles = Vehicle::with(['vehicleModel', 'branch'])
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('registration_number')
            ->get();
        
        $drivers = Driver::with('branch')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
        
        return view('masters.bus-assignments.edit', compact(
            'busAssignment',
            'groupInfos',
            'dailyItineraries',
            'vehicles',
            'drivers'
        ));
    }

    public function update(Request $request, string $id)
    {
        try {
            $busAssignment = BusAssignment::where('id', $id)
                ->orWhere('key_uuid', $id)
                ->firstOrFail();
            
            $validated = $request->validate([
                'yoyaku_uuid' => 'required|string|max:36',
                'daily_itinerary_id' => 'nullable|exists:daily_itinerary,id',
                'vehicle_id' => 'nullable|integer|exists:vehicles,id',
                'driver_id' => 'nullable|integer|exists:drivers,id',
                'start_date' => 'required|date',
                'start_time' => 'nullable',
                'end_date' => 'required|date|after_or_equal:start_date',
                'end_time' => 'nullable',
                'count_daily' => 'nullable|integer|min:0',
                'lock_arrangement' => 'nullable|boolean',
                'status_sent' => 'nullable|boolean',
                'status_finalized' => 'nullable|boolean',
            ]);
            
            $data = $request->all();
            
            $data['updated_by'] = Auth::id() ?? 0;
            
            $data['lock_arrangement'] = $request->has('lock_arrangement') ? 1 : 0;
            $data['status_sent'] = $request->has('status_sent') ? 1 : 0;
            $data['status_finalized'] = $request->has('status_finalized') ? 1 : 0;
            
            if ($data['status_finalized']) {
                $data['status_sent'] = 1;
                $data['lock_arrangement'] = 1;
            } elseif ($data['status_sent']) {
                $data['lock_arrangement'] = 1;
            }
            
            $busAssignment->update($data);
            
            $dailyCount = DailyItinerary::where('bus_ass_uuid', $busAssignment->key_uuid)->count();
            if ($busAssignment->count_daily != $dailyCount) {
                $busAssignment->count_daily = $dailyCount;
                $busAssignment->save();
            }
            
            if ($request->input('iframe') == '1') {
                return response()->json(['success' => true]);
            }
            
            return redirect()->route('masters.bus-assignments.show', $busAssignment->id)
                ->with('success', '車両割当が正常に更新されました。');
                
        } catch (\Exception $e) {
            if ($request->input('iframe') == '1') {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return back()->withInput()
                ->with('error', '更新に失敗しました: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $busAssignment = BusAssignment::where('id', $id)
                ->orWhere('key_uuid', $id)
                ->firstOrFail();
            
            $dailyCount = DailyItinerary::where('bus_ass_uuid', $busAssignment->key_uuid)->count();
            
            if ($dailyCount > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'この割当に関連する日別旅程が存在します（' . $dailyCount . '件）。先に削除するか、関連付けを解除してください。'
                ], 400);
            }
            
            $busAssignment->delete();
            
            if (request()->ajax()) {
                return response()->json(['success' => true]);
            }
            
            return redirect()->route('masters.bus-assignments.index')
                ->with('success', '車両割当が正常に削除されました。');
            
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            
            return back()->with('error', '削除に失敗しました: ' . $e->getMessage());
        }
    }

    public function getDailyItineraries(string $id)
    {
        try {
            $busAssignment = BusAssignment::where('id', $id)
                ->orWhere('key_uuid', $id)
                ->firstOrFail();
            
            $dailyItineraries = DailyItinerary::where('bus_ass_uuid', $busAssignment->key_uuid)
                ->orderBy('date')
                ->orderBy('time_start')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $dailyItineraries
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getByGroup(string $groupId)
    {
        try {
            $groupInfo = GroupInfo::where('id', $groupId)
                ->orWhere('key_uuid', $groupId)
                ->firstOrFail();
            
            $busAssignments = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)
                ->with(['vehicle', 'driver'])
                ->orderBy('start_date')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $busAssignments
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function toggleLock(Request $request, string $id)
    {
        try {
            $busAssignment = BusAssignment::where('id', $id)
                ->orWhere('key_uuid', $id)
                ->firstOrFail();
            
            $busAssignment->lock_arrangement = !$busAssignment->lock_arrangement;
            $busAssignment->updated_by = Auth::id() ?? 0;
            $busAssignment->save();
            
            return response()->json([
                'success' => true,
                'lock_arrangement' => $busAssignment->lock_arrangement
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function markAsSent(Request $request, string $id)
    {
        try {
            $busAssignment = BusAssignment::where('id', $id)
                ->orWhere('key_uuid', $id)
                ->firstOrFail();
            
            $busAssignment->status_sent = 1;
            $busAssignment->lock_arrangement = 1;
            $busAssignment->updated_by = Auth::id() ?? 0;
            $busAssignment->save();
            
            return response()->json([
                'success' => true,
                'status_sent' => $busAssignment->status_sent
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function finalize(Request $request, string $id)
    {
        try {
            $busAssignment = BusAssignment::where('id', $id)
                ->orWhere('key_uuid', $id)
                ->firstOrFail();
            
            DB::beginTransaction();
            
            $busAssignment->status_finalized = 1;
            $busAssignment->status_sent = 1;
            $busAssignment->lock_arrangement = 1;
            $busAssignment->updated_by = Auth::id() ?? 0;
            $busAssignment->save();
            
            $dailyCount = DailyItinerary::where('bus_ass_uuid', $busAssignment->key_uuid)->count();
            $busAssignment->count_daily = $dailyCount;
            $busAssignment->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'status_finalized' => $busAssignment->status_finalized,
                'count_daily' => $busAssignment->count_daily
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'required|integer',
                'action' => 'required|in:lock,unlock,send,finalize'
            ]);
            
            $ids = $request->ids;
            $action = $request->action;
            
            DB::beginTransaction();
            
            foreach ($ids as $id) {
                $busAssignment = BusAssignment::find($id);
                
                if ($busAssignment) {
                    switch ($action) {
                        case 'lock':
                            $busAssignment->lock_arrangement = 1;
                            break;
                        case 'unlock':
                            if ($busAssignment->status_sent || $busAssignment->status_finalized) {
                                continue 2;
                            }
                            $busAssignment->lock_arrangement = 0;
                            break;
                        case 'send':
                            $busAssignment->status_sent = 1;
                            $busAssignment->lock_arrangement = 1;
                            break;
                        case 'finalize':
                            $busAssignment->status_finalized = 1;
                            $busAssignment->status_sent = 1;
                            $busAssignment->lock_arrangement = 1;
                            break;
                    }
                    
                    $busAssignment->updated_by = Auth::id() ?? 0;
                    $busAssignment->save();
                }
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function export(Request $request)
    {
        try {
            $query = BusAssignment::with(['groupInfo', 'vehicle', 'driver']);
            
            if ($request->filled('start_date_from')) {
                $query->where('start_date', '>=', $request->start_date_from);
            }
            
            if ($request->filled('start_date_to')) {
                $query->where('start_date', '<=', $request->start_date_to);
            }
            
            if ($request->filled('status')) {
                switch ($request->status) {
                    case 'finalized':
                        $query->where('status_finalized', 1);
                        break;
                    case 'sent':
                        $query->where('status_sent', 1)->where('status_finalized', 0);
                        break;
                    case 'locked':
                        $query->where('lock_arrangement', 1)->where('status_sent', 0)->where('status_finalized', 0);
                        break;
                }
            }
            
            $busAssignments = $query->orderBy('start_date')->get();
            
            $filename = 'bus_assignments_' . date('Ymd_His') . '.csv';
            $handle = fopen('php://output', 'w');
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            fputcsv($handle, [
                'ID',
                'Key UUID',
                '予約UUID',
                '団体名',
                '車両',
                '運転手',
                '開始日',
                '開始時間',
                '終了日',
                '終了時間',
                'ステータス',
                '日別旅程数'
            ]);
            
            foreach ($busAssignments as $assignment) {
                fputcsv($handle, [
                    $assignment->id,
                    $assignment->key_uuid,
                    $assignment->yoyaku_uuid,
                    $assignment->groupInfo->group_name ?? '---',
                    $assignment->vehicle ? $assignment->vehicle->registration_number . ' (' . ($assignment->vehicle->vehicleModel->model_name ?? '不明') . ')' : '未割当',
                    $assignment->driver->name ?? '未割当',
                    $assignment->start_date,
                    $assignment->start_time,
                    $assignment->end_date,
                    $assignment->end_time,
                    $assignment->status_display,
                    $assignment->count_daily
                ]);
            }
            
            fclose($handle);
            exit;
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getDailyItinerariesByGroup(string $yoyakuUuid)
    {
        try {
            $dailyItineraries = DailyItinerary::where('yoyaku_uuid', $yoyakuUuid)
                ->orderBy('date')
                ->orderBy('time_start')
                ->get(['id', 'date', 'itinerary', 'time_start', 'time_end']);
            
            return response()->json([
                'success' => true,
                'data' => $dailyItineraries
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}