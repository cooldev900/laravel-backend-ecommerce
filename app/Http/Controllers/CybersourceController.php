<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use CyberSource\ExternalConfiguration;
use CyberSource\ApiClient;
use CyberSource\Api\TransactionDetailsApi;
use CyberSource\Api\CaptureApi;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\StoreView;
use App\Models\Company;


class CybersourceController extends Controller
{   
    private function getClient($store_view) {
        $user = JWTAuth::user();
        $company = Company::where('name', $user->company_name)->firstOrFail();

        $storeview = StoreView::where('company_id', $company->id)->where('code', $store_view)->firstOrFail();
        return hextobin(decrypt($storeview['cybersource']['shared_secret_key']));
        if (isset($storeview['cybersource']) && isset($storeview['cybersource']['secret_api_key'])) {
            $commonElement = new ExternalConfiguration(hextobin(decrypt($storeview['cybersource']['merchant_id'])), hextobin(decrypt($storeview['cybersource']['key'])), hextobin(decrypt($storeview['cybersource']['shared_secret_key'])));
            $config = $commonElement->ConnectionHost();
            $merchantConfig = $commonElement->merchantConfigObject();
    
            $api_client = new ApiClient($config, $merchantConfig);
            return $api_client;
        } else {
            new Exception('could_not_create_cybersource_client');
        }

    }

    public function getTransaction(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $id = $params['id'];
            $api_client = $this->getClient($params['store_view']);
            return response()->json([
                'status' => 'success',
                'data' => $api_client,
            ], 200);
            $api_instance = new TransactionDetailsApi($api_client);
            $apiResponse = $api_instance->getTransaction($id);

            return response()->json([
                'status' => 'success',
                'data' => $apiResponse,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // public function getTransaction(Request $request)
    // {
    //     try {
    //         $params = $request->route()->parameters();
    //         $id = $params['id'];
            
    //         $response = Http::acceptJson()->withHeaders([
    //             'host' =>	'apitest.cybersource.com',
    //             'signature' => 'keyid="6923d223-3278-4e81-a4c1-7ad1d630d3d3", algorithm="HmacSHA256", headers="host (request-target) v-c-merchant-id", signature="tbINDzLyFtkC/muRdj9pLfGJAx8yuJEN1xkIYTQGQ5k="',
    //             'v-c-merchant-id' => 'cbq_alfardan_qar',
    //             'v-c-date' => 'Tue, 12 Jul 2022 16:02:41 GMT'
    //         ])->get('https://apitest.cybersource.com/tss/v2/transactions/'.$id)->json();

    //         return response()->json([
    //             'status' => 'success',
    //             'data' => $response,
    //         ], 200);
    //     } catch (Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'error' => 'could_not_get_transaction',
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function capture(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $api_client = $this->getClient($params['store_view']);
            $api_instance = new CaptureApi($api_client);
            
            $apiResponse = $api_instance->capturePayment($requestObj, $id);

            return response()->json([
                'status' => 'success',
                'data' => $apiResponse,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_capture',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function void(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $payment_id = $request->input('payment_id');
            $stripe = $this->makeStripeClient($params['store_view']);

            $charges = $stripe->charges->all([
                'limit' => 3,
                'payment_intent' => $payment_id
            ]);

            $charge_id = null;
            if (sizeof($charges['data']) > 0) {
                $charge_id = $charges['data'][0]['id'];
            }

            $refund = $stripe->refunds->create([
                'charge' => $charge_id,
                'amount' => $request->input('amount_to_capture')
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $refund,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_refund',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
