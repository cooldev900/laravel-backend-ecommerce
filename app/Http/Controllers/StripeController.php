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

            $refund = $stripe->refunds->create([
                'charge' => 'ch_3KLtJRGfAlK9sk9k1rRZZZTr',
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $refund,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_orders',
                'message' => $e->getMessage(),
            ], $e->getCode());
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
                'error' => 'could_not_get_orders',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}