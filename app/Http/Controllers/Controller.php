<?php

namespace App\Http\Controllers;

use App\Models\NationalCodes;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Tymon\JWTAuth\Facades\JWTAuth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get guzzle instance for magento
     *
     * @return GuzzleHttp\Client;
     */

    public function allNationalCodes()
    {
        $posts = NationalCodes::all();

        return response()->json([
            'data' => $posts,
        ]);
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
