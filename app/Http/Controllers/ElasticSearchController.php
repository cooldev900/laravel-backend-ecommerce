<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Elasticsearch\ClientBuilder;
use Exception;

class ElasticSearchController extends Controller
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

    public function getNationalCodeData(Request $request)
    {
        try {
            $params = $request->all();
            unset($params['mode']);
            $mode = $request->input('mode');
            $query = [];

            $esFieldNames = [
                'Model' => 'MLOName',
                'Bodystyle' => 'Bodystyle',
                'Model_Version' => 'Model',
                'Year' => 'Model_Year',
                'mlo_code' => 'Model_Code'
            ];

            foreach (array_keys($params) as $key) {
                if ($key == 'Brand') {
                    array_push(
                        $query,
                        [
                            'term' => [
                                'MLTVehType' => $params['Brand']['type']
                            ]
                        ],
                        [
                            'term' => [
                                'MLTMakCd' => $params['Brand']['code']
                            ]
                        ]
                    );
                } else if ($key == 'Year' && isset($params['Year'])) {
                    array_push(
                        $query,
                        [
                            'range' => [
                                'start_year' => [
                                    'lte' => $params['Year']
                                ]
                            ]
                        ],
                        [
                            'range' => [
                                'end_year' => [
                                    'gte' => $params['Year']
                                ]
                            ]
                        ]
                    );
                } else if ($key == 'Engine' && isset($params['Engine'])) {
                    array_push(
                        $query,
                        [
                            'term' => [
                                'TYPName' => $params['Engine']
                            ]
                        ],
                    );
                } else {
                    if (isset($params[$key])) {
                        array_push(
                            $query,
                            [
                                'term' => [
                                    $esFieldNames[$key] => $params[$key]
                                ]
                            ],
                        );
                    }
                }
            }

            $response = $this->elasticsearch->search([
                'index' => env('ELASTIC_VEHICLE_INDEX'),
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => $query
                        ]
                    ],
                    'size' => 10000
                ]
            ]);

            $hits = $response['hits']['hits'];
            $result = [];
            if ($mode == 'single' || !isset($mode)) {
                foreach (array_keys($esFieldNames) as $key) {
                    $result[$key] = $hits[0]['_source'][$esFieldNames[$key]];
                }
            } else if ($mode == 'multi') {
                foreach ($hits as $hit) {
                    $_hit = [];
                    foreach (array_keys($esFieldNames) as $key) {
                        $_hit[$key] = $hit['_source'][$esFieldNames[$key]];
                    }

                    array_push($result, $_hit);
                }
            }

            return response()->json([
                'status' => 'success',
                'result' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSelectorStepData(Request $request)
    {
        try {
            $queryParams = $request->route()->parameters();
            $stepKey = $queryParams['key'];
            $params = $request->all();
            $query = [];

            $aggKeys = [
                'model' => 'MLOName',
                'year' => 'YearRanges',
                'bodystyle' => 'MLTName1',
                'fuel' => 'Fuel',
                'transmission' => 'Transmission',
                'trim' => 'Trim',
                'engine' => 'TYPName'
            ];

            foreach (array_keys($params) as $key) {
                if ($key == 'Brand') {
                    array_push(
                        $query,
                        [
                            'term' => [
                                'MAKVehType' => $params['Brand']['type']
                            ]
                        ],
                        [
                            'term' => [
                                'TYPMakCd' => $params['Brand']['code']
                            ]
                        ]
                    );
                } else if ($key == 'Year' && isset($params['Year'])) {
                    array_push(
                        $query,
                        [
                            'range' => [
                                'start_year' => [
                                    'lte' => $params['Year']
                                ]
                            ]
                        ],
                        [
                            'range' => [
                                'end_year' => [
                                    'gte' => $params['Year']
                                ]
                            ]
                        ]
                    );
                } else if ($key == 'Engine' && isset($params['Engine'])) {
                    array_push(
                        $query,
                        [
                            'term' => [
                                'TYPName' => $params['Engine']
                            ]
                        ],
                    );
                } else {
                    if (isset($params[$key])) {
                        array_push(
                            $query,
                            [
                                'term' => [
                                    strtolower($aggKeys[$key]) => $params[$key]
                                ]
                            ],
                        );
                    }
                }
            }

            $response = $this->elasticsearch->search([
                'index' => env('ELASTIC_VEHICLE_INDEX'),
                'body' => [
                    'query' => [
                        'bool' => [
                            'must' => $query
                        ]
                    ],
                    'size' => 0,
                    'aggs' => [
                        'result' => [
                            'terms' => [
                                'field' => $aggKeys[$stepKey],
                                'size' => 100
                            ]
                        ]
                    ]
                ]
            ]);

            $hits = $response['aggregations']['result']['buckets'];
            $result = [];
            if ($stepKey == 'year') {
                $sortedHits = [];
                foreach ($hits as $hit) {
                    if ($hit['key'] != '-') {
                        array_push($sortedHits, $hit['key']);
                    }
                }

                foreach ($sortedHits as $hit) {
                    $years = explode(',', $hit);
                    foreach ($years as $year) {
                        if (array_search($year, $result) == false) {
                            array_push($result, $year);
                        }
                    }
                }
            } else {
                foreach ($hits as $hit) {
                    if ($hit['key'] != '-') {
                        array_push($result, $hit['key']);
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'result' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function allProducts(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $pageSize = $request->get('pageSize') ?? 25;
            $currentPage = $request->get('currentPage') ?? 1;
            $mustQuery = json_decode($request->get('filter'));
            $mustNot = json_decode($request->get('must_not')) ? json_decode($request->get('must_not')): [];
            $should = json_decode($request->get('should'));
            $filterQuery = json_decode($request->get('product_type')) ?
                [
                    'terms' => [
                        'product_type' => json_decode($request->get('product_type'))
                    ]
                ] : [];
            array_push($mustNot, [
                'term' => [
                    'visibility' => '1'
                ]
            ]);
            array_push($mustNot, [
                'term' => [
                    'type_id' => 'grouped'
                ]
            ]);
            $client = $this->makeESClient($params['store_view'])['client'];
            $esIndex = $this->makeESClient($params['store_view'])['index'];

            $body = [
                'query' => [
                    'bool' => [
                        'must' => $mustQuery ?? [],
                        'must_not' => $mustNot,
                        'should' => $should ?? [],
                        'filter' => $filterQuery ?? []
                    ]
                ],
                'from' => ($currentPage - 1) * $currentPage,
                'size' => $pageSize
            ];

            $response = $client->search([
                'index' => "{$esIndex}_product",
                'body' => $body
            ]);

            // $totalCount = $client->count([
            //     'index' => "{$esIndex}_product",
            //     'body' => [
            //         'query' => $body['query'],
            //     ],
            // ]);

            $totalCount = $response['hits']['total']['value'];
            $hits = $response['hits']['hits'];
            $result = array_column($hits, '_source');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_count' => $totalCount,
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
