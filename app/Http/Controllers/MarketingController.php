<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;

class MarketingController extends Controller
{
     /**
     * Get Magento Coupons data.
     *
     * @return JsonResponse
     */
    public function getAllCoupons(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $search_criteria = json_decode($request->get('searchCriteria'));
            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : 'entity_id',
                ],
            ];
            
            $response = $client->request('GET', 'coupons/search', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_orders',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Magento Coupons data.
     *
     * @return JsonResponse
     */
    public function getSalesRule(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            
            $response = $client->request('GET', 'salesRules/'.$params['rule_id']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_orders',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
