<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Elasticsearch\ClientBuilder;

class ElasticSearchController extends Controller
{
    private $elasticsearch;

    public function __construct()
    {
        $hosts = [
            'https://elastic:zt73aUuD2MS6FlQN0QgeJvaa@i-o-optimized-deployment-560294.es.eastus2.azure.elastic-cloud.com',
        ];
        $this->elasticsearch = ClientBuilder::create()
            ->setHosts($hosts)
            ->build();
    }

    public function getSelectorStepData(Request $request)
    {
        $item = $this->elasticsearch->search([
            'index' => 'vehicle-selector-v2',
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'term' => [
                                'MAKVehType' => 10
                            ],
                            'term' => [
                                'TYPMakCd' => 6848
                            ],
                        ]
                    ]
                ]
            ]
        ]);

        dd($item);
    }
}
