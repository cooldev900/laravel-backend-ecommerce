<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function allEnquiries(Request $requset)
    {
        try {
            $queries = $requset->all();
            $params = $requset->route()->parameters();

            $enquiries = Enquiry::query();
            $enquiries->where('client_id', $params['client_id']);
            $enquiries->where('store_id', $params['store_id']);
            foreach ($queries as $key => $query) {
                $enquiries->where($key, $query);
            }
            $result = $enquiries->get()->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_enquiries',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createEnquiry(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string',
                'vin' => 'string',
                'item_required' => 'numeric',
                'message' => 'string',
                'phone' => 'string',
                'client_id' => 'required|numeric',
                'store_id' => 'required|numeric',
            ]);

            $inputs = $request->all();
            $enquiry = new Enquiry();
            foreach ($inputs as $key => $input) {
                $enquiry[$key] = $input;
            };
            $enquiry->created_at = Carbon::now();
            $enquiry->updated_at = Carbon::now();
            $enquiry->save();

            return response()->json([
                'status' => 'success',
                'data' => $enquiry,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_enquiry',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateEnquiry(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string',
                'vin' => 'string',
                'item_required' => 'numeric',
                'message' => 'string',
                'phone' => 'string',
                'client_id' => 'required|numeric',
                'store_id' => 'required|numeric',
            ]);

            $params = $request->route()->parameters();
            $inputs = $request->all();

            $enquiry = Enquiry::find($params['id']);
            foreach ($inputs as $key => $input) {
                $enquiry[$key] = $input;
            };
            $enquiry->updated_at = Carbon::now();
            $enquiry->save();

            return response()->json([
                'status' => 'success',
                'data' => $enquiry,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_update_enquiry',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}