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
            ], 500);
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
            ], 500);
        }
    }

    /**
     * https: //magento.redoc.ly/2.4.3-admin/tag/orderorderIdinvoice

     * Create Magento an Invoice data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function createInvoice(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $entity = $request->input('entity');

            $response = $client->request('POST', 'order/' . $params['orderId'] . '/invoice', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($entity),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_invoice',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
