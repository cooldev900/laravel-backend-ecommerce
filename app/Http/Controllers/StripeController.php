<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function capturePaymentIntent(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $stripe = $this->makeStripeClient($params['store_view']);

            $payment_intent = $stripe->paymentIntents->capture(
                $request->input('payment_id'),
                [
                    'amount_to_capture' => $request->input('amount_to_capture'),
                ]
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

    public function createRefund(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $charge_id = $request->input('charge_id');
            $stripe = $this->makeStripeClient($params['store_view']);

            $refund = $stripe->refunds->create([
                'charge' => $charge_id,
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