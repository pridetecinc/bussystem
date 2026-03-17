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
use Illuminate\Support\Facades\Log;
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

    if (strpos($startDate, ' ') !== false) {
        $newStart = Carbon::parse($startDate);
    } else {
        $newStart = Carbon::parse($startDate . ' ' . ($startTime ?? '00:00:00'));
    }

    if (strpos($endDate, ' ') !== false) {
        $newEnd = Carbon::parse($endDate);
    } else {
        $newEnd = Carbon::parse($endDate . ' ' . ($endTime ?? '23:59:59'));
    }

    if (!empty($vehicleId)) {
        $existingAssignments = BusAssignment::where('vehicle_id', $vehicleId)
            ->when($excludeUuid, function($query) use ($excludeUuid) {
                return $query->where('key_uuid', '!=', $excludeUuid);
            })
            ->get();

        foreach ($existingAssignments as $assignment) {
            if (strpos($assignment->start_date, ' ') !== false) {
                $existingStart = Carbon::parse($assignment->start_date);
            } else {
                $existingStart = Carbon::parse($assignment->start_date . ' ' . ($assignment->start_time ?? '00:00:00'));
            }

            if (strpos($assignment->end_date, ' ') !== false) {
                $existingEnd = Carbon::parse($assignment->end_date);
            } else {
                $existingEnd = Carbon::parse($assignment->end_date . ' ' . ($assignment->end_time ?? '23:59:59'));
            }

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
            if (strpos($assignment->start_date, ' ') !== false) {
                $existingStart = Carbon::parse($assignment->start_date);
            } else {
                $existingStart = Carbon::parse($assignment->start_date . ' ' . ($assignment->start_time ?? '00:00:00'));
            }

            if (strpos($assignment->end_date, ' ') !== false) {
                $existingEnd = Carbon::parse($assignment->end_date);
            } else {
                $existingEnd = Carbon::parse($assignment->end_date . ' ' . ($assignment->end_time ?? '23:59:59'));
            }

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
                } else {
                    $dailyItineraryData['vehicle_id'] = 0;
                }
                
                if (!empty($request->driver_id)) {
                    $dailyItineraryData['driver_id'] = $request->driver_id;
                } else {
                    $dailyItineraryData['driver_id'] = 0;
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
    
    Log::info('===== edit画面表示 =====');
    Log::info('グループID: ' . $id);
    Log::info('キーUUID: ' . $groupInfo->key_uuid);
    Log::info('BusAssignment数: ' . $busAssignments->count());
    foreach ($busAssignments as $bus) {
        Log::info('Bus: ' . $bus->key_uuid . ', 車両ID: ' . $bus->vehicle_id . ', インデックス: ' . $bus->vehicle_index);
    }
    
    $allItineraries = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                    ->orderBy('date', 'asc')
                                    ->orderBy('id', 'asc')
                                    ->get();
    
    Log::info('行程数: ' . $allItineraries->count());
    foreach ($allItineraries as $it) {
        Log::info('行程: ID=' . $it->id . ', bus_ass_uuid=' . ($it->bus_ass_uuid ?? 'なし') . ', vehicle_id=' . ($it->vehicle_id ?? 'なし'));
    }
    
    $groupedItineraries = [];
    $uniqueVehicles = [];
    
    foreach ($allItineraries as $itinerary) {
        $groupKey = $itinerary->bus_ass_uuid ?? ($itinerary->vehicle_id ?? 0);
        
        if (!isset($groupedItineraries[$groupKey])) {
            $vehicleInfo = null;
            if ($itinerary->vehicle_id && $itinerary->vehicle_id > 0) {
                $vehicleInfo = $vehicles->firstWhere('id', $itinerary->vehicle_id);
            }
            
            $busAssignment = null;
            if ($itinerary->bus_ass_uuid) {
                $busAssignment = $busAssignments->firstWhere('key_uuid', $itinerary->bus_ass_uuid);
            }
            
            $groupedItineraries[$groupKey] = [
                'vehicle_id' => $itinerary->vehicle_id,
                'vehicle_name' => $vehicleInfo ? $vehicleInfo->registration_number : ($itinerary->vehicle ?? '未分配车辆'),
                'vehicle_model' => $vehicleInfo && $vehicleInfo->vehicleModel ? $vehicleInfo->vehicleModel->model_name : '',
                'driver_name' => $itinerary->driver ?? '',
                'bus_assignment' => $busAssignment,
                'itineraries' => []
            ];
            
            if ($itinerary->vehicle_id && $itinerary->vehicle_id > 0 && !in_array($itinerary->vehicle_id, array_column($uniqueVehicles, 'id'))) {
                $uniqueVehicles[] = [
                    'id' => $itinerary->vehicle_id,
                    'name' => $vehicleInfo ? $vehicleInfo->registration_number : $itinerary->vehicle,
                    'model' => $vehicleInfo && $vehicleInfo->vehicleModel ? $vehicleInfo->vehicleModel->model_name : ''
                ];
            }
        }
        
        $groupedItineraries[$groupKey]['itineraries'][] = $itinerary;
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
    
    Log::info('グループ化された運行詳細数: ' . count($groupedItineraries));
    
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
        'bus_assignments.*.vehicle_id' => [
            'required',
            function ($attribute, $value, $fail) {
                if (!empty($value) && !Vehicle::where('id', $value)->exists()) {
                    $fail('選択された車両は存在しません。');
                }
            },
        ],
        'bus_assignments.*.driver_id' => [
            'required',
            function ($attribute, $value, $fail) {
                if (!empty($value) && !Driver::where('id', $value)->exists()) {
                    $fail('選択された運転手は存在しません。');
                }
            },
        ],
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
        'daily_itineraries.*.time_start' => 'required|date_format:H:i',
        'daily_itineraries.*.time_end' => 'required|date_format:H:i',
        'daily_itineraries.*.start_location' => 'nullable|string|max:255',
        'daily_itineraries.*.end_location' => 'nullable|string|max:255',
        'daily_itineraries.*.itinerary' => 'nullable|string',
        'daily_itineraries.*.vehicle_id' => [
            'required',
            function ($attribute, $value, $fail) {
                if (!empty($value) && !Vehicle::where('id', $value)->exists()) {
                    $fail('選択された車両は存在しません。');
                }
            },
        ],
        'daily_itineraries.*.driver_id' => [
            'required',
            function ($attribute, $value, $fail) {
                if (!empty($value) && !Driver::where('id', $value)->exists()) {
                    $fail('選択された運転手は存在しません。');
                }
            },
        ],
        'daily_itineraries.*.bus_ass_uuid' => 'nullable|string|max:200',
        'daily_itineraries.*.accommodation' => 'nullable|boolean',
        'daily_itineraries.*.remarks' => 'nullable|string|max:255',
        'daily_itineraries.*.display_order' => 'required|integer|min:1',
        'daily_itineraries.*.vehicle_group' => 'nullable|integer',
        
        'deleted_itineraries' => 'sometimes|array',
        'deleted_itineraries.*' => 'integer|exists:daily_itinerary,id',
    ];

    $messages = [
        'key_uuid.required' => '予約IDは必須です。',
        'start_date.required' => '開始日は必須です。',
        'end_date.required' => '終了日は必須です。',
        'end_date.after_or_equal' => '終了日は開始日以降の日付を入力してください。',
        'vehicle_id.required' => '車両を選択してください。',
        'driver_id.required' => '運転手を選択してください。',
        'bus_assignments.*.vehicle_id.required' => 'すべての運行詳細で車両を選択してください。',
        'bus_assignments.*.driver_id.required' => 'すべての運行詳細で運転手を選択してください。',
        'daily_itineraries.*.date.required' => 'すべての旅程の運行日は必須です。',
        'daily_itineraries.*.time_start.required' => 'すべての旅程の開始時刻は必須です。',
        'daily_itineraries.*.time_end.required' => 'すべての旅程の終了時刻は必須です。',
        'daily_itineraries.*.time_start.date_format' => '開始時刻はH:i形式で入力してください（例: 09:00）。',
        'daily_itineraries.*.time_end.date_format' => '終了時刻はH:i形式で入力してください（例: 18:00）。',
        'daily_itineraries.*.vehicle_id.required' => 'すべての旅程で車両を選択してください。',
        'daily_itineraries.*.driver_id.required' => 'すべての旅程で運転手を選択してください。',
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

        if ($request->has('deleted_itineraries')) {
            $deletedIds = $request->input('deleted_itineraries');
            if (is_array($deletedIds) && !empty($deletedIds)) {
                DailyItinerary::whereIn('id', $deletedIds)
                             ->where('key_uuid', $groupInfo->key_uuid)
                             ->delete();
            }
        }

        $existingBusUuids = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)
                                        ->pluck('key_uuid')
                                        ->toArray();

        $hasExistingBusAssignments = !empty($existingBusUuids);
        $hasVehicleInBusAssignments = false;
        
        if ($request->has('bus_assignments') && is_array($request->bus_assignments)) {
            foreach ($request->bus_assignments as $busData) {
                if (!empty($busData['vehicle_id'])) {
                    $hasVehicleInBusAssignments = true;
                    break;
                }
            }
        }
        
        if (!$hasExistingBusAssignments && $hasVehicleInBusAssignments) {
            $firstBusData = null;
            foreach ($request->bus_assignments as $busData) {
                $firstBusData = $busData;
                break;
            }
            
            if ($firstBusData && !empty($firstBusData['vehicle_id'])) {
                $newBusUuid = (string) Str::uuid();
                
                $itineraries = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                            ->orderBy('date', 'asc')
                                            ->get();
                
                $firstItinerary = $itineraries->first();
                $lastItinerary = $itineraries->last();
                
                $vehicleName = '';
                if (!empty($firstBusData['vehicle_id'])) {
                    $vehicle = Vehicle::find($firstBusData['vehicle_id']);
                    $vehicleName = $vehicle ? $vehicle->registration_number : '';
                }
                
                $driverName = '';
                if (!empty($firstBusData['driver_id'])) {
                    $driver = Driver::find($firstBusData['driver_id']);
                    $driverName = $driver ? $driver->name : '';
                }
                
                $busAssignmentData = [
                    'key_uuid' => $newBusUuid,
                    'yoyaku_uuid' => $groupInfo->key_uuid,
                    'daily_itinerary_id' => $firstItinerary ? $firstItinerary->id : null,
                    'vehicle_id' => !empty($firstBusData['vehicle_id']) && $firstBusData['vehicle_id'] > 0 ? $firstBusData['vehicle_id'] : null,
                    'driver_id' => $firstBusData['driver_id'] ?? null,
                    'start_date' => $groupInfo->start_date,
                    'start_time' => $groupInfo->start_time,
                    'end_date' => $groupInfo->end_date,
                    'end_time' => $groupInfo->end_time,
                    'lock_arrangement' => isset($firstBusData['lock_arrangement']) ? (bool)$firstBusData['lock_arrangement'] : false,
                    'status_sent' => isset($firstBusData['status_sent']) ? (bool)$firstBusData['status_sent'] : false,
                    'status_finalized' => isset($firstBusData['status_finalized']) ? (bool)$firstBusData['status_finalized'] : false,
                    'count_daily' => $itineraries->count(),
                    'vehicle_number' => $firstBusData['vehicle_number'] ?? '01',
                    'step_car' => $firstBusData['step_car'] ?? null,
                    'adult_count' => $firstBusData['adult_count'] ?? $groupInfo->adult_count ?? 0,
                    'child_count' => $firstBusData['child_count'] ?? $groupInfo->child_count ?? 0,
                    'guide_count' => $firstBusData['guide_count'] ?? 0,
                    'other_count' => $firstBusData['other_count'] ?? 0,
                    'luggage_count' => $firstBusData['luggage_count'] ?? 0,
                    'vehicle_type_spec_check' => isset($firstBusData['vehicle_type_spec_check']) ? (bool)$firstBusData['vehicle_type_spec_check'] : false,
                    'temporary_driver' => isset($firstBusData['temporary_driver']) ? (bool)$firstBusData['temporary_driver'] : false,
                    'accompanying' => $firstBusData['accompanying'] ?? null,
                    'representative' => $firstBusData['representative'] ?? null,
                    'representative_phone' => $firstBusData['representative_phone'] ?? null,
                    'attention' => $firstBusData['attention'] ?? null,
                    'operation_remarks' => $firstBusData['operation_remarks'] ?? null,
                    'operation_memo' => $firstBusData['operation_memo'] ?? null,
                    'operation_basic_remarks' => $firstBusData['operation_basic_remarks'] ?? null,
                    'doc_remarks' => $firstBusData['doc_remarks'] ?? null,
                    'history_remarks' => $firstBusData['history_remarks'] ?? null,
                    'vehicle_index' => $firstBusData['vehicle_index'] ?? 1,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $newBus = BusAssignment::create($busAssignmentData);
                
                foreach ($itineraries as $itinerary) {
                    $itinerary->update([
                        'bus_ass_uuid' => $newBusUuid,
                        'vehicle_id' => $firstBusData['vehicle_id'],
                        'vehicle' => $vehicleName,
                        'driver_id' => $firstBusData['driver_id'] ?? 0,
                        'driver' => $driverName,
                    ]);
                }
                
                $request->merge([
                    'bus_assignments' => [
                        [
                            'key_uuid' => $newBusUuid,
                            'vehicle_id' => $firstBusData['vehicle_id'],
                            'driver_id' => $firstBusData['driver_id'] ?? null,
                            'vehicle_number' => $firstBusData['vehicle_number'] ?? '01',
                            'step_car' => $firstBusData['step_car'] ?? null,
                            'adult_count' => $firstBusData['adult_count'] ?? $groupInfo->adult_count ?? 0,
                            'child_count' => $firstBusData['child_count'] ?? $groupInfo->child_count ?? 0,
                            'guide_count' => $firstBusData['guide_count'] ?? 0,
                            'other_count' => $firstBusData['other_count'] ?? 0,
                            'luggage_count' => $firstBusData['luggage_count'] ?? 0,
                            'vehicle_type_spec_check' => $firstBusData['vehicle_type_spec_check'] ?? 0,
                            'temporary_driver' => $firstBusData['temporary_driver'] ?? 0,
                            'accompanying' => $firstBusData['accompanying'] ?? null,
                            'representative' => $firstBusData['representative'] ?? null,
                            'representative_phone' => $firstBusData['representative_phone'] ?? null,
                            'attention' => $firstBusData['attention'] ?? null,
                            'operation_remarks' => $firstBusData['operation_remarks'] ?? null,
                            'operation_memo' => $firstBusData['operation_memo'] ?? null,
                            'operation_basic_remarks' => $firstBusData['operation_basic_remarks'] ?? null,
                            'doc_remarks' => $firstBusData['doc_remarks'] ?? null,
                            'history_remarks' => $firstBusData['history_remarks'] ?? null,
                            'lock_arrangement' => $firstBusData['lock_arrangement'] ?? 0,
                            'status_sent' => $firstBusData['status_sent'] ?? 0,
                            'status_finalized' => $firstBusData['status_finalized'] ?? 0,
                            'vehicle_index' => $firstBusData['vehicle_index'] ?? 1,
                        ]
                    ]
                ]);
                
                $existingBusUuids = [$newBusUuid];
                
                $updatedItineraries = [];
                foreach ($request->daily_itineraries as $idx => $itineraryData) {
                    $itineraryData['bus_ass_uuid'] = $newBusUuid;
                    $updatedItineraries[] = $itineraryData;
                }
                $request->merge(['daily_itineraries' => $updatedItineraries]);
            }
        }

        $submittedBusUuids = [];
        $busAssignmentDataArray = [];

        if ($request->has('bus_assignments') && is_array($request->bus_assignments)) {
            foreach ($request->bus_assignments as $busData) {
                $busUuid = $busData['key_uuid'] ?? (string) Str::uuid();
                $submittedBusUuids[] = $busUuid;
                
                $busAssignmentDataArray[] = [
                    'key_uuid' => $busUuid,
                    'yoyaku_uuid' => $groupInfo->key_uuid,
                    'vehicle_id' => !empty($busData['vehicle_id']) && $busData['vehicle_id'] > 0 ? $busData['vehicle_id'] : null,
                    'driver_id' => $busData['driver_id'] ?? null,
                    'lock_arrangement' => isset($busData['lock_arrangement']) ? (bool)$busData['lock_arrangement'] : false,
                    'status_sent' => isset($busData['status_sent']) ? (bool)$busData['status_sent'] : false,
                    'status_finalized' => isset($busData['status_finalized']) ? (bool)$busData['status_finalized'] : false,
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
            }
        }

        $busAssignmentsToKeep = [];
        foreach ($busAssignmentDataArray as $busData) {
            $busUuid = $busData['key_uuid'];
            $busAssignmentsToKeep[] = $busUuid;
            
            $existingBus = BusAssignment::where('key_uuid', $busUuid)->first();
            if ($existingBus) {
                $busData['created_by'] = $existingBus->created_by;
                $busData['created_at'] = $existingBus->created_at;
                $existingBus->update($busData);
            } else {
                $busData['created_by'] = $userId;
                $busData['created_at'] = now();
                BusAssignment::create($busData);
            }
        }
        
        $busAssignmentsToDelete = array_diff($existingBusUuids, $submittedBusUuids);

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
            $itinerariesById = [];
            foreach ($request->daily_itineraries as $index => $itineraryData) {
                $itineraryId = $itineraryData['id'] ?? null;
                if ($itineraryId) {
                    $itinerariesById[$itineraryId] = $itineraryData;
                    $submittedItineraryIds[] = $itineraryId;
                }
            }
            
            $allExistingItineraries = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)->get();
            
            foreach ($allExistingItineraries as $existingItinerary) {
                $itineraryId = $existingItinerary->id;
                
                if (isset($itinerariesById[$itineraryId])) {
                    $itineraryData = $itinerariesById[$itineraryId];
                    
                    $timeStart = $itineraryData['time_start'] . ':00';
                    $timeEnd = $itineraryData['time_end'] . ':00';
                    
                    $vehicleId = $itineraryData['vehicle_id'] ?? 0;
                    $driverId = $itineraryData['driver_id'] ?? 0;
                    $busAssUuid = $itineraryData['bus_ass_uuid'] ?? null;
                    
                    $vehicleName = '';
                    if ($vehicleId > 0) {
                        $vehicle = Vehicle::find($vehicleId);
                        $vehicleName = $vehicle ? $vehicle->registration_number : '';
                    }
                    
                    $driverName = '';
                    if ($driverId > 0) {
                        $driver = Driver::find($driverId);
                        $driverName = $driver ? $driver->name : '';
                    }
                    
                    $finalVehicleId = ($vehicleId > 0) ? $vehicleId : $existingItinerary->vehicle_id;
                    $finalDriverId = ($driverId > 0) ? $driverId : $existingItinerary->driver_id;
                    
                    $currentDate = $itineraryData['date'];
                    $currentStartTime = $timeStart;
                    $currentEndTime = $timeEnd;
                    
                    if (!$minStartDate || $currentDate < $minStartDate) {
                        $minStartDate = $currentDate;
                        $minStartTime = $currentStartTime;
                    }
                    
                    if (!$maxEndDate || $currentDate > $maxEndDate) {
                        $maxEndDate = $currentDate;
                        $maxEndTime = $currentEndTime;
                    }
                    
                    $itineraryFields = [
                        'key_uuid' => $groupInfo->key_uuid,
                        'date' => $itineraryData['date'],
                        'time_start' => $timeStart,
                        'time_end' => $timeEnd,
                        'itinerary' => $itineraryData['itinerary'] ?? $existingItinerary->itinerary,
                        'start_location' => $itineraryData['start_location'] ?? $existingItinerary->start_location,
                        'end_location' => $itineraryData['end_location'] ?? $existingItinerary->end_location,
                        'accommodation' => isset($itineraryData['accommodation']) ? (bool)$itineraryData['accommodation'] : $existingItinerary->accommodation,
                        'vehicle_id' => $finalVehicleId,
                        'vehicle' => $vehicleName ?: $existingItinerary->vehicle,
                        'driver_id' => $finalDriverId,
                        'driver' => $driverName ?: $existingItinerary->driver,
                        'guide' => $existingItinerary->guide,
                        'remarks' => $itineraryData['remarks'] ?? $existingItinerary->remarks,
                        'bus_ass_uuid' => $itineraryData['bus_ass_uuid'] ?? $existingItinerary->bus_ass_uuid,
                        'updated_at' => now(),
                        'updated_by' => $userId,
                    ];

                    $existingItinerary->update($itineraryFields);
                } else {
                    $currentDate = $existingItinerary->date;
                    
                    if (!$minStartDate || $currentDate < $minStartDate) {
                        $minStartDate = $currentDate;
                        $minStartTime = $existingItinerary->time_start;
                    }
                    
                    if (!$maxEndDate || $currentDate > $maxEndDate) {
                        $maxEndDate = $currentDate;
                        $maxEndTime = $existingItinerary->time_end;
                    }
                }
            }
            
            $newItineraryRows = array_filter($request->daily_itineraries, function($itineraryData) {
                return empty($itineraryData['id']);
            });
            
            foreach ($newItineraryRows as $index => $itineraryData) {
                $timeStart = $itineraryData['time_start'] . ':00';
                $timeEnd = $itineraryData['time_end'] . ':00';
                
                $vehicleId = $itineraryData['vehicle_id'] ?? 0;
                $driverId = $itineraryData['driver_id'] ?? 0;
                $busAssUuid = $itineraryData['bus_ass_uuid'] ?? null;
                
                $vehicleName = '';
                if ($vehicleId > 0) {
                    $vehicle = Vehicle::find($vehicleId);
                    $vehicleName = $vehicle ? $vehicle->registration_number : '';
                }
                
                $driverName = '';
                if ($driverId > 0) {
                    $driver = Driver::find($driverId);
                    $driverName = $driver ? $driver->name : '';
                }
                
                $currentDate = $itineraryData['date'];
                $currentStartTime = $timeStart;
                $currentEndTime = $timeEnd;
                
                if (!$minStartDate || $currentDate < $minStartDate) {
                    $minStartDate = $currentDate;
                    $minStartTime = $currentStartTime;
                }
                
                if (!$maxEndDate || $currentDate > $maxEndDate) {
                    $maxEndDate = $currentDate;
                    $maxEndTime = $currentEndTime;
                }
                
                $itineraryFields = [
                    'key_uuid' => $groupInfo->key_uuid,
                    'date' => $itineraryData['date'],
                    'time_start' => $timeStart,
                    'time_end' => $timeEnd,
                    'itinerary' => $itineraryData['itinerary'] ?? null,
                    'start_location' => $itineraryData['start_location'] ?? null,
                    'end_location' => $itineraryData['end_location'] ?? null,
                    'accommodation' => isset($itineraryData['accommodation']) ? (bool)$itineraryData['accommodation'] : false,
                    'vehicle_id' => $vehicleId,
                    'vehicle' => $vehicleName,
                    'driver_id' => $driverId,
                    'driver' => $driverName,
                    'guide' => null,
                    'remarks' => $itineraryData['remarks'] ?? null,
                    'bus_ass_uuid' => $busAssUuid,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ];
                
                DailyItinerary::create($itineraryFields);
            }
            
            foreach ($busAssignmentsToDelete as $busUuidToDelete) {
                BusAssignment::where('key_uuid', $busUuidToDelete)->delete();
            }
            
            $busAssignments = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)->get();

            if (!$groupInfo->ignore_operation && $minStartDate && $maxEndDate) {
                foreach ($busAssignments as $bus) {
                    if ($bus->vehicle_id) {
                        $this->checkConflict(
                            $bus->vehicle_id,
                            null,
                            $minStartDate,
                            $minStartTime,
                            $maxEndDate,
                            $maxEndTime,
                            $bus->key_uuid
                        );
                    }
                    
                    if ($bus->driver_id) {
                        $this->checkConflict(
                            null,
                            $bus->driver_id,
                            $minStartDate,
                            $minStartTime,
                            $maxEndDate,
                            $maxEndTime,
                            $bus->key_uuid
                        );
                    }
                }
            }

            foreach ($busAssignments as $bus) {
                $itinerariesForBus = DailyItinerary::where('key_uuid', $groupInfo->key_uuid)
                                                  ->where('bus_ass_uuid', $bus->key_uuid)
                                                  ->orderBy('date', 'asc')
                                                  ->get();
                
                if ($itinerariesForBus->isEmpty()) {
                    $bus->delete();
                    continue;
                }
                
                $firstItinerary = $itinerariesForBus->first();
                $lastItinerary = $itinerariesForBus->last();
                
                $bus->update([
                    'vehicle_id' => $firstItinerary->vehicle_id > 0 ? $firstItinerary->vehicle_id : null,
                    'driver_id' => $firstItinerary->driver_id > 0 ? $firstItinerary->driver_id : null,
                    'start_date' => $firstItinerary->date,
                    'start_time' => $firstItinerary->time_start,
                    'end_date' => $lastItinerary->date,
                    'end_time' => $lastItinerary->time_end,
                    'count_daily' => $itinerariesForBus->count(),
                ]);
            }
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

    public function splitItineraries(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'itinerary_ids' => 'required|array',
            'itinerary_ids.*' => 'exists:daily_itinerary,id',
            'source_vehicle_id' => 'nullable|exists:vehicles,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            $selectedItineraries = DailyItinerary::whereIn('id', $request->itinerary_ids)
                                                 ->where('key_uuid', $groupInfo->key_uuid)
                                                 ->orderBy('date', 'asc')
                                                 ->get();
            
            if ($selectedItineraries->isEmpty()) {
                throw new \Exception('選択された行程が見つかりません。');
            }
            
            $newBusUuid = (string) Str::uuid();
            
            $maxVehicleIndex = BusAssignment::where('yoyaku_uuid', $groupInfo->key_uuid)
                                           ->max('vehicle_index') ?? 0;
            $newVehicleIndex = $maxVehicleIndex + 1;
            
            $firstItinerary = $selectedItineraries->first();
            $lastItinerary = $selectedItineraries->last();
            
            $busAssignmentData = [
                'key_uuid' => $newBusUuid,
                'yoyaku_uuid' => $groupInfo->key_uuid,
                'vehicle_id' => $firstItinerary->vehicle_id > 0 ? $firstItinerary->vehicle_id : null,
                'driver_id' => $firstItinerary->driver_id > 0 ? $firstItinerary->driver_id : null,
                'start_date' => $firstItinerary->date,
                'start_time' => $firstItinerary->time_start,
                'end_date' => $lastItinerary->date,
                'end_time' => $lastItinerary->time_end,
                'lock_arrangement' => false,
                'status_sent' => false,
                'status_finalized' => false,
                'count_daily' => $selectedItineraries->count(),
                'vehicle_number' => sprintf('%02d', $newVehicleIndex),
                'step_car' => null,
                'adult_count' => $groupInfo->adult_count,
                'child_count' => $groupInfo->child_count,
                'guide_count' => $groupInfo->guide_count,
                'other_count' => $groupInfo->other_count,
                'luggage_count' => $groupInfo->luggage_count,
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
                'vehicle_index' => $newVehicleIndex,
                'created_by' => $userId,
                'updated_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            BusAssignment::create($busAssignmentData);
            
            foreach ($selectedItineraries as $itinerary) {
                $itinerary->update([
                    'bus_ass_uuid' => $newBusUuid,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $selectedItineraries->count() . '件の行程を分割しました。',
                'new_vehicle_index' => $newVehicleIndex,
                'new_bus_uuid' => $newBusUuid
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '分割処理に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mergeItineraries(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'operation_id' => 'required|string|max:200',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            $sourceGroup = GroupInfo::where('id', $request->operation_id)
                                   ->orWhere('key_uuid', $request->operation_id)
                                   ->first();
            
            if (!$sourceGroup) {
                throw new \Exception('指定された運行IDのグループが見つかりません。');
            }
            
            $sourceItineraries = DailyItinerary::where('key_uuid', $sourceGroup->key_uuid)
                                              ->orderBy('date', 'asc')
                                              ->orderBy('time_start', 'asc')
                                              ->get();
            
            if ($sourceItineraries->isEmpty()) {
                throw new \Exception('統合元のグループに行程がありません。');
            }
            
            foreach ($sourceItineraries as $itinerary) {
                $newItinerary = $itinerary->replicate();
                $newItinerary->key_uuid = $groupInfo->key_uuid;
                $newItinerary->bus_ass_uuid = null;
                $newItinerary->created_by = $userId;
                $newItinerary->updated_by = $userId;
                $newItinerary->created_at = now();
                $newItinerary->updated_at = now();
                $newItinerary->save();
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $sourceItineraries->count() . '件の行程を統合しました。',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '統合処理に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function mergeItinerariesById(Request $request, $id)
    {
        $groupInfo = GroupInfo::findOrFail($id);
        
        $request->validate([
            'target_bus_uuid' => 'required|string|max:200',
            'source_operation_id' => 'required|integer',
            'vehicle_id' => 'nullable|integer|exists:vehicles,id',
            'driver_id' => 'nullable|integer|exists:drivers,id',
        ]);
        
        try {
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            $sourceBus = BusAssignment::where('id', $request->source_operation_id)
                                     ->where('yoyaku_uuid', $groupInfo->key_uuid)
                                     ->first();
            
            if (!$sourceBus) {
                throw new \Exception('指定された運行ID (#' . $request->source_operation_id . ') が見つかりません。');
            }
            
            $sourceBusUuid = $sourceBus->key_uuid;
            
            if ($sourceBusUuid === $request->target_bus_uuid) {
                throw new \Exception('同じ運行ID (#' . $request->source_operation_id . ') には統合できません。');
            }
            
            $targetBus = BusAssignment::where('key_uuid', $request->target_bus_uuid)
                                     ->where('yoyaku_uuid', $groupInfo->key_uuid)
                                     ->first();
            
            if (!$targetBus) {
                throw new \Exception('対象の運行が見つかりません。');
            }
            
            $sourceItineraries = DailyItinerary::where('bus_ass_uuid', $sourceBusUuid)
                                              ->where('key_uuid', $groupInfo->key_uuid)
                                              ->orderBy('date', 'asc')
                                              ->orderBy('time_start', 'asc')
                                              ->get();
            
            if ($sourceItineraries->isEmpty()) {
                throw new \Exception('統合元の運行 (#' . $request->source_operation_id . ') に行程がありません。');
            }
            
            foreach ($sourceItineraries as $itinerary) {
                $itinerary->update([
                    'vehicle_id' => $request->vehicle_id ?? $targetBus->vehicle_id ?? 0,
                    'driver_id' => $request->driver_id ?? $targetBus->driver_id ?? 0,
                    'bus_ass_uuid' => $request->target_bus_uuid,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }
            
            $targetItineraries = DailyItinerary::where('bus_ass_uuid', $request->target_bus_uuid)
                                              ->where('key_uuid', $groupInfo->key_uuid)
                                              ->orderBy('date', 'asc')
                                              ->get();
            
            if ($targetItineraries->isNotEmpty()) {
                $firstItinerary = $targetItineraries->first();
                $lastItinerary = $targetItineraries->last();
                
                $targetBus->update([
                    'start_date' => $firstItinerary->date,
                    'start_time' => $firstItinerary->time_start,
                    'end_date' => $lastItinerary->date,
                    'end_time' => $lastItinerary->time_end,
                    'count_daily' => $targetItineraries->count(),
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);
            }
            
            $sourceBus->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $sourceItineraries->count() . '件の行程を運行ID #' . $request->source_operation_id . ' から統合しました。',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => '統合処理に失敗しました: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBusAssignment(Request $request, $id)
    {
        Log::info('========== 运行详细更新按钮被点击 ==========');
        Log::info('请求URL: ' . $request->fullUrl());
        Log::info('请求方法: ' . $request->method());
        Log::info('请求数据:', $request->all());
        
        try {
            $groupInfo = GroupInfo::findOrFail($id);
            
            $request->validate([
                'bus_uuid' => 'required|string|max:200',
            ]);
            
            DB::beginTransaction();
            
            $userId = session('user_id', auth()->id() ?? 0);
            
            Log::info('BusAssignment検索: key_uuid=' . $request->bus_uuid . ', yoyaku_uuid=' . $groupInfo->key_uuid);
            
            $busAssignment = BusAssignment::where('key_uuid', $request->bus_uuid)
                                         ->where('yoyaku_uuid', $groupInfo->key_uuid)
                                         ->first();
            
            if (!$busAssignment) {
                Log::error('BusAssignmentが見つかりません');
                return response()->json([
                    'success' => false,
                    'message' => '指定された運行が見つかりません。'
                ], 404);
            }
            
            Log::info('更新前のBusAssignment:', $busAssignment->toArray());
            
            if ($request->has('deleted_itineraries') && is_array($request->deleted_itineraries)) {
                $deletedIds = $request->deleted_itineraries;
                Log::info('削除対象の行程ID: ' . json_encode($deletedIds));
                
                $deletedCount = DailyItinerary::whereIn('id', $deletedIds)
                                             ->where('key_uuid', $groupInfo->key_uuid)
                                             ->where('bus_ass_uuid', $request->bus_uuid)
                                             ->delete();
                
                Log::info('削除された件数: ' . $deletedCount);
            }
            
            $itineraries = DailyItinerary::where('bus_ass_uuid', $request->bus_uuid)
                                        ->where('key_uuid', $groupInfo->key_uuid)
                                        ->orderBy('date', 'asc')
                                        ->get();
            
            Log::info('関連する行程数: ' . $itineraries->count());
            
            $startDate = null;
            $startTime = null;
            $endDate = null;
            $endTime = null;
            
            if ($itineraries->isNotEmpty()) {
                $firstItinerary = $itineraries->first();
                $lastItinerary = $itineraries->last();
                
                $startDate = $firstItinerary->date;
                $startTime = $firstItinerary->time_start;
                $endDate = $lastItinerary->date;
                $endTime = $lastItinerary->time_end;
                
                Log::info('計算された日付範囲: 開始=' . $startDate . ' ' . $startTime . ', 終了=' . $endDate . ' ' . $endTime);
            } else {
                $startDate = $busAssignment->start_date;
                $startTime = $busAssignment->start_time;
                $endDate = $busAssignment->end_date;
                $endTime = $busAssignment->end_time;
            }
            
            $vehicleChanged = $request->has('vehicle_id') && $request->vehicle_id != $busAssignment->vehicle_id;
            $driverChanged = $request->has('driver_id') && $request->driver_id != $busAssignment->driver_id;
            $ignoreOperation = $groupInfo->ignore_operation ?? false;
            
            Log::info('競合チェック条件: vehicleChanged=' . ($vehicleChanged ? 'true' : 'false') . 
                      ', driverChanged=' . ($driverChanged ? 'true' : 'false') . 
                      ', ignoreOperation=' . ($ignoreOperation ? 'true' : 'false'));
            
            if (!$ignoreOperation && ($vehicleChanged || $driverChanged)) {
                Log::info('競合チェック実行: vehicle_id=' . $request->vehicle_id . 
                          ', driver_id=' . $request->driver_id . 
                          ', 期間=' . $startDate . '〜' . $endDate);
                
                try {
                    $this->checkConflict(
                        $request->vehicle_id,
                        $request->driver_id,
                        $startDate,
                        $startTime,
                        $endDate,
                        $endTime,
                        $busAssignment->key_uuid
                    );
                    Log::info('競合チェック: 問題なし');
                } catch (\Exception $e) {
                    Log::error('競合チェックエラー: ' . $e->getMessage());
                    throw new \Exception($e->getMessage());
                }
            } else {
                Log::info('競合チェックをスキップ');
            }
            
            $updateData = [
                'vehicle_id' => $request->vehicle_id,
                'driver_id' => $request->driver_id,
                'vehicle_number' => $request->vehicle_number,
                'step_car' => $request->step_car,
                'adult_count' => $request->adult_count ?? 0,
                'child_count' => $request->child_count ?? 0,
                'guide_count' => $request->guide_count ?? 0,
                'other_count' => $request->other_count ?? 0,
                'luggage_count' => $request->luggage_count ?? 0,
                'vehicle_type_spec_check' => $request->boolean('vehicle_type_spec_check'),
                'temporary_driver' => $request->boolean('temporary_driver'),
                'accompanying' => $request->accompanying,
                'representative' => $request->representative,
                'representative_phone' => $request->representative_phone,
                'attention' => $request->attention,
                'operation_remarks' => $request->operation_remarks,
                'operation_memo' => $request->operation_memo,
                'operation_basic_remarks' => $request->operation_basic_remarks,
                'doc_remarks' => $request->doc_remarks,
                'history_remarks' => $request->history_remarks,
                'lock_arrangement' => $request->boolean('lock_arrangement'),
                'status_sent' => $request->boolean('status_sent'),
                'status_finalized' => $request->boolean('status_finalized'),
                'updated_by' => $userId,
                'updated_at' => now(),
            ];
            
            Log::info('更新データ:', $updateData);
            
            if ($itineraries->isNotEmpty()) {
                $updateData['start_date'] = $startDate;
                $updateData['start_time'] = $startTime;
                $updateData['end_date'] = $endDate;
                $updateData['end_time'] = $endTime;
                $updateData['count_daily'] = $itineraries->count();
            }
            
            $busAssignment->update($updateData);
            
            Log::info('更新後のBusAssignment:', $busAssignment->fresh()->toArray());
            
            foreach ($itineraries as $itinerary) {
                $vehicleName = '';
                if ($request->vehicle_id > 0) {
                    $vehicle = Vehicle::find($request->vehicle_id);
                    $vehicleName = $vehicle ? $vehicle->registration_number : '';
                }
                
                $driverName = '';
                if ($request->driver_id > 0) {
                    $driver = Driver::find($request->driver_id);
                    $driverName = $driver ? $driver->name : '';
                }
                
                $itineraryUpdateData = [
                    'vehicle_id' => $request->vehicle_id ?? 0,
                    'vehicle' => $vehicleName,
                    'driver_id' => $request->driver_id ?? 0,
                    'driver' => $driverName,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ];
                
                Log::info('行程更新: ID=' . $itinerary->id . ' → ', $itineraryUpdateData);
                
                $itinerary->update($itineraryUpdateData);
            }
            
            DB::commit();
            
            Log::info('========== updateBusAssignment 成功 ==========');
            
            return response()->json([
                'success' => true,
                'message' => '運行詳細を更新しました。',
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('バリデーションエラー: ' . json_encode($e->errors()));
            
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('========== updateBusAssignment エラー ==========');
            Log::error('エラーメッセージ: ' . $e->getMessage());
            Log::error('ファイル: ' . $e->getFile() . ' 行: ' . $e->getLine());
            Log::error('スタックトレース: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}