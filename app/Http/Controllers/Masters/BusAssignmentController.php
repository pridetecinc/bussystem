<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use App\Models\Masters\Guide;
use App\Models\Masters\Branch;
use App\Models\Masters\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BusAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $groupName = $request->input('group_name');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $dateType = $request->input('date_type');
        $reservationId = $request->input('reservation_id');
        $operationId = $request->input('operation_id');
        $branchId = $request->input('branch_id');
        $vehicleTypeId = $request->input('vehicle_type_id');
        $vehicleName = $request->input('vehicle_name');
        $vehicleId = $request->input('vehicle_id');
    
        if ($dateType == 'today') {
            $startDate = now()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        } elseif ($dateType == 'same') {
            if ($startDate) {
                $endDate = $startDate;
            }
        } else {
            $startDate = $startDate ?? now()->format('Y-m-d');
            $endDate = $endDate ?? now()->addMonths(2)->format('Y-m-d');
        }
    
        $query = BusAssignment::with([
                'groupInfo',
                'vehicle.vehicleModel',
                'driver',
                'guide',
                'dailyItineraries' => function($query) {
                    $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
                }
            ]);
    
        if ($operationId) {
            $query->where('id', $operationId);
        }
    
        if ($reservationId) {
            $query->where('group_info_id', $reservationId);
        }
    
        if ($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }
    
        if ($startDate && $endDate) {
            $query->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function($subQ) use ($startDate, $endDate) {
                      $subQ->where('start_date', '<=', $startDate)
                           ->where('end_date', '>=', $endDate);
                  });
            });
        }
    
        if ($vehicleName) {
            $query->whereHas('vehicle', function($q) use ($vehicleName) {
                $q->where('registration_number', 'like', '%' . $vehicleName . '%');
            });
        }
    
        if ($vehicleTypeId) {
            $query->whereHas('vehicle', function($q) use ($vehicleTypeId) {
                $q->where('vehicle_type_id', $vehicleTypeId);
            });
        }
    
        if ($branchId) {
            $query->whereHas('vehicle', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }
    
        if ($groupName) {
            $query->whereHas('groupInfo', function($q) use ($groupName) {
                $q->where('group_name', 'like', '%' . $groupName . '%');
            });
        }
    
        $query->orWhereDoesntHave('groupInfo');
    
        $assignments = $query->orderBy('vehicle_index', 'asc')
            ->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate(20)
            ->withQueryString();
    
        $totalAdult = $assignments->sum('adult_count');
        $totalChild = $assignments->sum('child_count');
        $totalGuide = $assignments->sum('guide_count');
        $totalAmount = $totalAdult * 15000;
    
        $branches = Branch::orderBy('display_order')->get();
        $vehicleTypes = VehicleType::with('models')->orderBy('type_name')->get();
        $vehicles = Vehicle::with('vehicleModel')->orderBy('registration_number')->get();
    
        return view('masters.bus-assignments.index', compact(
            'assignments',
            'groupName',
            'startDate',
            'endDate',
            'dateType',
            'reservationId',
            'operationId',
            'branchId',
            'vehicleTypeId',
            'vehicleId',
            'vehicleName',
            'totalAdult',
            'totalChild',
            'totalGuide',
            'totalAmount',
            'branches',
            'vehicleTypes',
            'vehicles'
        ));
    }

    public function create()
    {
        $groupInfos = GroupInfo::orderBy('created_at', 'desc')->get();
        $vehicles = Vehicle::all();
        $drivers = Driver::all();
        $guides = Guide::all();

        return view('masters.bus-assignments.create', compact('groupInfos', 'vehicles', 'drivers', 'guides'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_info_id' => 'required|exists:group_info,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'guide_id' => 'nullable|exists:guides,id',
            'start_date' => 'required|date',
            'start_time' => 'nullable',
            'end_date' => 'required|date',
            'end_time' => 'nullable',
            'vehicle_number' => 'nullable|string|max:50',
            'step_car' => 'nullable|string|max:50',
            'adult_count' => 'nullable|integer',
            'child_count' => 'nullable|integer',
            'guide_count' => 'nullable|integer',
            'luggage_count' => 'nullable|integer',
            'representative' => 'nullable|string|max:100',
            'representative_phone' => 'nullable|string|max:20',
            'operation_remarks' => 'nullable|string',
            'ignore_operation' => 'nullable|boolean',
        ]);

        $availability = BusAssignment::checkAvailability([
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'driver_id' => $validated['driver_id'] ?? null,
            'start_date' => $validated['start_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_date' => $validated['end_date'],
            'end_time' => $validated['end_time'] ?? null,
            'ignore_operation' => $validated['ignore_operation'] ?? false,
        ]);

        if (!$availability['vehicle'] || !$availability['driver']) {
            return back()->withInput()->withErrors([
                'conflict' => $availability['message']
            ]);
        }

        $maxIndex = BusAssignment::where('group_info_id', $validated['group_info_id'])
                                 ->max('vehicle_index') ?? 0;
        $validated['vehicle_index'] = $maxIndex + 1;

        $validated['key_uuid'] = (string) \Str::uuid();

        BusAssignment::create($validated);

        return redirect()->route('masters.bus-assignments.index')
                         ->with('success', '運行割当を作成しました。');
    }

    public function show($id)
    {
        $busAssignment = BusAssignment::with(['groupInfo', 'vehicle', 'driver', 'guide', 'dailyItineraries'])
            ->findOrFail($id);
        
        return view('masters.bus-assignments.show', compact('busAssignment'));
    }

    public function edit($id)
    {
        $busAssignment = BusAssignment::with([
                'groupInfo',
                'vehicle.vehicleModel',
                'driver',
                'guide',
            ])
            ->findOrFail($id);
        
        $busAssignment->load([
            'dailyItineraries' => function($query) {
                $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
            }
        ]);

        $vehicles = Vehicle::with('vehicleModel')->orderBy('registration_number')->get();
        $drivers = Driver::orderBy('name')->get();
        $guides = Guide::orderBy('name')->get();
        
        $otherAssignments = collect();
        if ($busAssignment->group_info_id) {
            $otherAssignments = BusAssignment::with([
                    'vehicle.vehicleModel',
                    'driver',
                    'guide',
                    'dailyItineraries' => function($query) {
                        $query->orderBy('date', 'asc')->orderBy('time_start', 'asc');
                    }
                ])
                ->where('group_info_id', $busAssignment->group_info_id)
                ->where('id', '!=', $busAssignment->id)
                ->orderBy('vehicle_index', 'asc')
                ->get();
        }

        $allAssignments = collect([$busAssignment])
            ->concat($otherAssignments)
            ->sortBy('vehicle_index')
            ->values();

        $startDate = $busAssignment->start_date 
            ? \Carbon\Carbon::parse($busAssignment->start_date)->format('Y-m-d')
            : now()->format('Y-m-d');
            
        $endDate = $busAssignment->end_date 
            ? \Carbon\Carbon::parse($busAssignment->end_date)->format('Y-m-d')
            : now()->addDays(7)->format('Y-m-d');

        return view('masters.bus-assignments.edit', compact(
            'busAssignment',
            'allAssignments',
            'vehicles',
            'drivers',
            'guides',
            'startDate',
            'endDate'
        ));
    }

public function update(Request $request, $id)
{
    // 临时调试：查看提交的数据
    \Log::info('Update Request Data', $request->all());
    
    $busAssignment = BusAssignment::findOrFail($id);
    
    $validated = $request->validate([
        'vehicle_id' => 'nullable|exists:vehicles,id',
        'driver_id' => 'nullable|exists:drivers,id',
        'guide_id' => 'nullable|exists:guides,id',
        'start_date' => 'nullable|date',
        'start_time' => 'nullable',
        'end_date' => 'nullable|date',
        'end_time' => 'nullable',
        'vehicle_number' => 'nullable|string|max:50',
        'step_car' => 'nullable|string|max:50',
        'adult_count' => 'nullable|integer|min:0',
        'child_count' => 'nullable|integer|min:0',
        'guide_count' => 'nullable|integer|min:0',
        'other_count' => 'nullable|integer|min:0',
        'luggage_count' => 'nullable|integer|min:0',
        'representative' => 'nullable|string|max:100',
        'representative_phone' => 'nullable|string|max:20',
        'attention' => 'nullable|string',
        'operation_remarks' => 'nullable|string',
        'operation_memo' => 'nullable|string',
        'operation_basic_remarks' => 'nullable|string',
        'doc_remarks' => 'nullable|string',
        'history_remarks' => 'nullable|string',
        'lock_arrangement' => 'boolean',
        'status_sent' => 'boolean',
        'status_finalized' => 'boolean',
        'vehicle_type_spec_check' => 'boolean',
        'temporary_driver' => 'boolean',
        'ignore_operation' => 'boolean',
        'ignore_driver' => 'boolean',
    ]);

    $ignoreOperation = $request->has('ignore_operation') && $request->input('ignore_operation') == '1';
    $ignoreDriver = $request->has('ignore_driver') && $request->input('ignore_driver') == '1';
    
    $validated['ignore_operation'] = $ignoreOperation;
    $validated['ignore_driver'] = $ignoreDriver;
    
    $newVehicleId = $validated['vehicle_id'] ?? $busAssignment->vehicle_id;
    $newDriverId = $validated['driver_id'] ?? $busAssignment->driver_id;
    $newGuideId = $validated['guide_id'] ?? $busAssignment->guide_id;
    
    // 获取要删除的行程ID
    $deletedItineraries = $request->input('deleted_itineraries', []);
    
    // 调试：打印要删除的ID
    \Log::info('要删除的行程ID:', $deletedItineraries);
    
    DB::beginTransaction();
    
    try {
        // 1. 先删除标记的行程
        if (!empty($deletedItineraries)) {
            $deletedCount = DailyItinerary::whereIn('id', $deletedItineraries)
                ->where('bus_assignment_id', $busAssignment->id)
                ->delete();
            
            \Log::info('删除的行程数量: ' . $deletedCount);
        }
        
        // 2. 更新 BusAssignment
        $busAssignment->update($validated);
        
        // 3. 获取更新后的车辆、司机信息
        $vehicleName = '';
        if ($newVehicleId) {
            $vehicle = Vehicle::find($newVehicleId);
            $vehicleName = $vehicle ? $vehicle->registration_number : '';
        }
        
        $driverName = '';
        if ($newDriverId) {
            $driver = Driver::find($newDriverId);
            $driverName = $driver ? $driver->name : '';
        }
        
        $guideName = '';
        if ($newGuideId) {
            $guide = Guide::find($newGuideId);
            $guideName = $guide ? $guide->name : '';
        }
        
        // 4. 更新或创建行程
        if ($request->has('daily_itineraries') && is_array($request->daily_itineraries)) {
            $this->updateDailyItineraries(
                $request->daily_itineraries, 
                $busAssignment->id,
                $newVehicleId,
                $newDriverId,
                $newGuideId,
                $vehicleName,
                $driverName,
                $guideName
            );
        }
        
        DB::commit();
        
        \Log::info('BusAssignment update committed', ['id' => $id]);
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('BusAssignment update failed: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return back()->withInput()->withErrors([
            'error' => '更新中にエラーが発生しました: ' . $e->getMessage()
        ]);
    }
    
    // 5. 冲突检测（如果未忽略运行）
    if (!$ignoreOperation) {
        $conflictMessages = [];
        
        $itineraries = DailyItinerary::where('bus_assignment_id', $busAssignment->id)->get();
        
        if ($newVehicleId) {
            foreach ($itineraries as $itinerary) {
                try {
                    $this->checkResourceConflicts(
                        'vehicle',
                        $newVehicleId,
                        $itinerary->date,
                        $itinerary->time_start,
                        $itinerary->time_end,
                        $busAssignment->id,
                        null
                    );
                } catch (\Exception $e) {
                    $conflictMessages[] = $e->getMessage();
                }
            }
        }
        
        if (!$ignoreDriver && $newDriverId) {
            foreach ($itineraries as $itinerary) {
                try {
                    $this->checkResourceConflicts(
                        'driver',
                        $newDriverId,
                        $itinerary->date,
                        $itinerary->time_start,
                        $itinerary->time_end,
                        $busAssignment->id,
                        null
                    );
                } catch (\Exception $e) {
                    $conflictMessages[] = $e->getMessage();
                }
            }
        }
        
        if (!empty($conflictMessages)) {
            return redirect()->route('masters.bus-assignments.edit', $busAssignment->id)
                ->withInput()
                ->withErrors([
                    'conflict' => implode(' ', array_unique($conflictMessages))
                ]);
        }
    }
    
    return redirect()->route('masters.bus-assignments.edit', $busAssignment->id)
        ->with('success', '運行割当を更新しました。');
}

private function updateDailyItineraries($itineraries, $busAssignmentId, $vehicleId = null, $driverId = null, $guideId = null, $vehicleName = '', $driverName = '', $guideName = '')
{
    foreach ($itineraries as $index => $itineraryData) {
        if (empty($itineraryData['date'])) {
            continue;
        }
        
        $itineraryId = $itineraryData['id'] ?? null;
        
        // 准备更新数据
        $data = [
            'bus_assignment_id' => $busAssignmentId,
            'date' => $itineraryData['date'],
            'time_start' => $itineraryData['time_start'] . ':00',
            'time_end' => $itineraryData['time_end'] . ':00',
            'itinerary' => $itineraryData['itinerary'] ?? null,
            'start_location' => $itineraryData['start_location'] ?? null,
            'end_location' => $itineraryData['end_location'] ?? null,
            'accommodation' => isset($itineraryData['accommodation']) ? (int)$itineraryData['accommodation'] : 0,
            'updated_at' => now(),
        ];
        
        if ($vehicleId) {
            $data['vehicle_id'] = $vehicleId;
            $data['vehicle'] = $vehicleName;
        } else {
            $data['vehicle_id'] = null;
            $data['vehicle'] = null;
        }
        
        if ($driverId) {
            $data['driver_id'] = $driverId;
            $data['driver'] = $driverName;
        } else {
            $data['driver_id'] = null;
            $data['driver'] = null;
        }
        
        if ($guideId) {
            $data['guide_id'] = $guideId;
            $data['guide'] = $guideName;
        } else {
            $data['guide_id'] = null;
            $data['guide'] = null;
        }
        
        $data = array_filter($data, function($value) {
            return !is_null($value);
        });
        
        if ($itineraryId && is_numeric($itineraryId)) {
            // 更新现有行程
            DailyItinerary::where('id', $itineraryId)
                ->where('bus_assignment_id', $busAssignmentId)
                ->update($data);
        } elseif (empty($itineraryId)) {
            // 创建新行程
            $data['created_at'] = now();
            $data['created_by'] = session('user_id', auth()->id() ?? 0);
            $data['updated_by'] = session('user_id', auth()->id() ?? 0);
            DailyItinerary::create($data);
        }
    }
}

private function checkResourceConflicts($type, $resourceId, $date, $startTime, $endTime, $excludeBusId = null, $excludeGroupId = null)
{
    $query = DailyItinerary::query()
        ->where('date', $date)
        ->where(function($q) use ($startTime, $endTime) {
            $q->where(function($sub) use ($startTime, $endTime) {
                $sub->where('time_start', '<', $endTime)
                    ->where('time_end', '>', $startTime);
            });
        });
    
    if ($type === 'vehicle') {
        $query->where('vehicle_id', $resourceId);
    } elseif ($type === 'driver') {
        $query->where('driver_id', $resourceId);
    }
    
    if ($excludeBusId) {
        $query->where('bus_assignment_id', '!=', $excludeBusId);
    }
    
    if ($excludeGroupId) {
        $query->where('group_info_id', '!=', $excludeGroupId);
    }
    
    $conflicts = $query->with('busAssignment')->get();
    
    foreach ($conflicts as $conflict) {
        $busAssignment = $conflict->busAssignment;
        if ($busAssignment && $busAssignment->ignore_operation) {
            continue;
        }
        
        if ($type === 'vehicle') {
            $vehicle = Vehicle::find($resourceId);
            $resourceName = $vehicle ? $vehicle->registration_number : '#' . $resourceId;
            throw new \Exception("車両「{$resourceName}」は{$date}の{$startTime}〜{$endTime}ですでに予約されています。");
        } elseif ($type === 'driver') {
            $driver = Driver::find($resourceId);
            $resourceName = $driver ? $driver->name : '#' . $resourceId;
            throw new \Exception("運転手「{$resourceName}」は{$date}の{$startTime}〜{$endTime}ですでに予約されています。");
        }
    }
}

    public function destroy($id)
    {
        $busAssignment = BusAssignment::findOrFail($id);
        $busAssignment->delete();
        
        return redirect()->route('masters.bus-assignments.index')
                         ->with('success', '運行割当を削除しました。');
    }

    public function bulkUpdate(Request $request)
    {
        $ids = $request->input('ids', []);
        $action = $request->input('action');

        if (empty($ids)) {
            return back()->with('error', '項目を選択してください。');
        }

        DB::transaction(function() use ($ids, $action) {
            $assignments = BusAssignment::whereIn('id', $ids)->get();

            foreach ($assignments as $assignment) {
                switch ($action) {
                    case 'lock':
                        $assignment->lock_arrangement = true;
                        break;
                    case 'unlock':
                        $assignment->lock_arrangement = false;
                        break;
                    case 'mark_sent':
                        $assignment->status_sent = true;
                        break;
                    case 'finalize':
                        $assignment->status_finalized = true;
                        $assignment->status_sent = true;
                        break;
                    case 'unfinalize':
                        $assignment->status_finalized = false;
                        break;
                }
                $assignment->save();
            }
        });

        return back()->with('success', '一括更新が完了しました。');
    }

    public function printInstructions(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', '印刷する項目を選択してください。');
        }

        $assignments = BusAssignment::with(['groupInfo', 'vehicle', 'driver', 'guide', 'dailyItineraries'])
            ->whereIn('id', $ids)
            ->orderBy('vehicle_index')
            ->get();

        return view('masters.bus-assignments.print', compact('assignments'));
    }

    public function updateDateTime(Request $request, BusAssignment $busAssignment)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'nullable',
            'end_date' => 'required|date',
            'end_time' => 'nullable',
        ]);

        $busAssignment->update($validated);

        return response()->json(['success' => true]);
    }
}