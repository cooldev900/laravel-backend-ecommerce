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
            $end_date = Carbon::parse($start_date)->addDays('5')->toDateTimeString();

            if ($this->fillSlots()) {
                
                $availableCount = DB::table('technicians')->select('*')->where('company_id', '=', $client_id)->get()->count();
                $sql = "SELECT t2.`client_id`, t1.*, t2.total, if(total < {$availableCount}, true, false) as availabe FROM slots AS t1  LEFT JOIN (
                 SELECT *, COUNT(*) AS total FROM appointments WHERE `client_id` = '{$client_id}' GROUP BY slot_id
                ) AS t2 ON t1.`id` = t2.`slot_id` WHERE t1.`start_time` >= '{$start_date}' AND t1.`start_time` <= '{$end_date}' ";

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
}
