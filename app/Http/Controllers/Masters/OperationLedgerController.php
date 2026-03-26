<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use App\Models\Masters\Vehicle;
use App\Models\Masters\DailyItinerary;
use App\Models\Masters\ReservationCategory;
use App\Models\Masters\VehicleType;
use App\Models\Masters\Agency;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OperationLedgerController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $period = $request->input('period');
        $attendanceStatus = $request->input('attendance_status');
        $vehicleTypeId = $request->input('vehicle_type_id');
        $agencyId = $request->input('agency_id');
        $reservationStatus = $request->input('reservation_status');
        $hasGuide = $request->input('has_guide');
        
        if ($period && !$startDate && !$endDate) {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->addDays($period * 7 - 1)->format('Y-m-d');
        }
        
        if (!$startDate && !$endDate && !$period) {
            $startDate = Carbon::today()->format('Y-m-d');
            $endDate = Carbon::today()->addDays(6)->format('Y-m-d');
        }
        
        $startDate = $startDate ?? Carbon::today()->format('Y-m-d');
        $endDate = $endDate ?? Carbon::today()->addDays(6)->format('Y-m-d');
        
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        $dates = [];
        $current = clone $start;
        while ($current <= $end) {
            $dates[] = [
                'date' => $current->copy(),
                'day_of_week' => $this->getJapaneseDayOfWeek($current->dayOfWeek),
                'display' => $current->format('n/j') . '（' . $this->getJapaneseDayOfWeek($current->dayOfWeek) . '）'
            ];
            $current->addDay();
        }
        
        $vehicles = Vehicle::with(['vehicleModel', 'branch'])
            ->where('is_active', true)
            ->when($vehicleTypeId, function($query) use ($vehicleTypeId) {
                $query->where('vehicle_type_id', $vehicleTypeId);
            })
            ->orderBy('display_order', 'asc')
            ->orderBy('vehicle_code', 'asc')
            ->get();
        
        $itineraries = DailyItinerary::with(['busAssignment', 'groupInfo', 'busAssignment.driver', 'busAssignment.guide'])
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('vehicle_id')
            ->when($agencyId, function($query) use ($agencyId) {
                $query->whereHas('groupInfo', function($q) use ($agencyId) {
                    $q->where('agency_id', $agencyId);
                });
            })
            ->when($reservationStatus, function($query) use ($reservationStatus) {
                $query->whereHas('groupInfo', function($q) use ($reservationStatus) {
                    $q->where('reservation_status', $reservationStatus);
                });
            })
            ->when($hasGuide, function($query) {
                $query->whereHas('busAssignment', function($q) {
                    $q->whereNotNull('guide_id');
                });
            })
            ->when($attendanceStatus, function($query) use ($attendanceStatus) {
                $query->whereHas('busAssignment.driver', function($q) use ($attendanceStatus) {
                    $q->where('attendance_status', $attendanceStatus);
                });
            })
            ->orderBy('date', 'asc')
            ->orderBy('time_start', 'asc')
            ->get();
        
        $reservationCategories = ReservationCategory::pluck('color_code', 'id')->toArray();
        
        $vehicleTypes = VehicleType::orderBy('type_name')->get();
        
        $agencies = Agency::orderBy('agency_name')->get();
        
        $scheduleData = [];
        foreach ($vehicles as $vehicle) {
            $vehicleId = $vehicle->id;
            $vehicleSchedule = [];
            
            foreach ($dates as $dateInfo) {
                $dateStr = $dateInfo['date']->format('Y-m-d');
                
                $dayItineraries = $itineraries->filter(function($itinerary) use ($dateStr, $vehicleId) {
                    $itineraryDate = Carbon::parse($itinerary->date)->format('Y-m-d');
                    return $itineraryDate == $dateStr && $itinerary->vehicle_id == $vehicleId;
                });
                
                if ($dayItineraries->count() > 0) {
                    $vehicleSchedule[$dateStr] = $this->formatItineraries($dayItineraries, $reservationCategories);
                } else {
                    $vehicleSchedule[$dateStr] = null;
                }
            }
            
            $scheduleData[$vehicleId] = [
                'vehicle' => $vehicle,
                'schedule' => $vehicleSchedule
            ];
        }
        
        return view('masters.operation-ledger.index', compact(
            'dates', 
            'vehicles', 
            'scheduleData', 
            'startDate', 
            'endDate',
            'vehicleTypes',
            'agencies'
        ));
    }
    
    private function getJapaneseDayOfWeek($dayOfWeek)
    {
        $days = ['日', '月', '火', '水', '木', '金', '土'];
        return $days[$dayOfWeek];
    }
    
    private function formatItineraries($itineraries, $reservationCategories)
    {
        $result = [];
        foreach ($itineraries as $itinerary) {
            $busAssignment = $itinerary->busAssignment;
            $groupInfo = $itinerary->groupInfo;
            
            $reservationStatus = $groupInfo->reservation_status ?? '';
            if ($reservationStatus === '見積' || $reservationStatus === 'キャンセル') {
                continue;
            }
            
            $agency = $groupInfo ? $groupInfo->agencyInfo : null;
            $driver = $busAssignment ? $busAssignment->driver : null;
            $guide = $busAssignment ? $busAssignment->guide : null;
            
            $startTime = Carbon::parse($itinerary->time_start);
            $endTime = Carbon::parse($itinerary->time_end);
            $startMinutes = $startTime->hour * 60 + $startTime->minute;
            $endMinutes = $endTime->hour * 60 + $endTime->minute;
            $duration = $endMinutes - $startMinutes;
            
            if ($duration > 0) {
                $statusColor = $this->getReservationStatusColor($reservationStatus);
                
                $categoryId = $groupInfo ? $groupInfo->reservation_categories_id : null;
                $categoryColor = 'transparent';
                if ($categoryId && $categoryId != 0 && isset($reservationCategories[$categoryId])) {
                    $categoryColor = $reservationCategories[$categoryId];
                }
                
                $result[] = [
                    'start_minutes' => $startMinutes,
                    'end_minutes' => $endMinutes,
                    'duration' => $duration,
                    'group_info_id' => $groupInfo->id ?? '?',
                    'bus_assignment_id' => $busAssignment->id ?? '?',
                    'driver_name' => $driver->name ?? ($itinerary->driver ?? '未割当'),
                    'driver_name_kana' => $driver->name_kana ?? '',
                    'driver_phone' => $driver->phone_number ?? '',
                    'is_temporary_driver' => $busAssignment->temporary_driver ?? false,
                    'vehicle_type_spec_check' => $busAssignment->vehicle_type_spec_check ?? false,
                    'status_finalized' => $busAssignment->status_finalized ?? false,
                    'guide_name' => $guide->name ?? ($itinerary->guide ?? ''),
                    'agency_code' => $agency->agency_code ?? '',
                    'group_name' => $groupInfo->group_name ?? '',
                    'remarks' => $itinerary->remarks ?? '',
                    'reservation_status' => $reservationStatus,
                    'status_color' => $statusColor,
                    'category_color' => $categoryColor,
                    'category_id' => $categoryId,
                ];
            }
        }
        
        usort($result, function($a, $b) {
            return $a['start_minutes'] - $b['start_minutes'];
        });
        
        return $result;
    }
    
    private function getReservationStatusColor($status)
    {
        $colors = [
            '予約' => '#ccf5ff',
            '仮押さえ' => '#ffff99',
            '見積' => '#ccffcc',
            '危ない' => '#ffcccc',
            '確定待ち' => '#ffd9b3',
            '確定' => '#cbb87c',
            '送信済' => '#e6e6fa',
            '実績待ち' => '#e0b0ff',
            '運行済' => '#c0c0c0',
            '請求済' => '#b0e0e6',
            'キャンセル' => '#d3d3d3',
            '稼働不可' => '#2c2c2c',
        ];
        return $colors[$status] ?? '#ffffff';
    }
}