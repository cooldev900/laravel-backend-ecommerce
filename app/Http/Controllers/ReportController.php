<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Elasticsearch\ClientBuilder;
use Exception;

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
                'clientId' => 'nullable|string',
                'storeviewCode' => 'nullable|string',
                'storeviewId' => 'required|string',
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:start_date'
            ]);

            $params = $request->all();

            $mustQuery = [];

            if (isset($params['clientId'])) {
                $term = [
                    'term' => [
                        'ClientID' => $params['clientId']
                    ]
                ];
                array_push($mustQuery, $term);
            }

            if (isset($params['storeviewId'])) {
                $term = [
                    'term' => [
                        'StoreID' => $params['storeviewId']
                    ]
                ];
                array_push($mustQuery, $term);
            }

            $date_range = [];
            $date = \Carbon\Carbon::today()->subDays(7)->format('m/d/Y');
            $date_range['gte'] = $date;
            if (isset($params['startDate'])) {
                $date_range['gte'] = \Carbon\Carbon::parse($params['startDate'])->format('m/d/Y');
            }

            if (isset($params['endDate'])) {
                $date_range['lte'] = \Carbon\Carbon::parse($params['endDate'])->format('m/d/Y');
            }
            $term = [
                'range' => [
                    'Date' => $date_range
                ]
            ];

            array_push($mustQuery, $term);


            $body = [
                'query' => [
                    'bool' => [
                        'must' => $mustQuery ?? []
                    ]
                ],
                'aggs' => [
                    'make_count' => [
                        'cardinality' => [
                            'field' => 'Make'
                        ]
                    ]
                ]
            ];

            $response = $this->elasticsearch->search([
                'index' => "reglookup",
                'body' => $body
            ]);

            $make_count = $response['aggregations']['make_count']['value'];
            $hits = $response['hits']['hits'];
            $result = array_column($hits, '_source');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'make_count' => $make_count,
                    'items' => $result ?? [],
                    'query' => $body,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
