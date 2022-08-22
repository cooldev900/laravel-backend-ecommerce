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

            $enquiries = Enquiry::query();
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

    public function getEnquiries(Request $requset)
    {
        try {
            $params = $requset->route()->parameters();

            $enquiries = Enquiry::where('client_id', $params['client_id'])
                ->where('store_id', $params['store_id'])->paginate(10);

            return response()->json([
                'status' => 'success',
                'data' => $enquiries,
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
                'vin' => 'nullable|string',
                'item_required' => 'nullable|string',
                'message' => 'nullable|string',
                'phone' => 'nullable|string',
                'client_id' => 'required|numeric',
                'status' => 'required|string',
                'store_id' => 'required|numeric',
            ]);

            $inputs = $request->all();
            $enquiry = new Enquiry();
            foreach ($inputs as $key => $input) {
                if ($key === 'token') continue;
                $enquiry[$key] = $input;
            };
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
                'vin' => 'nullable|string',
                'item_required' => 'nullable|string',
                'message' => 'nullable|string',
                'phone' => 'nullable|string',
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

    public function deleteEnquiry(Request $request)
    {
        try {
            $params = $request->route()->parameters();

            $enquiry = Enquiry::find($params['id']);
            $enquiry->delete();

            return response()->json([
                'status' => 'success',
                'data' => $enquiry,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_enquiry',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
