<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\StoreView;
use App\Models\Company;
use App\Models\User;
use App\Models\UserLocation;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     * path="/{store_view}/orders",
     * summary="Get all orders",
     * description="Get all orders",
     * operationId="getAllOrders",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass search criteria",
     *    @OA\JsonContent(
     *       required={"searchCriteria"},
     *       @OA\Property(property="searchCriteria", type="object", example=""),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="could_not_get_orders"),
     *       @OA\Property(property="message", type="string", example="Sorry, wrong criteria. Please try again")
     *        )
     *     )
     * )
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
     * @OA\Get(
     * path="/{store_view}/orders/{id}",
     * summary="Get an order",
     * description="Get an order",
     * operationId="getOrder",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Get an order",
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="credentials_error"),
     *       @OA\Property(property="message", type="string", example="Sorry, wrong email address ,password or company name. Please try again")
     *        )
     *     )
     * )
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
     * @OA\Post(
     * path="/{store_view}/orders",
     * summary="create an order",
     * description="create an order",
     * operationId="createOrder",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Create an order",
     *    @OA\JsonContent(
     *       required={"entity", "vsf_url"},
     *       @OA\Property(property="entity", type="string", example=""),
     *       @OA\Property(property="vsf_url", type="string", example=""),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function createOrder(Request $request)
    {
        try {
            $user = JWTAuth::user();
            $storeview = $user['store_views'][0];

            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $order = $request->input('entity');
            $vsfUrl = $request->input('vsf_url');

            if ($vsfUrl) {
                $mailClient = new Client();
                $mailClient->request(
                    'POST',
                    'https://api.eu.mailgun.net/v3/' . $storeview['email_domain'] . '/messages',
                    [
                        'auth' => ['api', $storeview['email_password']],
                        'form_params' => [
                            'from' => 'Mailgun Sandbox <' . $storeview['email_sender'] . '>',
                            'to' => $order['customer_firstname'] . ' ' . $order['customer_lastname'] . ' <' . $order['customer_email'] . '>',
                            'subject' => 'Hello Tom Brown',
                            'template' => 'order',
                            'h:X-Mailgun-Variables' => '{"myorderurl": "' . $vsfUrl . '"}'
                        ]
                    ]
                );
            }

            $payload = [
                'entity' => $order,
            ];

            $response = $client->request('POST', 'orders', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_order',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @OA\Get(
     * path="/{store_view}/orders/items/{id}",
     * summary="get an order item",
     * description="get an order item",
     * operationId="getOrderItem",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Get an order item",
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
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
     * @OA\Post(
     * path="/{store_view}/orders/{id}/cancel",
     * summary="cancel an order",
     * description="cancel an order",
     * operationId="cancelOrder",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="cancel an order",
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function cancelOrder(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('POST', 'orders/' . $params['id'].'/cancel');

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
     * @OA\Get(
     * path="/{store_view}/orders/{id}/status",
     * summary="get status of an order",
     * description="get status of an order",
     * operationId="getOrderStatus",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="cancel an order",
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function getOrderItemStatus(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $response = $client->request('GET', 'orders/' . $params['id'].'/statuses');
            
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
     * @OA\Get(
     * path="/{store_view}/orders/items",
     * summary="get order items",
     * description="get order items",
     * operationId="getOrderItems",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="get order items",
     *    @OA\JsonContent(
     *       required={},
     *       @OA\Property(property="searchCriteria", type="object"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
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
     * @OA\Post(
     * path="/{store_view}/orders/notify-orders-are-ready-for-pickup",
     * summary="get notification",
     * description="get notification",
     * operationId="getNotify",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="get order items",
     *    @OA\JsonContent(
     *       required={"orderIds"},
     *       @OA\Property(property="orderIds", type="object"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
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
     * @OA\Post(
     * path="/{store_view}/orders/{orderId}/comments",
     * summary="get notification",
     * description="get notification",
     * operationId="setOrderComments",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="get order items",
     *    @OA\JsonContent(
     *       required={},
     *       @OA\Property(property="statusHistory", type="object"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
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

    /**
     * @OA\Post(
     * path="/{store_view}/orders/{orderId}/refund",
     * summary="refund order",
     * description="refund order",
     * operationId="refundOrder",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="get order items",
     *    @OA\JsonContent(
     *       required={},
     *       @OA\Property(property="statusHistory", type="object"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function refundOrder(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $inputs = $request->all();

            $response = $client->request('POST', 'order/' . $params['orderId'] . '/refund', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($inputs),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_refund_invoice',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/{store_view}/mail/internal/new-order",
     * summary="send email for a new order",
     * description="send email for a new order",
     * operationId="newOrderEmail",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="get order items",
     *    @OA\JsonContent(
     *       required={},
     *       @OA\Property(property="storeview", type="string", example=""),
     *       @OA\Property(property="client_id", type="string", example=""),
     *       @OA\Property(property="location", type="string", example=""),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function newOrder(Request $request)
    {
        try {
            // $user = JWTAuth::user();
            // $storeview = $user['store_views'][0];
            $token = $request->header('Token');

            $store_view = $request->input('storeview');
            $client_id = $request->input('client_id');
            $location = $request->input('location');

            $storeview = StoreView::findOrFail($store_view);

            $company = Company::findOrFail($client_id);
            if ($storeview && $token != $storeview->webhook_token) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Token_Not_Matched',
                    'message' => 'Token is not matched',
                ], 500);
            }
            $user_ids = UserLocation::where('location_id', $location)->pluck('user_id');
            $sender = User::where('company_name', $company->name)->where('email_only', 0)->first(); 
            $users = User::where('company_name', $company->name)->where('email_only',1)->whereIn('id', $user_ids)->get();
            $to = '';
            foreach($users as $key => $user) {
                $to .= $user['name']." <".$user['email'].">";
                
                $mailClient = new Client();            
                $mailClient->request(
                    'POST',
                    'https://api.eu.mailgun.net/v3/' . $storeview['email_domain'] . '/messages',
                    [
                        'auth' => ['api', $storeview['email_password']],
                        'form_params' => [
                            'from' => 'Mailgun Sandbox <' . $storeview['email_sender'] . '>',
                            'to' => $to,
                            'subject' => 'New Order',
                            'template' => 'order',
                            'h:X-Mailgun-Variables' => '{"myorderurl": "' . $storeview['vsf_url'] . '"}'
                        ]
                    ]
                );           
            }

            

            return response()->json([
                'status' => 'success',
                'data' => $users,
            ], 200);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_order',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/{store_view}/mail/external/new-order",
     * summary="send external email for a new order",
     * description="send external email for a new order",
     * operationId="newOrderExternalEmail",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="get order items",
     *    @OA\JsonContent(
     *       required={},
     *       @OA\Property(property="storeview", type="string", example=""),
     *       @OA\Property(property="client_id", type="string", example=""),
     *       @OA\Property(property="location", type="string", example=""),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="invalid_user_data",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="invalid_user_data"),
     *       @OA\Property(property="message", type="string", example="The given data was invalid.")
     *        )
     *     )
     * )
     */

    public function newExternalOrder(Request $request)
    {
        try {
            // $user = JWTAuth::user();
            // $storeview = $user['store_views'][0];
            $token = $request->header('Token');

            $store_view = $request->input('storeview');
            $client_id = $request->input('client_id');
            $location = $request->input('location');

            $storeview = StoreView::findOrFail($store_view);

            $company = Company::findOrFail($client_id);
            if ($storeview && $token != $storeview->webhook_token) {
                return response()->json([
                    'status' => 'error',
                    'error' => 'Token_Not_Matched',
                    'message' => 'Token is not matched',
                ], 500);
            }
            $user_ids = UserLocation::where('location_id', $location)->pluck('user_id');
            $sender = User::where('company_name', $company->name)->where('email_only', 0)->first(); 
            $users = User::where('company_name', $company->name)->where('email_only',1)->whereIn('id', $user_ids)->get();
            $to = '';
            foreach($users as $key => $user) {
                $to .= $user['name']." <".$user['email'].">";
                
                $mailClient = new Client();            
                $mailClient->request(
                    'POST',
                    'https://api.eu.mailgun.net/v3/' . $storeview['email_domain'] . '/messages',
                    [
                        'auth' => ['api', $storeview['email_password']],
                        'form_params' => [
                            'from' => 'Mailgun Sandbox <' . $storeview['email_sender'] . '>',
                            'to' => $to,
                            'subject' => 'New Order',
                            'template' => 'order',
                            'h:X-Mailgun-Variables' => '{"myorderurl": "' . $storeview['vsf_url'] . '"}'
                        ]
                    ]
                );           
            }

            

            return response()->json([
                'status' => 'success',
                'data' => $users,
            ], 200);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_order',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     * path="/{store_view}/orders/open-carts",
     * summary="Get all open carts",
     * description="Get all open carts",
     * operationId="openCarts",
     * tags={"Order"},
     * security={ {"Bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass search criteria",
     *    @OA\JsonContent(
     *       required={"searchCriteria"},
     *       @OA\Property(property="searchCriteria", type="object", example=""),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="could_not_get_orders"),
     *       @OA\Property(property="message", type="string", example="Sorry, wrong criteria. Please try again")
     *        )
     *     )
     * )
     */

    public function openCarts(Request $request)
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

            $response = $client->request('GET', 'carts/search', $query);

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_delete_product',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
