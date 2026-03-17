<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\GroupInfo;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\BusAssignment;
use App\Models\Masters\Vehicle;
use App\Models\Masters\Driver;
use App\Models\Masters\Guide;
use App\Models\Masters\Agency;
use App\Models\Masters\ReservationCategory;
use App\Models\Masters\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class GroupInfoController extends Controller
{
    public function index(Request $request)
    {
        $query = GroupInfo::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('agency', 'like', "%{$search}%")
                  ->orWhere('vehicle', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        $groupInfos = $query->orderBy('start_date', 'asc')
                           ->orderBy('start_time')
                           ->paginate(20)
                           ->withQueryString();

        return view('masters.group-infos.index', compact('groupInfos'));
    }

    public function show($id)
    {
        $groupInfo = GroupInfo::with('dailyItineraries')->findOrFail($id);
        return view('masters.group-infos.show', compact('groupInfo'));
    }

    public function create()
    {
        $agencies = Agency::where('is_active', true)
                         ->orderBy('display_order', 'asc')
                         ->orderBy('agency_code', 'asc')
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
                      
        $reservationCategories = ReservationCategory::where('is_active', true)
            ->orderBy('display_order')
            ->get();
        
        return view('masters.group-infos.create', compact('agencies', 'vehicles', 'drivers', 'guides', 'reservationCategories'));
    }

    private function checkConflict($vehicleId, $driverId, $startDate, $startTime, $endDate, $endTime, $excludeUuid = null)
    {
        if (empty($vehicleId) && empty($driverId)) {
            return;
        }
    
        $newStart = Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00'));
        $newEnd = Carbon::parse($endDate . ' ' . ($endTime ?? '23:59:59'));
    
        if (!empty($vehicleId)) {
            $existingAssignments = BusAssignment::where('vehicle_id', $vehicleId)
                ->when($excludeUuid, function($query) use ($excludeUuid) {
                    return $query->where('key_uuid', '!=', $excludeUuid);
                })
                ->get();
    
            foreach ($existingAssignments as $assignment) {
                $existingStart = Carbon::parse($assignment->start_date . ' ' . ($assignment->start_time ?? '00:00:00'));
                $existingEnd = Carbon::parse($assignment->end_date . ' ' . ($assignment->end_time ?? '23:59:59'));
    
                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    throw new \Exception('選択された車両は、指定された期間に既に割り当てられています。');
                }
            }
        }
    
        if (!empty($driverId)) {
            $existingAssignments = BusAssignment::where('driver_id', $driverId)
                ->when($excludeUuid, function($query) use ($excludeUuid) {
                    return $query->where('key_uuid', '!=', $excludeUuid);
                })
                ->get();
    
            foreach ($existingAssignments as $assignment) {
                $existingStart = Carbon::parse($assignment->start_date . ' ' . ($assignment->start_time ?? '00:00:00'));
                $existingEnd = Carbon::parse($assignment->end_date . ' ' . ($assignment->end_time ?? '23:59:59'));
    
                if ($newStart->lt($existingEnd) && $newEnd->gt($existingStart)) {
                    throw new \Exception('選択された運転手は、指定された期間に既に割り当てられています。');
                }
            }
        }
    }

    public function store(Request $request)
    {
        $rules = [
            'vehicle_name_input' => 'nullable|string|max:200',
            'guide_name_input' => 'nullable|string|max:100',
            'driver_name_input' => 'nullable|string|max:100',
            'agency_name_input' => 'nullable|string|max:200',
            
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'guide_id' => 'nullable|exists:guides,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'agency_id' => 'nullable|exists:agencies,id',
            
            'agency' => 'nullable|string|max:200',
            'agency_code' => 'nullable|string|max:50',
            'agency_branch' => 'nullable|string|max:100',
            'agency_phone' => 'nullable|string|max:20',
            'key_uuid' => 'nullable|string|max:200',
            'vehicle' => 'nullable|string|max:200',
            'vehicle_number' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
            'guide' => 'nullable|string|max:100',
            'driver' => 'nullable|string|max:100',
            'reservation_status' => 'nullable|string|max:50',
            'business_category' => 'nullable|string|max:100',
            'vehicle_type_selection' => 'nullable|string|max:200',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'guide_count' => 'nullable|integer|min:0',
            'other_count' => 'nullable|integer|min:0',
            'agency_contact_name' => 'nullable|string|max:100',
            'agency_country' => 'nullable|string|max:100',
            'agt_tour_id' => 'nullable|string|max:100',
            'luggage_count' => 'nullable|integer|min:0',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:200',
            'vehicle_branch' => 'nullable|string|max:200',
            'start_location' => 'nullable|string|max:255',
            'end_location' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'ignore_operation' => 'nullable',
            'ignore_attendance' => 'nullable',
            'reservation_channel' => 'nullable|string|max:100',
        ];
    
        $messages = [
            'start_date.required' => '開始日は必須です。',
            'start_date.date' => '開始日の形式が正しくありません。YYYY/MM/DD形式で入力してください。',
            'start_time.required' => '開始時間は必須です。',
            'start_time.date_format' => '開始時間の形式が正しくありません。HH:MM形式で入力してください。',
            'end_date.required' => '終了日は必須です。',
            'end_date.date' => '終了日の形式が正しくありません。YYYY/MM/DD形式で入力してください。',
            'end_time.required' => '終了時間は必須です。',
            'end_time.date_format' => '終了時間の形式が正しくありません。HH:MM形式で入力してください。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
            'vehicle_id.exists' => '選択された車両は存在しません。',
            'guide_id.exists' => '選択されたガイドは存在しません。',
            'driver_id.exists' => '選択された運転手は存在しません。',
            'agency_id.exists' => '選択された代理店は存在しません。',
            'adult_count.integer' => '大人人数は数値で入力してください。',
            'child_count.integer' => '子供人数は数値で入力してください。',
            'guide_count.integer' => 'ガイド人数は数値で入力してください。',
            'other_count.integer' => 'その他人数は数値で入力してください。',
            'luggage_count.integer' => '荷物数は数値で入力してください。',
        ];
    
        $validated = $request->validate($rules, $messages);
    
        $validated['ignore_operation'] = $request->has('ignore_operation');
        $validated['ignore_attendance'] = $request->has('ignore_attendance');
    
        try {
            DB::beginTransaction();
    
            $userId = session('user_id', auth()->id() ?? 0);
    
            $groupKeyUuid = (string) Str::uuid();
            
            $startTime = !empty($validated['start_time']) ? $validated['start_time'] . ':00' : null;
            $endTime = !empty($validated['end_time']) ? $validated['end_time'] . ':00' : null;
            
            $vehicleInfo = null;
            if (!empty($request->vehicle_id)) {
                $vehicleInfo = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                                     ->find($request->vehicle_id);
            }
    
            $driverInfo = null;
            if (!empty($request->driver_id)) {
                $driverInfo = Driver::find($request->driver_id);
            }
    
            $guideInfo = null;
            if (!empty($request->guide_id)) {
                $guideInfo = Guide::find($request->guide_id);
            }
    
            $agencyInfo = null;
            if (!empty($request->agency_id)) {
                $agencyInfo = Agency::find($request->agency_id);
            }
    
            if (!$validated['ignore_operation'] && (!empty($request->vehicle_id) || !empty($request->driver_id))) {
                $this->checkConflict(
                    $request->vehicle_id,
                    $request->driver_id,
                    $validated['start_date'],
                    $startTime,
                    $validated['end_date'],
                    $endTime,
                    null
                );
            }
    
            $groupData = [
                'key_uuid' => $groupKeyUuid,
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'guide_id' => $request->guide_id,
                'agency' => $validated['agency_name_input'] ?? $validated['agency'] ?? null,
                'agency_code' => $validated['agency_code'] ?? ($agencyInfo->agency_code ?? null),
                'agency_branch' => $validated['agency_branch'] ?? ($agencyInfo->branch_name ?? null),
                'agency_phone' => $validated['agency_phone'] ?? ($agencyInfo->phone_number ?? null),
                'agency_contact_name' => $validated['agency_contact_name'] ?? ($agencyInfo->manager_name ?? null),
                'agency_country' => $validated['agency_country'] ?? ($agencyInfo->country ?? null),
                'reservation_status' => $validated['reservation_status'] ?? '',
                'start_date' => $validated['start_date'],
                'start_time' => $startTime,
                'end_date' => $validated['end_date'],
                'end_time' => $endTime,
                'vehicle_type_selection' => $validated['vehicle_type_selection'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'itinerary_id' => 0,
                'business_category' => $validated['business_category'] ?? null,
                'itinerary_name' => null,
                'reservation_channel' => $validated['reservation_channel'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? ($vehicleInfo->vehicleType->type_name ?? null),
                'vehicle_model' => $validated['vehicle_model'] ?? ($vehicleInfo->vehicleModel->model_name ?? null),
                'vehicle' => $validated['vehicle_name_input'] ?? $validated['vehicle'] ?? ($vehicleInfo->registration_number ?? null),
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'driver' => $validated['driver_name_input'] ?? $validated['driver'] ?? ($driverInfo->name ?? null),
                'guide' => $validated['guide_name_input'] ?? $validated['guide'] ?? ($guideInfo->name ?? null),
                'vehicle_branch' => $validated['vehicle_branch'] ?? ($vehicleInfo->branch->branch_name ?? null),
                'adult_count' => $validated['adult_count'] ?? 0,
                'child_count' => $validated['child_count'] ?? 0,
                'guide_count' => $validated['guide_count'] ?? 0,
                'other_count' => $validated['other_count'] ?? 0,
                'luggage_count' => $validated['luggage_count'] ?? 0,
                'copy_new_start_date' => null,
                'agt_tour_code' => null,
                'agt_tour_id' => $validated['agt_tour_id'] ?? null,
                'ignore_operation' => $validated['ignore_operation'] ? 1 : 0,
                'ignore_attendance' => $validated['ignore_attendance'] ? 1 : 0,
                'reception_contact' => null,
                'reception_office' => null,
                'created_tag' => null,
                'lock_arrangement' => 0,
                'operation_count' => 0,
                'created_at' => now(),
                'updated_at' => now(),
                'created_by' => $userId,
                'updated_by' => $userId,
            ];
    
            $groupInfo = GroupInfo::create($groupData);
    
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            
            $daysDiff = $startDate->diffInDays($endDate) + 1;
            
            $createdItineraries = [];
    
            for ($i = 0; $i < $daysDiff; $i++) {
                $currentDate = $startDate->copy()->addDays($i);
                
                $itineraryText = '';
                if (!empty($validated['start_location']) || !empty($validated['end_location'])) {
                    if ($i == 0) {
                        $itineraryText = trim(
                            ($validated['start_location'] ?? '') . ' → ' . ($validated['end_location'] ?? '')
                        );
                    } elseif ($i == $daysDiff - 1) {
                        $itineraryText = trim(
                            ($validated['end_location'] ?? '') . ' → ' . ($validated['start_location'] ?? '')
                        );
                    } else {
                        $itineraryText = ($validated['end_location'] ?? '目的地') . ' 滞在';
                    }
                }
            
                $startLocation = null;
                $endLocation = null;
                
                if ($i == 0) {
                    $startLocation = $validated['start_location'] ?? null;
                }
                
                if ($i == $daysDiff - 1) {
                    $endLocation = $validated['end_location'] ?? null;
                }
            
                $vehicleDisplay = $groupData['vehicle'] ?? null;
                $driverDisplay = $groupData['driver'] ?? null;
                $guideDisplay = $groupData['guide'] ?? null;
            
                $dailyItineraryData = [
                    'key_uuid' => $groupKeyUuid,
                    'date' => $currentDate->format('Y-m-d'),
                    'time_start' => $startTime,
                    'time_end' => $endTime,
                    'itinerary' => $itineraryText,
                    'start_location' => $startLocation,
                    'end_location' => $endLocation,
                    'accommodation' => ($i < $daysDiff - 1) ? true : false,
                    'vehicle' => $vehicleDisplay,
                    'driver' => $driverDisplay,
                    'guide' => $guideDisplay,
                    'remarks' => $validated['remarks'] ?? null,
                    'bus_ass_uuid' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ];
            
                if (!empty($request->vehicle_id)) {
                    $dailyItineraryData['vehicle_id'] = $request->vehicle_id;
                }
                
                if (!empty($request->driver_id)) {
                    $dailyItineraryData['driver_id'] = $request->driver_id;
                }
                
                if (!empty($request->guide_id)) {
                    $dailyItineraryData['guide_id'] = $request->guide_id;
                }
            
                $itinerary = DailyItinerary::create($dailyItineraryData);
                $createdItineraries[] = $itinerary;
            }
    
            if (!empty($request->vehicle_id) || !empty($request->driver_id)) {
                $busAssignmentUuid = (string) Str::uuid();
                $firstItinerary = $createdItineraries[0] ?? null;
                
                $busAssignmentData = [
                    'key_uuid' => $busAssignmentUuid,
                    'yoyaku_uuid' => $groupKeyUuid,
                    'daily_itinerary_id' => $firstItinerary ? $firstItinerary->id : null,
                    'vehicle_id' => $request->vehicle_id,
                    'driver_id' => $request->driver_id,
                    'start_date' => $validated['start_date'],
                    'start_time' => $startTime,
                    'end_date' => $validated['end_date'],
                    'end_time' => $endTime,
                    'lock_arrangement' => 0,
                    'status_sent' => 0,
                    'status_finalized' => 0,
                    'count_daily' => $daysDiff,
                    'vehicle_number' => '01',
                    'step_car' => null,
                    'adult_count' => $validated['adult_count'] ?? 0,
                    'child_count' => $validated['child_count'] ?? 0,
                    'guide_count' => $validated['guide_count'] ?? 0,
                    'other_count' => $validated['other_count'] ?? 0,
                    'luggage_count' => $validated['luggage_count'] ?? 0,
                    'vehicle_type_spec_check' => false,
                    'temporary_driver' => false,
                    'accompanying' => null,
                    'representative' => null,
                    'representative_phone' => null,
                    'attention' => null,
                    'operation_remarks' => null,
                    'operation_memo' => null,
                    'operation_basic_remarks' => null,
                    'doc_remarks' => null,
                    'history_remarks' => null,
                    'vehicle_index' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
    
                BusAssignment::create($busAssignmentData);
                
                foreach ($createdItineraries as $itinerary) {
                    $itinerary->update(['bus_ass_uuid' => $busAssignmentUuid]);
                }
            }
    
            DB::commit();
    
            if ($request->input('iframe') == '1') {
                return response()->json([
                    'success' => true,
                    'id' => $groupInfo->id,
                    'message' => "グループ情報と{$daysDiff}日間の旅程を登録しました。",
                    'redirect' => route('masters.group-infos.edit', $groupInfo->id)
                ]);
            }
    
            return redirect()->route('masters.group-infos.edit', $groupInfo->id)
                ->with([
                    'success' => "グループ情報と{$daysDiff}日間の旅程を登録しました。",
                    'alert-type' => 'success'
                ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->input('iframe') == '1') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [
                        'system' => [$e->getMessage()]
                    ]
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '保存中にエラーが発生しました: ' . $e->getMessage(),
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function edit($id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $agencies = Agency::where('is_active', true)
                         ->orderBy('display_order', 'asc')
                         ->orderBy('agency_code', 'asc')
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
                      
        $branches = Branch::orderBy('display_order', 'asc')
                         ->orderBy('branch_code', 'asc')
                         ->get();
        
        $reservationCategories = ReservationCategory::where('is_active', true)
                                                   ->orderBy('display_order', 'asc')
                                                   ->get();
        
        $busAssignments = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)
                                      ->orderBy('vehicle_index', 'asc')
                                      ->get();
        
        $allItineraries = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                        ->orderBy('date', 'asc')
                                        ->orderBy('id', 'asc')
                                        ->get();
        
        $groupedItineraries = [];
        $uniqueVehicles = [];
        
        foreach ($allItineraries as $itinerary) {
            $vehicleKey = $itinerary->vehicle_id ?? 0;
            
            if (!isset($groupedItineraries[$vehicleKey])) {
                $vehicleInfo = null;
                if ($itinerary->vehicle_id) {
                    $vehicleInfo = $vehicles->firstWhere('id', $itinerary->vehicle_id);
                }
                
                $busAssignment = $busAssignments->firstWhere('vehicle_id', $itinerary->vehicle_id);
                
                $groupedItineraries[$vehicleKey] = [
                    'vehicle_id' => $itinerary->vehicle_id,
                    'vehicle_name' => $vehicleInfo ? $vehicleInfo->registration_number : ($itinerary->vehicle ?? '未分配车辆'),
                    'vehicle_model' => $vehicleInfo && $vehicleInfo->vehicleModel ? $vehicleInfo->vehicleModel->model_name : '',
                    'driver_name' => $itinerary->driver ?? '',
                    'bus_assignment' => $busAssignment,
                    'itineraries' => []
                ];
                
                if ($itinerary->vehicle_id && !in_array($itinerary->vehicle_id, array_column($uniqueVehicles, 'id'))) {
                    $uniqueVehicles[] = [
                        'id' => $itinerary->vehicle_id,
                        'name' => $vehicleInfo ? $vehicleInfo->registration_number : $itinerary->vehicle,
                        'model' => $vehicleInfo && $vehicleInfo->vehicleModel ? $vehicleInfo->vehicleModel->model_name : ''
                    ];
                }
            }
            
            $groupedItineraries[$vehicleKey]['itineraries'][] = $itinerary;
        }
        
        foreach ($groupedItineraries as &$group) {
            usort($group['itineraries'], function($a, $b) {
                return strtotime($a->date) - strtotime($b->date);
            });
        }
        
        if (isset($groupedItineraries[0])) {
            $groupedItineraries[0]['vehicle_name'] = '未分配车辆';
        }
        
        $hasMultipleVehicles = count($groupedItineraries) > 1;
        
        return view('masters.group-infos.edit', compact(
            'groupInfo', 
            'agencies',
            'vehicles', 
            'drivers', 
            'guides', 
            'busAssignments', 
            'allItineraries',
            'groupedItineraries',
            'uniqueVehicles',
            'hasMultipleVehicles',
            'branches',
            'reservationCategories'
        ));
    }

    public function update(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
    
        $rules = [
            'vehicle_name_input' => 'nullable|string|max:200',
            'guide_name_input' => 'nullable|string|max:100',
            'driver_name_input' => 'nullable|string|max:100',
            'agency_name_input' => 'nullable|string|max:200',
            
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'guide_id' => 'nullable|exists:guides,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'agency_id' => 'nullable|exists:agencies,id',
            
            'agt_tour_id' => 'nullable|string|max:200',
            'agency' => 'nullable|string|max:200',
            'agency_code' => 'nullable|string|max:50',
            'agency_branch' => 'nullable|string|max:100',
            'agency_phone' => 'nullable|string|max:20',
            'key_uuid' => 'required|string|max:200',
            'vehicle' => 'nullable|string|max:200',
            'vehicle_number' => 'nullable|string|max:50',
            'start_date' => 'required|date',
            'start_time' => 'nullable|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'guide' => 'nullable|string|max:100',
            'driver' => 'nullable|string|max:100',
            'reservation_status' => 'nullable|string|max:50',
            'business_category' => 'nullable|string|max:100',
            'vehicle_type_selection' => 'nullable|string|max:200',
            'adult_count' => 'nullable|integer|min:0',
            'child_count' => 'nullable|integer|min:0',
            'guide_count' => 'nullable|integer|min:0',
            'other_count' => 'nullable|integer|min:0',
            'agency_contact_name' => 'nullable|string|max:100',
            'agency_country' => 'nullable|string|max:100',
            'luggage_count' => 'nullable|integer|min:0',
            'vehicle_type' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:200',
            'vehicle_branch' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
            'ignore_operation' => 'nullable',
            'ignore_attendance' => 'nullable',
            'reservation_channel' => 'nullable|string|max:100',
            
            'bus_assignments' => 'sometimes|array',
            'bus_assignments.*.key_uuid' => 'nullable|string|max:200',
            'bus_assignments.*.vehicle_id' => 'nullable|exists:vehicles,id',
            'bus_assignments.*.driver_id' => 'nullable|exists:drivers,id',
            'bus_assignments.*.vehicle_number' => 'nullable|string|max:50',
            'bus_assignments.*.step_car' => 'nullable|string|max:255',
            'bus_assignments.*.adult_count' => 'nullable|integer|min:0',
            'bus_assignments.*.child_count' => 'nullable|integer|min:0',
            'bus_assignments.*.guide_count' => 'nullable|integer|min:0',
            'bus_assignments.*.other_count' => 'nullable|integer|min:0',
            'bus_assignments.*.luggage_count' => 'nullable|integer|min:0',
            'bus_assignments.*.vehicle_type_spec_check' => 'nullable|boolean',
            'bus_assignments.*.temporary_driver' => 'nullable|boolean',
            'bus_assignments.*.accompanying' => 'nullable|string|max:100',
            'bus_assignments.*.representative' => 'nullable|string|max:100',
            'bus_assignments.*.representative_phone' => 'nullable|string|max:20',
            'bus_assignments.*.attention' => 'nullable|string',
            'bus_assignments.*.operation_remarks' => 'nullable|string',
            'bus_assignments.*.operation_memo' => 'nullable|string',
            'bus_assignments.*.operation_basic_remarks' => 'nullable|string',
            'bus_assignments.*.doc_remarks' => 'nullable|string',
            'bus_assignments.*.history_remarks' => 'nullable|string',
            'bus_assignments.*.lock_arrangement' => 'nullable|boolean',
            'bus_assignments.*.status_sent' => 'nullable|boolean',
            'bus_assignments.*.status_finalized' => 'nullable|boolean',
            'bus_assignments.*.vehicle_index' => 'required|integer|min:1',
            
            'daily_itineraries' => 'sometimes|array',
            'daily_itineraries.*.id' => 'nullable|integer|exists:daily_itinerary,id',
            'daily_itineraries.*.date' => 'required|date',
            'daily_itineraries.*.time_start' => 'nullable|date_format:H:i',
            'daily_itineraries.*.time_end' => 'nullable|date_format:H:i',
            'daily_itineraries.*.start_location' => 'nullable|string|max:255',
            'daily_itineraries.*.end_location' => 'nullable|string|max:255',
            'daily_itineraries.*.itinerary' => 'nullable|string',
            'daily_itineraries.*.vehicle_id' => 'nullable|exists:vehicles,id',
            'daily_itineraries.*.driver_id' => 'nullable|exists:drivers,id',
            'daily_itineraries.*.accommodation' => 'nullable|boolean',
            'daily_itineraries.*.remarks' => 'nullable|string|max:255',
            'daily_itineraries.*.display_order' => 'required|integer|min:1',
            'daily_itineraries.*.vehicle_group' => 'nullable|integer',
        ];
    
        $messages = [
            'key_uuid.required' => '予約IDは必須です。',
            'start_date.required' => '開始日は必須です。',
            'end_date.required' => '終了日は必須です。',
            'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
            'vehicle_id.exists' => '選択された車両は存在しません。',
            'guide_id.exists' => '選択されたガイドは存在しません。',
            'driver_id.exists' => '選択された運転手は存在しません。',
            'agency_id.exists' => '選択された代理店は存在しません。',
            'adult_count.integer' => '大人人数は数値で入力してください。',
            'child_count.integer' => '子供人数は数値で入力してください。',
            'guide_count.integer' => 'ガイド人数は数値で入力してください。',
            'other_count.integer' => 'その他人数は数値で入力してください。',
            'luggage_count.integer' => '荷物数は数値で入力してください。',
            'daily_itineraries.*.date.required' => 'すべての旅程の日付は必須です。',
            'daily_itineraries.*.vehicle_id.exists' => '選択された車両は存在しません。',
            'daily_itineraries.*.driver_id.exists' => '選択された運転手は存在しません。',
        ];
    
        $validated = $request->validate($rules, $messages);
    
        $validated['ignore_operation'] = $request->has('ignore_operation');
        $validated['ignore_attendance'] = $request->has('ignore_attendance');
    
        try {
            DB::beginTransaction();
    
            $userId = session('user_id', auth()->id() ?? 0);
            
            $vehicleInfo = null;
            if (!empty($request->vehicle_id)) {
                $vehicleInfo = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                                     ->find($request->vehicle_id);
            }
    
            $driverInfo = null;
            if (!empty($request->driver_id)) {
                $driverInfo = Driver::find($request->driver_id);
            }
    
            $guideInfo = null;
            if (!empty($request->guide_id)) {
                $guideInfo = Guide::find($request->guide_id);
            }
    
            $agencyInfo = null;
            if (!empty($request->agency_id)) {
                $agencyInfo = Agency::find($request->agency_id);
            }
    
            $existingBusUuids = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)
                                            ->pluck('key_uuid')
                                            ->toArray();
            $submittedBusUuids = [];
    
            if ($request->has('bus_assignments') && is_array($request->bus_assignments)) {
                foreach ($request->bus_assignments as $busData) {
                    $busUuid = $busData['key_uuid'] ?? (string) Str::uuid();
                    $submittedBusUuids[] = $busUuid;
                    
                    $firstItinerary = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                                    ->where('vehicle_id', $busData['vehicle_id'] ?? null)
                                                    ->orderBy('date', 'asc')
                                                    ->first();
                    
                    $lastItinerary = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                                   ->where('vehicle_id', $busData['vehicle_id'] ?? null)
                                                   ->orderBy('date', 'desc')
                                                   ->first();
                    
                    $busStartDate = $firstItinerary ? $firstItinerary->date : $validated['start_date'];
                    $busEndDate = $lastItinerary ? $lastItinerary->date : $validated['end_date'];
                    
                    $busStartTime = null;
                    $busEndTime = null;
                    if ($firstItinerary && $firstItinerary->time_start) {
                        $busStartTime = $firstItinerary->time_start;
                    }
                    if ($lastItinerary && $lastItinerary->time_end) {
                        $busEndTime = $lastItinerary->time_end;
                    }
                    
                    $itineraryCount = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                                   ->where('vehicle_id', $busData['vehicle_id'] ?? null)
                                                   ->count();
                    
                    $busAssignmentData = [
                        'key_uuid' => $busUuid,
                        'yoyaku_uuid' => $groupInfo->key_uuid,
                        'vehicle_id' => $busData['vehicle_id'] ?? null,
                        'driver_id' => $busData['driver_id'] ?? null,
                        'start_date' => $busStartDate,
                        'start_time' => $busStartTime,
                        'end_date' => $busEndDate,
                        'end_time' => $busEndTime,
                        'lock_arrangement' => isset($busData['lock_arrangement']) ? (bool)$busData['lock_arrangement'] : false,
                        'status_sent' => isset($busData['status_sent']) ? (bool)$busData['status_sent'] : false,
                        'status_finalized' => isset($busData['status_finalized']) ? (bool)$busData['status_finalized'] : false,
                        'count_daily' => $itineraryCount,
                        'vehicle_number' => $busData['vehicle_number'] ?? sprintf('%02d', $busData['vehicle_index'] ?? 1),
                        'step_car' => $busData['step_car'] ?? null,
                        'adult_count' => $busData['adult_count'] ?? 0,
                        'child_count' => $busData['child_count'] ?? 0,
                        'guide_count' => $busData['guide_count'] ?? 0,
                        'other_count' => $busData['other_count'] ?? 0,
                        'luggage_count' => $busData['luggage_count'] ?? 0,
                        'vehicle_type_spec_check' => isset($busData['vehicle_type_spec_check']) ? (bool)$busData['vehicle_type_spec_check'] : false,
                        'temporary_driver' => isset($busData['temporary_driver']) ? (bool)$busData['temporary_driver'] : false,
                        'accompanying' => $busData['accompanying'] ?? null,
                        'representative' => $busData['representative'] ?? null,
                        'representative_phone' => $busData['representative_phone'] ?? null,
                        'attention' => $busData['attention'] ?? null,
                        'operation_remarks' => $busData['operation_remarks'] ?? null,
                        'operation_memo' => $busData['operation_memo'] ?? null,
                        'operation_basic_remarks' => $busData['operation_basic_remarks'] ?? null,
                        'doc_remarks' => $busData['doc_remarks'] ?? null,
                        'history_remarks' => $busData['history_remarks'] ?? null,
                        'vehicle_index' => $busData['vehicle_index'] ?? 1,
                        'updated_by' => $userId,
                        'updated_at' => now(),
                    ];
    
                    $existingBus = BusAssignment::where('key_uuid', $busUuid)->first();
                    if ($existingBus) {
                        $busAssignmentData['created_by'] = $existingBus->created_by;
                        $busAssignmentData['created_at'] = $existingBus->created_at;
                        $existingBus->update($busAssignmentData);
                    } else {
                        $busAssignmentData['created_by'] = $userId;
                        $busAssignmentData['created_at'] = now();
                        BusAssignment::create($busAssignmentData);
                    }
                }
            }
    
            $uuidsToDelete = array_diff($existingBusUuids, $submittedBusUuids);
            if (!empty($uuidsToDelete)) {
                BusAssignment::whereIn('key_uuid', $uuidsToDelete)->delete();
            }
    
            $busAssignmentMap = [];
            $busAssignments = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)->get();
            foreach ($busAssignments as $bus) {
                if ($bus->vehicle_id) {
                    $busAssignmentMap[$bus->vehicle_id] = $bus->key_uuid;
                }
            }
    
            $existingItineraryIds = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                                  ->pluck('id')
                                                  ->toArray();
            $submittedItineraryIds = [];
    
            $minStartDate = null;
            $maxEndDate = null;
            $minStartTime = null;
            $maxEndTime = null;
    
            if ($request->has('daily_itineraries') && is_array($request->daily_itineraries)) {
                $itinerariesByVehicle = [];
                
                foreach ($request->daily_itineraries as $itineraryData) {
                    $vehicleId = $itineraryData['vehicle_id'] ?? null;
                    if ($vehicleId) {
                        if (!isset($itinerariesByVehicle[$vehicleId])) {
                            $itinerariesByVehicle[$vehicleId] = [];
                        }
                        $itinerariesByVehicle[$vehicleId][] = $itineraryData;
                    }
                    
                    $currentDate = $itineraryData['date'];
                    $currentStartTime = !empty($itineraryData['time_start']) ? $itineraryData['time_start'] . ':00' : null;
                    $currentEndTime = !empty($itineraryData['time_end']) ? $itineraryData['time_end'] . ':00' : null;
                    
                    if (!$minStartDate || $currentDate < $minStartDate) {
                        $minStartDate = $currentDate;
                        $minStartTime = $currentStartTime;
                    } elseif ($currentDate == $minStartDate && $currentStartTime && (!$minStartTime || $currentStartTime < $minStartTime)) {
                        $minStartTime = $currentStartTime;
                    }
                    
                    if (!$maxEndDate || $currentDate > $maxEndDate) {
                        $maxEndDate = $currentDate;
                        $maxEndTime = $currentEndTime;
                    } elseif ($currentDate == $maxEndDate && $currentEndTime && (!$maxEndTime || $currentEndTime > $maxEndTime)) {
                        $maxEndTime = $currentEndTime;
                    }
                }
                
                foreach ($itinerariesByVehicle as $vehicleId => $vehicleItineraries) {
                    for ($i = 0; $i < count($vehicleItineraries); $i++) {
                        for ($j = $i + 1; $j < count($vehicleItineraries); $j++) {
                            $itinerary1 = $vehicleItineraries[$i];
                            $itinerary2 = $vehicleItineraries[$j];
                            
                            if ($itinerary1['date'] !== $itinerary2['date']) {
                                continue;
                            }
                            
                            $time1Start = !empty($itinerary1['time_start']) ? $itinerary1['time_start'] . ':00' : null;
                            $time1End = !empty($itinerary1['time_end']) ? $itinerary1['time_end'] . ':00' : null;
                            $time2Start = !empty($itinerary2['time_start']) ? $itinerary2['time_start'] . ':00' : null;
                            $time2End = !empty($itinerary2['time_end']) ? $itinerary2['time_end'] . ':00' : null;
                            
                            if ($time1Start && $time1End && $time2Start && $time2End) {
                                $start1 = Carbon::parse($itinerary1['date'] . ' ' . $time1Start);
                                $end1 = Carbon::parse($itinerary1['date'] . ' ' . $time1End);
                                $start2 = Carbon::parse($itinerary2['date'] . ' ' . $time2Start);
                                $end2 = Carbon::parse($itinerary2['date'] . ' ' . $time2End);
                                
                                if ($start1->lt($end2) && $end1->gt($start2)) {
                                    throw new \Exception('同じ車両で同じ日に重複する時間帯の行程は保存できません。');
                                }
                            }
                        }
                    }
                }
    
                foreach ($request->daily_itineraries as $index => $itineraryData) {
                    $itineraryId = $itineraryData['id'] ?? null;
                    $displayOrder = $itineraryData['display_order'] ?? ($index + 1);
                    
                    $timeStart = !empty($itineraryData['time_start']) ? $itineraryData['time_start'] . ':00' : null;
                    $timeEnd = !empty($itineraryData['time_end']) ? $itineraryData['time_end'] . ':00' : null;
                    
                    $vehicleId = $itineraryData['vehicle_id'] ?? null;
                    $busAssUuid = $vehicleId ? ($busAssignmentMap[$vehicleId] ?? null) : null;
                    
                    $vehicleName = '';
                    if (!empty($vehicleId)) {
                        $vehicle = Vehicle::find($vehicleId);
                        $vehicleName = $vehicle ? $vehicle->registration_number : '';
                    }
                    
                    $driverName = '';
                    if (!empty($itineraryData['driver_id'])) {
                        $driver = Driver::find($itineraryData['driver_id']);
                        $driverName = $driver ? $driver->name : '';
                    }
                    
                    $itineraryFields = [
                        'key_uuid' => $groupInfo->key_uuid,
                        'date' => $itineraryData['date'],
                        'time_start' => $timeStart,
                        'time_end' => $timeEnd,
                        'itinerary' => $itineraryData['itinerary'] ?? '',
                        'start_location' => $itineraryData['start_location'] ?? null,
                        'end_location' => $itineraryData['end_location'] ?? null,
                        'accommodation' => isset($itineraryData['accommodation']) ? (bool)$itineraryData['accommodation'] : false,
                        'vehicle_id' => $vehicleId,
                        'vehicle' => $vehicleName ?: ($itineraryData['vehicle'] ?? ''),
                        'driver_id' => $itineraryData['driver_id'] ?? null,
                        'driver' => $driverName ?: ($itineraryData['driver'] ?? ''),
                        'guide' => '',
                        'remarks' => $itineraryData['remarks'] ?? null,
                        'bus_ass_uuid' => $busAssUuid,
                        'updated_at' => now(),
                        'updated_by' => $userId,
                    ];
    
                    if (!empty($itineraryId)) {
                        $itinerary = DailyItinerary::find($itineraryId);
                        if ($itinerary) {
                            $itinerary->update($itineraryFields);
                            $submittedItineraryIds[] = $itineraryId;
                        }
                    } else {
                        $itineraryFields['created_at'] = now();
                        $itineraryFields['created_by'] = $userId;
                        $newItinerary = DailyItinerary::create($itineraryFields);
                        $submittedItineraryIds[] = $newItinerary->id;
                    }
                }
            }
    
            $idsToDelete = array_diff($existingItineraryIds, $submittedItineraryIds);
            if (!empty($idsToDelete)) {
                DailyItinerary::whereIn('id', $idsToDelete)->delete();
            }
    
            $updateData = [
                'agency' => $validated['agency_name_input'] ?? $validated['agency'] ?? $groupInfo->agency,
                'agency_code' => $validated['agency_code'] ?? ($agencyInfo->agency_code ?? $groupInfo->agency_code),
                'agency_branch' => $validated['agency_branch'] ?? ($agencyInfo->branch_name ?? $groupInfo->agency_branch),
                'agency_phone' => $validated['agency_phone'] ?? ($agencyInfo->phone_number ?? $groupInfo->agency_phone),
                'agency_contact_name' => $validated['agency_contact_name'] ?? ($agencyInfo->manager_name ?? $groupInfo->agency_contact_name),
                'agency_country' => $validated['agency_country'] ?? ($agencyInfo->country ?? $groupInfo->agency_country),
                'reservation_status' => $validated['reservation_status'] ?? '',
                'start_date' => $minStartDate ?? $validated['start_date'],
                'start_time' => $minStartTime,
                'end_date' => $maxEndDate ?? $validated['end_date'],
                'end_time' => $maxEndTime,
                'vehicle_type_selection' => $validated['vehicle_type_selection'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'agt_tour_id' => $validated['agt_tour_id'] ?? null,
                'business_category' => $validated['business_category'] ?? null,
                'adult_count' => $validated['adult_count'] ?? 0,
                'child_count' => $validated['child_count'] ?? 0,
                'other_count' => $validated['other_count'] ?? 0,
                'luggage_count' => $validated['luggage_count'] ?? 0,
                'reservation_channel' => $validated['reservation_channel'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? ($vehicleInfo->vehicleType->type_name ?? $groupInfo->vehicle_type),
                'vehicle_model' => $validated['vehicle_model'] ?? ($vehicleInfo->vehicleModel->model_name ?? $groupInfo->vehicle_model),
                'vehicle' => $validated['vehicle_name_input'] ?? $validated['vehicle'] ?? ($vehicleInfo->registration_number ?? $groupInfo->vehicle),
                'vehicle_number' => $validated['vehicle_number'] ?? $groupInfo->vehicle_number,
                'driver' => $validated['driver_name_input'] ?? $validated['driver'] ?? ($driverInfo->name ?? $groupInfo->driver),
                'guide' => $validated['guide_name_input'] ?? $validated['guide'] ?? ($guideInfo->name ?? $groupInfo->guide),
                'vehicle_branch' => $validated['vehicle_branch'] ?? ($vehicleInfo->branch->branch_name ?? $groupInfo->vehicle_branch),
                'guide_count' => $validated['guide_count'] ?? $groupInfo->guide_count,
                'ignore_operation' => $validated['ignore_operation'] ? 1 : 0,
                'ignore_attendance' => $validated['ignore_attendance'] ? 1 : 0,
                'updated_at' => now(),
                'updated_by' => $userId,
            ];
    
            $groupInfo->update($updateData);
    
            DB::commit();
    
            if ($request->input('iframe') == '1') {
                return response()->json([
                    'success' => true,
                    'id' => $groupInfo->id,
                    'message' => 'グループ情報を更新しました。',
                    'redirect' => route('masters.group-infos.edit', $groupInfo->id)
                ]);
            }
    
            return redirect()->route('masters.group-infos.edit', $groupInfo->id)
                ->with([
                    'success' => 'グループ情報を更新しました。',
                    'alert-type' => 'success'
                ]);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->input('iframe') == '1') {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [
                        'system' => [$e->getMessage()]
                    ]
                ], 422);
            }
            
            return redirect()->back()
                ->withInput()
                ->with([
                    'error' => '更新中にエラーが発生しました: ' . $e->getMessage(),
                    'alert-type' => 'danger'
                ]);
        }
    }

    public function destroy($id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        try {
            DB::beginTransaction();
            
            $keyUuid = $groupInfo->key_uuid;
            
            DailyItinerary::where('key_uuid', $keyUuid)->delete();
            
            BusAssignment::where('yoyaku_uuid', $keyUuid)->delete();
            
            $groupInfo->delete();
            
            DB::commit();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'グループと関連データを削除しました。'
                ]);
            }
            
            return redirect()->route('masters.group-infos.index')
                ->with('success', 'グループと関連データを削除しました。');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '削除中にエラーが発生しました: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', '削除中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    public function getVehicleInfo($id)
    {
        $vehicle = Vehicle::with(['vehicleType', 'vehicleModel', 'branch'])
                         ->findOrFail($id);
        
        return response()->json([
            'id' => $vehicle->id,
            'registration_number' => $vehicle->registration_number,
            'vehicle_code' => $vehicle->vehicle_code,
            'vehicle_type' => $vehicle->vehicleType->type_name ?? null,
            'vehicle_model' => $vehicle->vehicleModel->model_name ?? null,
            'vehicle_branch' => $vehicle->branch->branch_name ?? null,
            'seating_capacity' => $vehicle->seating_capacity,
            'display_name' => $vehicle->registration_number . ' (' . ($vehicle->vehicleModel->model_name ?? '') . ')'
        ]);
    }
}