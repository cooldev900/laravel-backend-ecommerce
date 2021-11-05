<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class DataController extends Controller
{
    /**
     * Get Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getData(Request $request)
    {
        $client = $this->makeHttpClient();
        $params = $request->route()->parameters();
        $search_criteria = json_decode($request->get('searchCriteria'));
        $query = [
            'query' => [
                'searchCriteria' => $search_criteria ? $search_criteria : '',
            ],
        ];

        $response = $client->request('GET', $params['scope'], $query);

        return response()->json([
            'status' => 'success',
            'data' => json_decode($response->getBody()),
        ], 200);
    }

    /**
     * Get guzzle instance for magento
     *
     * @return GuzzleHttp\Client;
     */

    private function makeHttpClient()
    {
        $user = JWTAuth::user();
        $company = $user->company;
        $stack = HandlerStack::create();

        $middleware = new Oauth1([
            'consumer_key' => decrypt($company->consumer_key),
            'consumer_secret' => decrypt($company->consumer_secret),
            'token' => decrypt($company->token),
            'token_secret' => decrypt($company->token_secret),
        ]);
        $stack->push($middleware);

        return new Client([
            'base_uri' => decrypt($company->url),
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
    }
}