<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Elasticsearch\ClientBuilder;
use GuzzleHttp\Client;
use Exception;
use App\Models\ReportingData;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Company;
use App\Models\StoreView;
use App\Models\Enquiry;

class ReportController extends Controller
{
    private $elasticsearch;

    public function __construct()
    {
        try {
            $hosts = [
                [
                    'host' => env('ELASTIC_VEHICLE_HOST'),
                    'port' => '9243',
                    'scheme' => 'https',
                    'user' => env('ELASTIC_VEHICLE_USER'),
                    'pass' => env('ELASTIC_VEHICLE_PASS'),
                ]
            ];

            $this->elasticsearch = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_connect_es',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReportData(Request $request)
    {
        try {
            $request->validate([
                'payload' => 'nullable|string'
            ]);

            $params = $request->all();
            $body = $params['payload'];

            $url = "https://" . env('ELASTIC_VEHICLE_HOST') . ":9243/reglookup/_search";
            $secret = env('ELASTIC_VEHICLE_USER') . ':' . env('ELASTIC_VEHICLE_PASS');
            $token = base64_encode($secret);

            $client = new Client();
            $response = $client->request(
                'POST',
                $url,
                [
                    'headers' =>
                    [
                        'Accept' => 'application/json',
                        'Accept-Language' => 'en_US',
                        'Content-Type' =>  'application/json',
                        'Authorization' => "Basic {$token}"
                    ],
                    'body' => $body
                ]
            );
            $data = json_decode($response->getBody(), true);

            $result = $data['aggregations']['groupByMake']['buckets'];

            return response()->json([
                'status' => 'success',
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createReportData(Request $request)
    {
        try {
            $request->validate([
                'order_date' => 'nullable|date',
                'order_number' => 'nullable|numeric',
                'store_id' => 'nullable|numeric',
                'client_id' => 'nullable|numeric'
            ]);

            $inputs = $request->all();
            try {
                $token = $request->header('Token');
                $storeview = StoreView::findOrFail($inputs['store_id']);

                // $company = Company::findOrFail($inputs['client_id']);
                if ($storeview && $token != $storeview->webhook_token) {
                    return response()->json([
                        'status' => 'error',
                        'error' => 'Token_Not_Matched',
                        'message' => 'Token is not matched',
                    ], 500);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'insufficient permissions',
                    'data' => [],
                ], 500);
            }
            $row = new ReportingData();
            foreach ($inputs as $key => $input) {
                $row[$key] = $input;
            }
            $row->save();

            return response()->json([
                'status' => 'success',
                'data' => $row,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_row',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReportingData(Request $request)
    {
        try {
            $request->validate([
                'start_date' => 'nullable|date',
                'end_number' => 'nullable|date',
                'store_id' => 'nullable|array',
                'client_id' => 'nullable|numeric'
            ]);


            $inputs = $request->all();
            try {
                $user = JWTAuth::user();
                if (!$user->is_admin) {
                    $company = Company::where('name', $user->company_name)->firstOrFail();
                    if (!$company || $company->id != $inputs['client_id']) {
                        return response()->json([
                            'status' => 'error',
                            'error' => 'wrong_client_id',
                            'message' => 'Wrong client Id',
                        ], 500);
                    }
                    $storeview = StoreView::where('company_id', $company->id)->where('id', $inputs['store_id'])->firstOrFail();
                    if (!$storeview)
                        return response()->json([
                            'status' => 'error',
                            'error' => 'wrong_store_id',
                            'message' => 'Wrong Store Id',
                        ], 500);
                }
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'insufficient permissions',
                    'data' => [],
                ], 500);
            }

            $query = ReportingData::where('id', '>', '0');
            $enquiry = Enquiry::where('id', '>', '0');
            if (isset($inputs['store_id'])) {
                $query = $query->whereIn('store_id', $inputs['store_id']);
                $enquiry = $enquiry->whereIn('store_id', $inputs['store_id']);
            }
            if (isset($inputs['client_id']) && $inputs['client_id']) {
                $query = $query->where('client_id', $inputs['client_id']);
                $enquiry = $enquiry->where('client_id', $inputs['client_id']);
            }
            if (isset($inputs['start_date'])) {
                $query = $query->where('order_date', '>=', $inputs['start_date']);
                $enquiry = $enquiry->where('created_at', '>=', $inputs['start_date']);
            }
            if (isset($inputs['end_date'])) {
                $query = $query->where('order_date', '<=', $inputs['end_date']);
                $enquiry = $enquiry->where('created_at', '<=', $inputs['end_date']);
            }

            $result = $query->selectRaw('sum(value) as total, count(*) as order_numbers')->get();
            $enquiry_result = $enquiry->selectRaw('count(*) as enquiry_numbers')->pluck('enquiry_numbers');
            $result = $result[0];
            $result['enquiry_numbers'] = $enquiry_result[0];

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_reporting_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCategoriesList(Request $request) {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $search_criteria = json_decode($request->get('searchCriteria'));
            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : 'entity_id',
                ],
            ];

            $response = $client->request('GET', 'categories/list', $query);

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
