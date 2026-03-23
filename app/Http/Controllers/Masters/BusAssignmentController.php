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
        $groupInfos = GroupInfo::orderBy('created_at', 'desc')->get();

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

        $allAssignments = collect([$busAssignment])->concat($otherAssignments);

        $startDate = $busAssignment->start_date ? $busAssignment->start_date->format('Y-m-d') : now()->format('Y-m-d');
        $endDate = $busAssignment->end_date ? $busAssignment->end_date->format('Y-m-d') : now()->addDays(7)->format('Y-m-d');

        return view('masters.bus-assignments.edit', compact(
            'busAssignment',
            'allAssignments',
            'vehicles',
            'drivers',
            'guides',
            'groupInfos',
            'startDate',
            'endDate'
        ));
    }

    public function update(Request $request, $id)
    {
        $busAssignment = BusAssignment::findOrFail($id);
        
        $validated = $request->validate([
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

        $isIgnored = $validated['ignore_operation'] ?? $busAssignment->ignore_operation ?? false;
        
        if (!$isIgnored) {
            $needCheck = false;
            
            $newVehicleId = $validated['vehicle_id'] ?? $busAssignment->vehicle_id;
            $newDriverId = $validated['driver_id'] ?? $busAssignment->driver_id;
            $newStartDate = $validated['start_date'] ?? $busAssignment->start_date;
            $newEndDate = $validated['end_date'] ?? $busAssignment->end_date;
            $newStartTime = $validated['start_time'] ?? $busAssignment->start_time;
            $newEndTime = $validated['end_time'] ?? $busAssignment->end_time;
            
            if ($newVehicleId != $busAssignment->vehicle_id ||
                $newDriverId != $busAssignment->driver_id ||
                $newStartDate != $busAssignment->start_date ||
                $newEndDate != $busAssignment->end_date) {
                $needCheck = true;
            }
            
            if ($needCheck) {
                $availability = BusAssignment::checkAvailability([
                    'vehicle_id' => $newVehicleId,
                    'driver_id' => $newDriverId,
                    'start_date' => $newStartDate,
                    'start_time' => $newStartTime,
                    'end_date' => $newEndDate,
                    'end_time' => $newEndTime,
                    'ignore_operation' => false,
                ], $busAssignment->id);

                if (!$availability['vehicle'] || !$availability['driver']) {
                    return back()->withInput()->withErrors([
                        'conflict' => $availability['message']
                    ]);
                }
            }
        }

        $busAssignment->update($validated);

        if ($request->has('daily_itineraries')) {
            $this->updateDailyItineraries($request->daily_itineraries, $busAssignment->id);
        }

        if ($request->has('deleted_itineraries')) {
            DailyItinerary::whereIn('id', $request->deleted_itineraries)->delete();
        }

        return back()->with('success', '運行割当を更新しました。');
    }

    private function updateDailyItineraries(array $itineraries, $busAssignmentId)
    {
        foreach ($itineraries as $index => $data) {
            unset($data['display_order']);
            unset($data['vehicle_group']);
            
            if (empty($data['id'])) {
                $data['bus_assignment_id'] = $busAssignmentId;
                DailyItinerary::create($data);
            } else {
                $itinerary = DailyItinerary::find($data['id']);
                if ($itinerary) {
                    $itinerary->update($data);
                }
            }
        }
    }

    public function updateSingle(Request $request, $id)
    {
        $busAssignment = BusAssignment::findOrFail($id);
        
        $validated = $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'guide_id' => 'nullable|exists:guides,id',
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

        $busAssignment->update($validated);

        return response()->json([
            'success' => true,
            'message' => '運行割当を更新しました。'
        ]);
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