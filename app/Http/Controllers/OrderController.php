<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get Magento Orders data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allOrders(Request $request)
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

            $response = $client->request('GET', 'orders', $query);

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
     * Get Magento an Order data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getOrder(Request $request)
    {

        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('GET', 'orders/' . $params['id']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_order',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Magento an Order data.
     *
     * @param String The order item ID.
     * @return \Illuminate\Http\JsonResponse
     */

    public function getOrderItem(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('GET', 'orders/items/' . $params['id']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_order_item',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Magento an Order data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getOrderItems(Request $request)
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

            $response = $client->request('GET', 'orders/items', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_order_items',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Magento an Order data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function getNotify(Request $request)
    {
        try {
            $order_ids = $request->input('orderIds');

            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('POST', 'order/notify-orders-are-ready-for-pickup', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'orderIds' => $order_ids
                ]),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_notify',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Magento comment.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */

    public function createOrderComment(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $statusHistory = $request->input('statusHistory');

            $response = $client->request('POST', 'orders/' . $params['orderId'] . '/comments', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode([
                    'statusHistory' => $statusHistory
                ]),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_comments',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
