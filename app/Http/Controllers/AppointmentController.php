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
                $sql = "SELECT t2.`client_id`, t1.*, t2.total, if(ifnull(total,0) < {$availableCount}, true, false) as available, t2.technician_ids, t2.id as appointment_id FROM slots AS t1  LEFT JOIN (
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

                $appointment = Appointment::where('client_id', $params['companyId']);
                if (isset($inputs['customer'])) {
                    $appointment->where('customer', 'like', "%{$inputs['customer']}%");
                }

                if (isset($inputs['orderId'])) {
                    $appointment->where('order_id', $inputs['orderId']);
                }

                if (isset($inputs['from'])) {
                    $date = Carbon::parse($inputs['from'])->toDateTimeString();
                    // $sql.= " and start_time >= '{$date}'";
                    $appointment->where('start_time', '>=', $date);
                }

                if (isset($inputs['to'])) {
                    $date = Carbon::parse($inputs['to'])->toDateTimeString();
                    // $sql.= " and start_time <= '{$date}'";
                    $appointment->where('start_time', '<=', $date);
                }

                if (isset($inputs['technician']) && sizeof($inputs['technician']) > 0) {
                    // $ids = implode(",", $inputs['technician']);
                    // $sql.= " and technician_id in ({$ids})";
                    $appointment->whereIn('technician_id', $inputs['technician']);
                }

                $result = $appointment->orderBy('start_time', 'desc')->paginate(10);

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

    private function isAvailable($client_id, $slot_id) {
        $availableCount = DB::table('technicians')->select('*')->where('company_id', '=', $client_id)->get()->count();
        $sql = "SELECT t2.`client_id`, t1.*, t2.total, if(ifnull(total,0) < {$availableCount}, true, false) as available, t2.technician_ids, t2.id AS appointment_id FROM slots AS t1  LEFT JOIN (
            SELECT *, COUNT(*) AS total , GROUP_CONCAT(technician_id) AS technician_ids FROM appointments WHERE `client_id` = '{$client_id}' GROUP BY slot_id
        ) AS t2 ON t1.`id` = t2.`slot_id` WHERE t1.id = '{$slot_id}' ";

        $result = DB::select($sql);
        if (isset($result) && sizeof($result) > 0)
         return $result[0]->available;
        else 
         return 0;
    }

    public function isAvaibleSlot(Request $request) {
        $request->validate([
            'slot_id' => 'string',
            'client_id' => 'string',
        ]);
        $client_id = $request->input('client_id');
        $slot_id = $request->input('slot_id');
        $availabe = $this->isAvailable($client_id, $slot_id);
        return response()->json([
            'status' => 'success',
            'data' => $availabe,
        ], 200);
    }

    public function setSlot(Request $request) {
        try {

            // $request->validate([
            //     'clientID' => 'nullable|string',
            //     'start_time' => 'nullable|string',
            //     'end_time' => 'nullable|string',
            //     'id' => 'nullable|integer',
            //     'orderid' => 'nullable|string',
            //     'booked_online' => 'nullable|boolean',
            //     'note' => 'nullable|string',
            //     'internal_booking' => 'nullable|boolean',
            //     'technician_id' => 'nullable|array',
            //     'slot_ids' => 'nullable|array',
            //     'isEdit' => 'nullable|boolean',
            //     'client_id' => 'nullable|string',
            // ]);
            
            $inputs = $request->all();
            $params = $request->route()->parameters();
            
            if (isset($inputs['isEdit']) && $inputs['isEdit']) {
                $slot_id = $inputs['slot_ids'][0];
                $client_id = $params['companyId'];
                $availabe = $this->isAvailable($client_id, $slot_id);
                if (!$availabe) {
                    return response()->json([
                        'status' => 'error',
                        'error' => 'fail_available_slot',
                        'message' => "You can not book this appointment anymore",
                    ], 200);
                }
                $appointment = Appointment::findOrFail($inputs['id']);
                if ($appointment) {
                    foreach ($inputs as $key => $input) {
                        if ($key === 'total'  || $key === 'slot_ids' || $key === 'isEdit' || $key === 'available' || $key === 'appointment_id' || $key === 'technician_ids' || $key === 'technical_id' ||  $key === 'orderid') continue;
                        if ($key === 'id') {  
                            $appointment['slot_id'] = $input;                      
                            continue;
                        }
                        $appointment[$key] = $input;
                    }
                    $slot = Slot::findOrFail($slot_id);
                    if ($slot) {
                        $appointment->start_time = $slot->start_time;
                        $appointment->end_time = $slot->end_time;
                    }
                    $appointment['slot_id'] = $slot_id;
                    $technician_id = $inputs['technician_id'];
                    $appointment->technician_id = $inputs['technician_id'];
                    $appointment->client_id = $client_id;
                    $appointment->save();

                    return response()->json([
                        'status' => 'success',
                        'data' => $appointment,
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'error' => 'Not_Found_Appointment',
                        'message' => 'Not_Found_Appointment',
                    ], 500);
                }
            }

            $client_id = $request->input('clientID');
            if (!$client_id) {
                $client_id = $request->input('client_id');
            }
            if (!$client_id)
                $client_id = $params['companyId'];
            $slot_id = $request->input('id');
            if ($slot_id == -1) $slot_id = $request->input('slot_id');

            $slot_ids = [];
            if ($slot_id) {
                array_push($slot_ids, $slot_id);
            } else {
                $slot_ids = $request->input('slot_ids');
            }            

            $appointments = [];
            
            foreach($slot_ids as $slot_id) {
                $availabe = $this->isAvailable($client_id, $slot_id);
                if (!$availabe) {
                    continue;
                }

                $start_time = '';
                $end_time = '';

                $slot = Slot::findOrFail($slot_id);
                if ($slot) {
                    $start_time = $slot->start_time;
                    $end_time = $slot->end_time;

                    $now = Carbon::now()->toDateTimeString();

                    if ($now > $start_time) {
                        continue;    
                    }

                    $appointment = '';

                    $technician_id = $request->input('technician_id');
                    if (!isset($technician_id)) {
                        // $appointment = Appointment::where('slot_id', $slot_id)->where('technician_id', $technician_id)->firstOrFail();
                        // if ($appointment) {
                        //     return response()->json([
                        //         'status' => 'error',
                        //         'error' => 'Duplicate_Technicain_Error',
                        //         'message' => 'This technician is duplicate with '.$technician_id,
                        //     ], 500);
                        // }
                        $old_ids = Appointment::where('slot_id', $slot_id)->where('client_id', $client_id)->pluck('technician_id')->toArray();
                        $technician_id = '';
                        if (sizeof($old_ids) > 0) {
                            $old_ids = implode(",", $old_ids);
                        } else {
                            $old_ids = '0';
                        }
                        $remained_ids = DB::select("select id from technicians where id not in ({$old_ids}) and company_id='{$client_id}'");
                        if (sizeof($remained_ids)) {
                            $technician_id = $remained_ids[0];
    
                            $appointment = new Appointment();
                            foreach ($inputs as $key => $input) {
                                if ($key === 'total' || $key === 'available' || $key === 'appointment_id' || $key === 'technician_ids' || $key === 'slot_ids') continue;
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
                            
                            $appointment->technician_id = $technician_id->id;
                            
                            $appointment->slot_id = $slot_id;
                            $appointment->save();
                            array_push($appointments, $appointment);
                        } else {
                            continue;
                        }
                    } else {
                        if (sizeof($technician_id)) {
                            foreach($technician_id as $t_id) {
                                $appointment = Appointment::where('slot_id', $slot_id)->where('technician_id', $t_id)->first();
                                if ($appointment) {
                                    continue;
                                }

                                $appointment = new Appointment();
                                foreach ($inputs as $key => $input) {
                                    if ($key === 'total'  || $key === 'slot_ids' || $key === 'available' || $key === 'appointment_id' || $key === 'technician_ids' || $key === 'technical_id' ||  $key === 'orderid') continue;
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
                                array_push($appointments, $appointment);
                            }
                        }
                    }
                }                           
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $appointments,
            ], 200);

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
