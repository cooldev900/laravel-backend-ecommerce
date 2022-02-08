<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function createSmartLabel(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeCourierClient($params['location_id']);

            $payload = $request->all();

            $response = $client->request('POST', 'couriers/v1/smart-shipping/create-label', [
                'body' => json_encode($payload),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'failed_create_courier_auth',
                'message' => $e->getMessage(),
            ], 500);
        }

    }
}