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
            $payment_id = $request->input('payment_id');

            $response = $client->request('GET', "authorizations/{$payment_id}");

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
