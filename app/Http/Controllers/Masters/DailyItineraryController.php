<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use App\Models\Masters\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyItineraryController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyItinerary::with([
            'groupInfo', 
            'busAssignment.vehicle', 
            'busAssignment.driver'
        ]);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('itinerary', 'like', "%{$search}%")
                  ->orWhere('start_location', 'like', "%{$search}%")
                  ->orWhere('end_location', 'like', "%{$search}%")
                  ->orWhere('vehicle', 'like', "%{$search}%")
                  ->orWhere('driver', 'like', "%{$search}%")
                  ->orWhereHas('groupInfo', function($q) use ($search) {
                      $q->where('group_name', 'like', "%{$search}%")
                        ->orWhere('agency', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('accommodation')) {
            $query->where('accommodation', $request->accommodation);
        }

        $dailyItineraries = $query->orderBy('date', 'asc')
                                 ->orderBy('time_start')
                                 ->paginate(20)
                                 ->withQueryString();

        return view('masters.daily-itineraries.index', compact('dailyItineraries'));
    }

    public function create()
    {
        $groupInfos = GroupInfo::select(['id', 'key_uuid', 'agency', 'group_name', 'start_date'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $busAssignments = BusAssignment::with('vehicle')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('masters.daily-itineraries.create', compact('groupInfos', 'busAssignments'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'key_uuid' => 'required|string|max:36|exists:group_info,key_uuid',
                'date' => 'required|date',
                'time_start' => 'nullable|date_format:H:i',
                'time_end' => 'nullable|date_format:H:i|after:time_start',
                'itinerary' => 'nullable|string|max:200',
                'start_location' => 'nullable|string|max:200',
                'end_location' => 'nullable|string|max:200',
                'accommodation' => 'nullable|boolean',
                'bus_ass_uuid' => 'nullable|string|max:36|exists:bus_assignment,key_uuid',
                'yoyaku_uuid' => 'nullable|string|max:36',
                'remarks' => 'nullable|string',
                
                'vehicles' => 'nullable|array',
                'vehicles.*.vehicle_id' => 'nullable|exists:vehicles,id',
                'vehicles.*.vehicle_name' => 'nullable|string|max:200',
                'vehicles.*.vehicle_number' => 'nullable|string|max:50',
                'vehicles.*.driver_id' => 'nullable|exists:drivers,id',
                'vehicles.*.driver_name' => 'nullable|string|max:100',
                'vehicles.*.guide' => 'nullable|string|max:100',
                'vehicles.*.seating_capacity' => 'nullable|integer',
                'vehicles.*.vehicle_type' => 'nullable|string|max:100',
                'vehicles.*.vehicle_model' => 'nullable|string|max:200',
                'vehicles.*.vehicle_branch' => 'nullable|string|max:200',
                'vehicles.*.remarks' => 'nullable|string|max:255',
                'vehicles.*.display_order' => 'required|integer|min:1',
            ]);

            $userId = session('user_id', auth()->id() ?? 0);
            
            if (!empty($validated['time_start'])) {
                $validated['time_start'] = $validated['time_start'] . ':00';
            }
            if (!empty($validated['time_end'])) {
                $validated['time_end'] = $validated['time_end'] . ':00';
            }

            $groupInfo = GroupInfo::where('key_uuid', $validated['key_uuid'])->first();
            $ignoreOperation = $groupInfo && $groupInfo->ignore_operation;

            if (!$ignoreOperation && !empty($validated['vehicles']) && is_array($validated['vehicles'])) {
                foreach ($validated['vehicles'] as $vehicle) {
                    $this->checkConflict(
                        $vehicle['vehicle_id'] ?? null,
                        $vehicle['driver_id'] ?? null,
                        $validated['date'],
                        $validated['time_start'],
                        $validated['date'],
                        $validated['time_end'],
                        null
                    );
                }
            }

            DB::beginTransaction();

            $vehicleData = $this->processVehicleData($validated['vehicles'] ?? []);
            
            $mainData = array_merge($validated, $vehicleData['main']);
            
            $mainData['created_by'] = $userId;
            $mainData['updated_by'] = $userId;

            $dailyItinerary = DailyItinerary::create($mainData);
            
            if ($request->has('vehicles') && is_array($request->vehicles) && count($request->vehicles) > 0) {
                $this->saveBusAssignments($dailyItinerary, $request->vehicles, $userId);
            }
            
            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => '日別旅程を登録しました。',
                    'data' => $dailyItinerary
                ]);
            }

            return redirect()
                ->route('masters.daily-itineraries.index')
                ->with('success', '日別旅程を登録しました。');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => '入力内容にエラーがあります。',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->with('error', '登録に失敗しました: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $dailyItinerary = DailyItinerary::with([
            'groupInfo', 
            'busAssignment.vehicle', 
            'busAssignment.driver'
        ])->findOrFail($id);

        return view('masters.daily-itineraries.show', compact('dailyItinerary'));
    }

    public function edit($id)
    {
        $dailyItinerary = DailyItinerary::with([
            'groupInfo',
            'busAssignments.vehicle.vehicleType',
            'busAssignments.vehicle.vehicleModel',
            'busAssignments.vehicle.branch',
            'busAssignments.driver'
        ])->findOrFail($id);
        
    if ($dailyItinerary->busAssignments->count() > 0) {
        foreach ($dailyItinerary->busAssignments as $assignment) {
            \Log::info('车辆分配ID: ' . $assignment->id);
            \Log::info('车辆ID: ' . $assignment->vehicle_id);
            \Log::info('车辆是否存在: ' . ($assignment->vehicle ? '是' : '否'));
            if ($assignment->vehicle) {
                \Log::info('车牌号: ' . $assignment->vehicle->registration_number);
                \Log::info('定员: ' . $assignment->vehicle->seating_capacity);
            }
        }
    } else {
        \Log::info('没有找到车辆分配数据');
    }
        
        $groupItineraries = DailyItinerary::where('key_uuid', $dailyItinerary->key_uuid)
            ->orderBy('date')
            ->orderBy('time_start')
            ->get();
            
        $groupInfos = GroupInfo::select(['id', 'key_uuid', 'group_name', 'agency'])
            ->orderBy('group_name')
            ->get();
        
        $vehicles = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                          ->where('is_active', true)
                          ->orderBy('display_order', 'asc')
                          ->orderBy('vehicle_code', 'asc')
                          ->get();
        
        $drivers = Driver::with('branch')
                        ->where('is_active', true)
                        ->orderBy('display_order', 'asc')
                        ->orderBy('driver_code', 'asc')
                        ->get();
        
        $guides = Guide::with('branch')
                      ->where('is_active', true)
                      ->orderBy('display_order', 'asc')
                      ->orderBy('guide_code', 'asc')
                      ->get();
        
        return view('masters.daily-itineraries.edit', compact(
            'dailyItinerary', 
            'groupItineraries', 
            'groupInfos', 
            'vehicles',
            'drivers',
            'guides'
        ));
    }

    public function update(Request $request, $id)
    {
        try {
            $dailyItinerary = DailyItinerary::findOrFail($id);
            
            if ($request->has('time_start') && $request->time_start) {
                if (strlen($request->time_start) > 5) {
                    $request->merge(['time_start' => substr($request->time_start, 0, 5)]);
                }
            }
            
            if ($request->has('time_end') && $request->time_end) {
                if (strlen($request->time_end) > 5) {
                    $request->merge(['time_end' => substr($request->time_end, 0, 5)]);
                }
            }
            
            $rules = [
                'key_uuid' => 'required|string|max:36',
                'yoyaku_uuid' => 'nullable|string|max:36',
                'date' => 'required|date',
                'time_start' => 'required|date_format:H:i',
                'time_end' => 'required|date_format:H:i|after:time_start',
                'itinerary' => 'nullable|string|max:200',
                'start_location' => 'nullable|string|max:200',
                'end_location' => 'nullable|string|max:200',
                'accommodation' => 'nullable|boolean',
                'remarks' => 'nullable|string',
                'bus_ass_uuid' => 'nullable|string|max:36',
                
                'vehicles' => 'nullable|array',
                'vehicles.*.id' => 'nullable|integer|exists:bus_assignment,id',
                'vehicles.*.vehicle_id' => 'nullable|exists:vehicles,id',
                'vehicles.*.vehicle_name' => 'nullable|string|max:200',
                'vehicles.*.vehicle_number' => 'nullable|string|max:50',
                'vehicles.*.driver_id' => 'nullable|exists:drivers,id',
                'vehicles.*.driver_name' => 'nullable|string|max:100',
                'vehicles.*.guide' => 'nullable|string|max:100',
                'vehicles.*.remarks' => 'nullable|string|max:255',
                'vehicles.*.display_order' => 'required|integer|min:1',
            ];
            
            $messages = [
                'key_uuid.required' => 'UUIDは必須です。',
                'date.required' => '日付は必須です。',
                'time_start.required' => '開始時刻は必須です。',
                'time_start.date_format' => '開始時刻はHH:MM形式で入力してください。',
                'time_end.required' => '終了時刻は必須です。',
                'time_end.date_format' => '終了時刻はHH:MM形式で入力してください。',
                'time_end.after' => '終了時刻は開始時刻より後でなければなりません。',
            ];
            
            $validated = $request->validate($rules, $messages);
            
            $userId = session('user_id', auth()->id() ?? 0);
            $validated['updated_by'] = $userId;
            
            foreach ($validated as $key => $value) {
                if ($value === '') {
                    $validated[$key] = null;
                }
            }
            
            if (isset($validated['accommodation'])) {
                $validated['accommodation'] = filter_var($validated['accommodation'], FILTER_VALIDATE_BOOLEAN);
            }
            
            if (isset($validated['time_start']) && $validated['time_start']) {
                $validated['time_start'] = $validated['time_start'] . ':00';
            }
            
            if (isset($validated['time_end']) && $validated['time_end']) {
                $validated['time_end'] = $validated['time_end'] . ':00';
            }

            $groupInfo = GroupInfo::where('key_uuid', $validated['key_uuid'])->first();
            $ignoreOperation = $groupInfo && $groupInfo->ignore_operation;

            if (!$ignoreOperation && !empty($validated['vehicles']) && is_array($validated['vehicles'])) {
                foreach ($validated['vehicles'] as $vehicle) {
                    $this->checkConflict(
                        $vehicle['vehicle_id'] ?? null,
                        $vehicle['driver_id'] ?? null,
                        $validated['date'],
                        $validated['time_start'],
                        $validated['date'],
                        $validated['time_end'],
                        $vehicle['id'] ?? null
                    );
                }
            }
            
            DB::beginTransaction();
            
            $dailyItinerary->update($validated);
            
            if ($request->has('vehicles') && is_array($request->vehicles) && count($request->vehicles) > 0) {
                $this->saveBusAssignments($dailyItinerary, $request->vehicles, $userId);
            }
            
            DB::commit();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '日別旅程を更新しました。',
                    'redirect_url' => route('masters.daily-itineraries.edit', $dailyItinerary->id)
                ]);
            }
            
            return redirect()
                ->route('masters.daily-itineraries.edit', $dailyItinerary->id)
                ->with('success', '日別旅程を更新しました。');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '入力内容にエラーがあります。',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors());
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '更新に失敗しました: ' . $e->getMessage());
        }
    }

    private function checkConflict($vehicleId, $driverId, $startDate, $startTime, $endDate, $endTime, $excludeId = null)
    {
        if (empty($vehicleId) && empty($driverId)) {
            return;
        }

        $startDateTime = $startDate . ' ' . ($startTime ?? '00:00:00');
        $endDateTime = $endDate . ' ' . ($endTime ?? '23:59:59');

        if (!empty($vehicleId)) {
            $vehicleQuery = BusAssignment::where('vehicle_id', $vehicleId)
                ->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $startDateTime)
                           ->where('end_date', '>=', $startDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $endDateTime)
                           ->where('end_date', '>=', $endDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '>=', $startDateTime)
                           ->where('end_date', '<=', $endDateTime);
                    });
                });

            if ($excludeId) {
                $vehicleQuery->where('id', '!=', $excludeId);
            }

            if ($vehicleQuery->exists()) {
                throw new \Exception('選択された車両は、指定された時間に既に割り当てられています。');
            }
        }

        if (!empty($driverId)) {
            $driverQuery = BusAssignment::where('driver_id', $driverId)
                ->where(function($q) use ($startDateTime, $endDateTime) {
                    $q->where(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $startDateTime)
                           ->where('end_date', '>=', $startDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '<=', $endDateTime)
                           ->where('end_date', '>=', $endDateTime);
                    })->orWhere(function($q2) use ($startDateTime, $endDateTime) {
                        $q2->where('start_date', '>=', $startDateTime)
                           ->where('end_date', '<=', $endDateTime);
                    });
                });

            if ($excludeId) {
                $driverQuery->where('id', '!=', $excludeId);
            }

            if ($driverQuery->exists()) {
                throw new \Exception('選択された運転手は、指定された時間に既に割り当てられています。');
            }
        }
    }

    private function saveBusAssignments($dailyItinerary, $vehicles, $userId)
    {
        if (empty($vehicles) || !is_array($vehicles)) {
            return;
        }
        
        usort($vehicles, function($a, $b) {
            return ($a['display_order'] ?? 0) - ($b['display_order'] ?? 0);
        });
        
        $existingAssignments = BusAssignment::where('daily_itinerary_id', $dailyItinerary->id)->get();
        $existingIds = $existingAssignments->pluck('id')->toArray();
        $processedIds = [];
        
        foreach ($vehicles as $index => $vehicle) {
            $existingAssignment = null;
            if (isset($vehicle['id']) && $vehicle['id']) {
                $existingAssignment = BusAssignment::find($vehicle['id']);
            }
            
            $busAssignmentData = [
                'daily_itinerary_id' => $dailyItinerary->id,
                'yoyaku_uuid' => $dailyItinerary->yoyaku_uuid ?? $dailyItinerary->key_uuid,
                'vehicle_id' => $vehicle['vehicle_id'] ?? null,
                'driver_id' => $vehicle['driver_id'] ?? null,
                'start_date' => $dailyItinerary->date,
                'start_time' => $dailyItinerary->time_start,
                'end_date' => $dailyItinerary->date,
                'end_time' => $dailyItinerary->time_end,
                'lock_arrangement' => 0,
                'status_sent' => 0,
                'status_finalized' => 0,
                'count_daily' => 1,
                'updated_by' => $userId,
                'updated_at' => now(),
            ];
            
            if ($existingAssignment) {
                $busAssignmentData['key_uuid'] = $existingAssignment->key_uuid;
                $existingAssignment->update($busAssignmentData);
                $processedIds[] = $existingAssignment->id;
                
                if ($index === 0) {
                    $dailyItinerary->update([
                        'bus_ass_uuid' => $existingAssignment->key_uuid,
                        'vehicle' => $vehicle['vehicle_name'] ?? null,
                        'driver' => $vehicle['driver_name'] ?? null,
                    ]);
                }
                
            } else {
                $busAssignmentUuid = (string) Str::uuid();
                $busAssignmentData['key_uuid'] = $busAssignmentUuid;
                $busAssignmentData['created_by'] = $userId;
                
                $newAssignment = BusAssignment::create($busAssignmentData);
                $processedIds[] = $newAssignment->id;
                
                if ($index === 0) {
                    $dailyItinerary->update([
                        'bus_ass_uuid' => $busAssignmentUuid,
                        'vehicle' => $vehicle['vehicle_name'] ?? null,
                        'driver' => $vehicle['driver_name'] ?? null,
                    ]);
                }
            }
        }
        
        $idsToDelete = array_diff($existingIds, $processedIds);
        if (!empty($idsToDelete)) {
            BusAssignment::whereIn('id', $idsToDelete)->delete();
        }
    }

    private function processVehicleData($vehicles)
    {
        $mainVehicle = [
            'vehicle' => null,
            'driver' => null,
        ];
        
        $details = [];
        
        if (!empty($vehicles) && is_array($vehicles)) {
            usort($vehicles, function($a, $b) {
                return ($a['display_order'] ?? 0) - ($b['display_order'] ?? 0);
            });
            
            foreach ($vehicles as $index => $vehicle) {
                if ($index === 0) {
                    $mainVehicle['vehicle'] = $vehicle['vehicle_name'] ?? null;
                    $mainVehicle['driver'] = $vehicle['driver_name'] ?? null;
                }
                
                $details[] = $vehicle;
            }
        }
        
        return [
            'main' => $mainVehicle,
            'details' => $details
        ];
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $dailyItinerary = DailyItinerary::findOrFail($id);
            $groupKeyUuid = $dailyItinerary->key_uuid;
            
            $itineraryCount = DailyItinerary::where('key_uuid', $groupKeyUuid)->count();
            
            BusAssignment::where('daily_itinerary_id', $dailyItinerary->id)->delete();
            
            $dailyItinerary->delete();
            
            if ($itineraryCount <= 1) {
                GroupInfo::where('key_uuid', $groupKeyUuid)->delete();
            }

            DB::commit();

            return redirect()->route('masters.daily-itineraries.index')
                ->with([
                    'success' => '日別旅程を削除しました。',
                    'alert-type' => 'success'
                ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with([
                    'error' => '削除中にエラーが発生しました: ' . $e->getMessage(),
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function byGroup($keyUuid)
    {
        $groupInfo = GroupInfo::where('key_uuid', $keyUuid)->firstOrFail();
        
        $dailyItineraries = DailyItinerary::where('key_uuid', $keyUuid)
                                          ->orderBy('date', 'asc')
                                          ->orderBy('time_start', 'asc')
                                          ->get();
        
        return view('masters.daily-itineraries.by-group', compact('groupInfo', 'dailyItineraries'));
    }
    
    public function export(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $itineraries = DailyItinerary::with(['groupInfo', 'busAssignment.vehicle', 'busAssignment.driver'])
            ->whereBetween('date', [$request->date_from, $request->date_to])
            ->orderBy('date')
            ->orderBy('time_start')
            ->get();

        $filename = 'itineraries_' . date('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($itineraries) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', '代理店', '団体名', '日付', '開始時間', '終了時間', '旅程', '出発地', '到着地', 
                '宿泊', '車両', '運転手', '備考'
            ]);
            
            foreach ($itineraries as $itinerary) {
                fputcsv($file, [
                    $itinerary->id,
                    $itinerary->groupInfo->agency ?? '',
                    $itinerary->groupInfo->group_name ?? '',
                    $itinerary->date,
                    $itinerary->time_start,
                    $itinerary->time_end,
                    $itinerary->itinerary,
                    $itinerary->start_location,
                    $itinerary->end_location,
                    $itinerary->accommodation ? '有' : '無',
                    $itinerary->vehicle,
                    $itinerary->driver,
                    $itinerary->remarks,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}