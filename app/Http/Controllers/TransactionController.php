<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Get Magento transactions data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function allTransactions(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $search_criteria = json_decode($request->get('searchCriteria'));
            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : '',
                ],
            ];

            $response = $client->request('GET', 'transactions', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transactions',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a transaction of Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getTransaction(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('GET', 'transactions/' . $params['id']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
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
