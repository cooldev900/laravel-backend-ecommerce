<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    /**
     * Get Magento Refund Data.
     * https://magento.redoc.ly/2.4.3-admin/tag/creditmemos
     *
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */

    public function allRefunds(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $search_criteria = json_decode($request->get('searchCriteria'));

            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : ''
                ],
            ];
            $response = $client->request('GET', 'creditmemos', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_refunds',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
