<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class DataController extends Controller
{

    private $client;

    /**
     * Create a new DataController instance.
     *
     * @return void
     */

    public function __construct()
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

        $this->client = new Client([
            'base_uri' => decrypt($company->url),
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
    }

    /**
     * Get Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getData(Request $request)
    {
        $params = $request->route()->parameters();
        $search_criteria = json_decode($request->get('searchCriteria'));

        $response = $this->client->request('GET', $params['scope'], [
            'query' => [
                'searchCriteria' => $search_criteria,
            ],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => json_decode($response->getBody()),
        ], 200);
    }
}