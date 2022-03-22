<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use http\Client;

use Elasticsearch\ClientBuilder;
use GraphQL\Query;

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

    public function graphql()
    {
        $client = new Client(
            'https://graphql-pokemon.now.sh/'
        );
        $gql = (new Query('pokemon'))
            ->setVariables([new Variable('name', 'String', true)])
            ->setArguments(['name' => '$name'])
            ->setSelectionSet(
                [
                    'id',
                    'number',
                    'name',
                    (new Query('evolutions'))
                        ->setSelectionSet(
                            [
                                'id',
                                'number',
                                'name',
                                (new Query('attacks'))
                                    ->setSelectionSet(
                                        [
                                            (new Query('fast'))
                                                ->setSelectionSet(
                                                    [
                                                        'name',
                                                        'type',
                                                        'damage',
                                                    ]
                                                )
                                        ]
                                    )
                            ]
                        )
                ]
            );

        try {
            $name = readline('Enter pokemon name: ');
            $results = $client->runQuery($gql, true, ['name' => $name]);

            dd($results);
        } catch (QueryError $exception) {
            exit;
        }
    }
}
