<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Get Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function allCustomers(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $search_criteria = json_decode($request->get('searchCriteria'));

            $json = '
            {
                "search_criteria": {
                    "filter_groups": [
                        {
                            "filters": [
                                {
                                    "field": "email",
                                    "value": "%",
                                    "condition_type": "like"
                                }
                            ]
                        }
                    ]
                }
            }
            ';
            $j = $search_criteria ? $search_criteria : json_decode($json);
            $get_params = http_build_query($j);

            $response = $client->request('GET', 'customers/search?' . $get_params);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_customers',
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Get Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getCustomer(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $response = $client->request('GET', 'customers/' . $params['customerId']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_customer',
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function deleteCustomer(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $client->request('DELETE', 'customers/' . $params['customerId']);

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_delete_customer',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
