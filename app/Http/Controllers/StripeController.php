<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function getTransaction(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $stripe = $this->makeStripeClient($params['store_view']);

            $payment_intent = $stripe->paymentIntents->retrieve(
                $request->input('payment_id'),
            );

            return response()->json([
                'status' => 'success',
                'data' => $payment_intent,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function capturePaymentIntent(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $payment_id = $request->input('payment_id');
            $stripe = $this->makeStripeClient($params['store_view']);

            // $charges = $stripe->charges->all([
            //     'limit' => 3,
            //     'payment_intent' => $payment_id
            // ]);

            // $charge_id = null;
            // if (sizeof($charges['data']) > 0) {
            //     $charge_id = $charges['data'][0]['id'];
            // }

            $capture = $stripe->paymentIntents->capture($payment_id, [
                'amount' => $request->input('amount')
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $capture,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_capture',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createRefund(Request $request)
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
