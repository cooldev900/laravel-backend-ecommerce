<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use PayPal\Api\Capture;

class PaypalController extends Controller
{
    public function createRefund(Request $request)
    {
        try {
            $apiContext = $this->makePaypalClient('hey');
            $capture = Capture::get($request->input('capture_id'), $apiContext);
            // $captureRefund = $capture->refundCapturedPayment($refundRequest, $apiContext);

            return response()->json([
                'status' => 'success',
                'data' => $capture,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_paypal_client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function capturePaymentIntent(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makePaypalClient($params['store_view']);

            // $payload = '
            //     {
            //         "intent": "CAPTURE",
            //         "purchase_units": [
            //             {
            //                 "amount": {
            //                     "currency_code": "USD",
            //                     "value": "100.00"
            //                 }
            //             }
            //         ]
            //     }
            // ';
            $response = $client->request('GET', 'authorizations/3KLHeWGfAlK9sk9k0c7tkJxo');

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}