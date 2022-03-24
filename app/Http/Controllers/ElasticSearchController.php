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
                    'host' => 'i-o-optimized-deployment-560294.es.eastus2.azure.elastic-cloud.com',
                    'port' => '9243',
                    'scheme' => 'https',
                    'user' => 'elastic',
                    'pass' => 'zt73aUuD2MS6FlQN0QgeJvaa'
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
                'Year' => 'Years',
                'Bodystyle' => 'MLTName1',
                'Fuel' => 'Fuel',
                'Transmission' => 'Transmission',
                'Trim' => 'Trim',
                'Engine' => 'TYPName',
                'Model_Code' => 'MLTCode',
                'National_Code' => 'National_Code'
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
                                    $esFieldNames[$key] => $params[$key]
                                ]
                            ],
                        );
                    }
                }
            }

            $response = $this->elasticsearch->search([
                'index' => 'vehicle-selector-v2',
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

    public function allProducts(Request $request)
    {
        try {
            $client = $this->makeESClient('default')['client'];
            $body = json_decode(
                '{
                    "query": {
                        "bool": {
                            "must": [
                                {
                                    "match_all": {}
                                }
                            ],
                            "must_not": [],
                            "should": []
                        }
                    },
                    "from": 0,
                    "size": 10,
                    "sort": [],
                    "aggs": {}
                }'
            );
            $response = $client->search([
                'index' => 'glynhopkinprod_1_product',
                'body' => $body
            ]);

            $result = $response['hits']['hits'];

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
}
