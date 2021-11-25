<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Get Magento invoices data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allInvoices(Request $request)
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

            $response = $client->request('GET', 'invoices', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_invoices',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Get Magento an Order data.
     *
     * @param String ID
     * @return \Illuminate\Http\JsonResponse
     */

    public function getInvoice(Request $request)
    {

        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('GET', 'invoices/' . $params['id']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_invoice',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Get Magento an Order data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function createInvoice(Request $request)
    {

        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            //TODO: get body and send

            // $params = $request->route()->parameters();
            $response = $client->request('POST', 'invoices');

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_invoice',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}