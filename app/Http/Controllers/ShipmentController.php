<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    /**
     * Get Magento shipments data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allShipments(Request $request)
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

            $response = $client->request('GET', 'shipments', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),

            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_shipments',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Get a shipment of Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getShipment(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('GET', 'shipment/' . $params['shipmentId']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_shipment',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * https: //magento.redoc.ly/2.4.3-admin/tag/orderorderIdship#operation/salesShipOrderV1ExecutePost

     * Create Magento a shipment data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function createShipment(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $entity = $request->input('entity');

            $response = $client->request('POST', 'order/' . $params['orderId'] . '/ship', [
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
                'error' => 'could_not_create_shipment',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function createShipmentTrack(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $entity = $request->input('entity');
            $payload = [
                'entity' => $entity,
            ];

            $response = $client->request('POST', 'shipment/track', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_shipment_track',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function deleteShipmentTrack(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $client->request('DELETE', 'shipment/track/' . $params['trackId']);

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_shipment_track',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }

    }
}