<?php

namespace App\Http\Controllers;
use App\Models\Appointment;
use App\Models\Company;
use App\Models\Technician;
use App\Models\Slot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AppointmentController extends Controller
{

    public function getSlots(Request $request) {
        try {

            $request->validate([
                'date' => 'nullable|string',
                'show' => 'nullable|string',
                'timezone' => 'nullable|string',
                'clientID' => 'nullable|string',
            ]);

            $client_id = $request->input('clientID');
            $date = $request->input('date');
            $show = $request->input('show');

            $start_date = Carbon::parse($date)->toDateTimeString();
            $end_date = Carbon::parse($start_date)->addDays('1')->toDateTimeString();

            if ($this->fillSlots()) {

                $availableCount = DB::table('technicians')->select('*')->where('company_id', '=', $client_id)->get()->count();
                $sql = "SELECT t2.`client_id`, t1.*, t2.total, if( ifnull(total,0) < {$availableCount}, true, false) as available, t2.technician_ids, t2.id as appointment_id FROM slots AS t1  LEFT JOIN (
                 SELECT *, COUNT(*) AS total , GROUP_CONCAT(technician_id) AS technician_ids FROM appointments WHERE `client_id` = '{$client_id}' GROUP BY slot_id
                ) AS t2 ON t1.`id` = t2.`slot_id` WHERE t1.`start_time` >= '{$start_date}' AND t1.`start_time` < '{$end_date}' ";

                $result = DB::select($sql);

                return response()->json([
                    'status' => 'success',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'fail_fill_slots',
                    'message' => 'Error in filling slots',
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function fillSlots() {
        try {
            ini_set('max_execution_time', 3000);
            $today = Carbon::now();
            $row_count = DB::table('slots')->select('*')->get()->count();
            $s = '';
            if (!$row_count) {
                $year = Carbon::now()->format('Y');

                for ($i = $year; $i < $year + 5; $i++ ) {
                    for ($j = 1; $j <= 12; $j++ ) {
                        $firstDay = $year."-".$j."-01";
                        $lastDayofMonth = Carbon::parse($firstDay)->endOfMonth()->format('d');
                        for ($k = 1; $k <= $lastDayofMonth ;$k++ ) {
                            for ($m = 0; $m < 12; $m ++) {
                                $start_time = Carbon::parse($i."-".$j."-".$k." ".(($m * 2)).":00")->toDateTimeString();
                                $end_time = Carbon::parse($i."-".$j."-".$k." ".((($m + 1) * 2)).":00")->toDateTimeString();
                                $slot = new Slot();
                                $slot->start_time = $start_time;
                                $slot->end_time = $end_time;
                                $slot->duration = 2;
                                $slot->save();
                                $s = $m;
                            }
                        }
                    }
                }
            }

            return true;
        } catch(Exception $e) {
            return false;
        }
    }

    public function getAllAppointments(Request $request) {
        try {
            $params = $request->route()->parameters();

            if ($this->fillSlots()) {

                $inputs = $request->all();
                $sql = "select * from appointments where client_id = '{$params['companyId']}'";
                if (isset($inputs['customer'])) {
                    $sql.= " and  customer like '%{$inputs['customer']}%'";
                }

                if (isset($inputs['from'])) {
                    $date = Carbon::parse($inputs['from'])->toDateTimeString();
                    $sql.= " and start_time >= '{$date}'";
                }

                if (isset($inputs['to'])) {
                    $date = Carbon::parse($inputs['to'])->toDateTimeString();
                    $sql.= " and start_time <= '{$date}'";
                }

                if (isset($inputs['technician']) && sizeof($inputs['technician']) > 0) {
                    $ids = implode(",", $inputs['technician']);
                    $sql.= " and technician_id in ({$ids})";
                }

                $result = DB::select($sql);

                return response()->json([
                    'status' => 'success',
                    'data' => $result,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'fail_fill_slots',
                    'message' => 'Error in filling slots',
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function setSlot(Request $request) {
        try {

            $request->validate([
                'clientID' => 'nullable|string',
                'start_time' => 'nullable|string',
                'end_time' => 'nullable|string',
                'id' => 'nullable|integer',
                'orderid' => 'nullable|string',
                'booked_online' => 'nullable|boolean',
                'note' => 'nullable|string',
                'internal_booking' => 'nullable|boolean',
                'technician_id' => 'nullable|array',
            ]);

            $client_id = $request->input('clientID');
            $slot_id = $request->input('id');
            if ($slot_id == -1) $slot_id = $request->input('slot_id');

            $start_time = '';
            $end_time = '';

            $slot = Slot::findOrFail($slot_id);
            if ($slot) {
                $start_time = $slot->start_time;
                $end_time = $slot->end_time;

                $now = Carbon::now()->toDateTimeString();

                if ($now > $start_time) {
                    return response()->json([
                        'status' => 'error',
                        'error' => 'past_slot_error',
                        'message' => 'Can not book the past slot with '.$slot_id,
                    ], 500);    
                }

                $inputs = $request->all();
                $appointment = '';

                $technician_id = $request->input('technician_id');
                if (!isset($technician_id)) {
                    $appointment = new Appointment();
                    foreach ($inputs as $key => $input) {
                        if ($key === 'total' || $key === 'available' || $key === 'appointment_id' || $key === 'technician_ids') continue;
                        if ($key === 'id') {  
                            $appointment['slot_id'] = $input;                      
                            continue;
                        }
                        if ($key === 'start_time') {  
                            $appointment[$key] = $start_time;                      
                            continue;
                        }
                        if ($key === 'end_time') {  
                            $appointment[$key] = $end_time;                      
                            continue;
                        }
                        $appointment[$key] = $input;
                    }
                    $row = Technician::where('company_id', $inputs['client_id'])->firstOrFail();
                    if ($row) {
                        $appointment->technician_id = $row->id;
                    }
                    $appointment['slot_id'] = $slot_id;
                    $appointment->save();
                } else {
                    if (sizeof($technician_id)) {
                        $params = $request->route()->parameters();
                        foreach($technician_id as $t_id) {
                            $appointment = new Appointment();
                            foreach ($inputs as $key => $input) {
                                if ($key === 'total' || $key === 'available' || $key === 'appointment_id' || $key === 'technician_ids' || $key === 'technical_id' ||  $key === 'orderid') continue;
                                if ($key === 'id') {  
                                    $appointment['slot_id'] = $input;                      
                                    continue;
                                }
                                if ($key === 'start_time') {  
                                    $appointment[$key] = $start_time;                      
                                    continue;
                                }
                                if ($key === 'end_time') {  
                                    $appointment[$key] = $end_time;                      
                                    continue;
                                }
                                $appointment[$key] = $input;
                            }
                            $appointment['slot_id'] = $slot_id;
                            $appointment->technician_id = $t_id;
                            $appointment->client_id = $params['companyId'];
                            $appointment->save();
                        }
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $appointment,
                ], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'error' => 'find_slot_fail',
                    'message' => 'Does not find slot with '.$slot_id,
                ], 500);    
            }

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSlot(Request $request) {
        try {

            $request->validate([
                'appointment_id' => 'nullable|integer'
            ]);
            
            $appointment_id = $request->input('appointment_id');
            $appointment = Appointment::findOrFail($appointment_id);
            $appointment->delete();

            return response()->json([
                'status' => 'success',
                'data' => $appointment,
            ], 200);
            

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchSlotData(Request $request) {
        try {

            $request->validate([
                'date' => 'nullable|string'
            ]);
            
            $date = $request->input('date');

            $start_date = Carbon::parse($date)->toDateTimeString();
            $end_date = Carbon::parse($start_date)->addDays('5')->toDateTimeString();

            $result = Slot::where('start_time','>=', $start_date)->where('start_time', '<', $end_date)->get();

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
            

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchTechnicians(Request $request) {
        try {

            $params = $request->route()->parameters();

            $result = Technician::where('company_id', $params['companyId'])->get();

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
            

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAppointment(Request $request) {
        try {

            $params = $request->route()->parameters();

            $result = Appointment::findOrFail($params['id']);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
            

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
