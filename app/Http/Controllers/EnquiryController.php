<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use App\Models\StoreView;
use App\Models\Company;
use App\Models\User;
use App\Models\UserLocation;
use GuzzleHttp\Client;

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
                if ($key === 'token' || $key === 'location') continue;
                $enquiry[$key] = $input;
            };
            $enquiry->save();

            //send email
            // $token = $inputs['token'];

            $store_view = $inputs['store_id'];
            $client_id = $inputs['client_id'];
            $location = $inputs['location'];

            $storeview = StoreView::findOrFail($store_view);
            $company = Company::findOrFail($client_id);
            if (!$storeview) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Token_Not_Matched',
                    'message' => 'Token is not matched',
                ], 500);
            }
            $user_ids = UserLocation::where('location_id', $location)->pluck('user_id');
            $sender = User::where('company_name', $company->name)->where('email_only', 0)->first();
            $users = User::where('company_name', $company->name)->where('email_only', 1)->whereIn('id', $user_ids)->get();
            $to = '';
            $params = $request->all();
            $mailgun_variables = "{'myorderurl': '{$storeview->vsf_url}'";
            foreach ($params as $key => $value) {
                $mailgun_variables .= ", '{$key}': '{$value}'";
            }
            $mailgun_variables .= "}";
            foreach ($users as $key => $user) {
                $to .= $user['name'] . " <" . $user['email'] . ">";

                $mailClient = new Client();
                $mailClient->request(
                    'POST',
                    'https://api.eu.mailgun.net/v3/omninext.app/messages',
                    [
                        'auth' => ['api', env('MAIL_GUN_SECRET')],
                        'form_params' => [
                            'from' =>  env('MAIL_GUN_SENDER'),
                            'to' => $to,
                            'subject' => 'New Enquiry',
                            'template' => 'internalnewenquiry',
                            'h:X-Mailgun-Variables' => $mailgun_variables
                        ]
                    ]
                );
            }

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
