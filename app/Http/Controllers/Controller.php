<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\NationalCodes;
use App\Models\StoreView;
use App\Models\UserLocation;
use App\Models\UserPermission;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Stripe;
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

    protected function makeHttpClient($store_view)
    {
        $user = JWTAuth::user();
        $stack = HandlerStack::create();
        $company = Company::where('name', $user->company_name)->firstOrFail();

        $middleware = new Oauth1([
            'consumer_key' => decrypt($company->consumer_key),
            'consumer_secret' => decrypt($company->consumer_secret),
            'token' => decrypt($company->token),
            'token_secret' => decrypt($company->token_secret),
        ]);
        $stack->push($middleware);

        return new Client([
            'base_uri' => decrypt($company->url) . $store_view . '/V1/',
            'handler' => $stack,
            'auth' => 'oauth',
        ]);
    }

    /**
     * Get guzzle instance for stripe
     *
     * @return GuzzleHttp\Client;
     */

    protected function makeStripeClient($store_view)
    {
        try {
            $user = JWTAuth::user();
            $company = Company::where('name', $user->company_name)->firstOrFail();

            $storeview = StoreView::where('company_id', $company->id)->where('code', $store_view)->firstOrFail();
            $secret_key = decrypt($storeview->api_key_2);

            return new \Stripe\StripeClient($secret_key);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_stripe_client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get guzzle instance for paypal
     *
     * @return GuzzleHttp\Client;
     */

    protected function makePaypalClient($store_view)
    {
        try {
            $user = JWTAuth::user();
            $company = Company::where('name', $user->company_name)->firstOrFail();
            $storeview = StoreView::where('company_id', $company->id)->where('code', $store_view)->firstOrFail();

            $client_id = decrypt($storeview->api_key_1);
            $secret_key = decrypt($storeview->api_key_2);
            $uri = 'https://api.sandbox.paypal.com/v1/oauth2/token';

            $authClient = new Client();
            $response = $authClient->request('POST', $uri, [
                'headers' =>
                [
                    'Accept' => 'application/json',
                    'Accept-Language' => 'en_US',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'body' => 'grant_type=client_credentials',

                'auth' => [$client_id, $secret_key, 'basic'],
            ]
            );

            $data = json_decode($response->getBody(), true);

            $access_token = $data['access_token'];

            return new Client([
                'base_uri' => 'https://api-m.sandbox.paypal.com/v2/',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer $access_token",
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_paypal_client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the user permission based on JWT token.
     *
     * @param  \Model\User $user
     *
     * @return Object $user with permissions
     */

    public function getPermission($user)
    {
        $_user = $user;

        $company = Company::where('name', $_user['company_name'])->firstOrFail();
        $_user['image_base_url'] = $company->image_base_url;
        $_user['client_id'] = $company->id;

        $permissions = UserPermission::where('user_id', $_user['id'])->get();
        $permissions_scopes_unique = json_decode(json_encode($permissions->unique('scopes')), true);
        $permissions_store_views_unique = json_decode(json_encode($permissions->unique('store_views')), true);
        $permissions_roles_unique = json_decode(json_encode($permissions->unique('roles')), true);

        $_user['scopes'] = array_column($permissions_scopes_unique, 'scopes');
        $_user['roles'] = array_column($permissions_roles_unique, 'roles');

        $_user['store_views'] = array_column($permissions_store_views_unique, 'store_views');
        $result_store_views = [];
        foreach ($_user['store_views'] as $storeview) {
            if ($storeview['company']) {
                $storeview['company'] = [
                    'id' => $storeview['company']['id'],
                    'name' => $storeview['company']['name'],
                ];
            }
            array_push($result_store_views, $storeview);
        }
        $_user['store_views'] = $result_store_views;

        $userLocations = UserLocation::where('user_id', $_user['id'])->get()->toArray();
        $_user['locations'] = array_column($userLocations, 'locations');
        $result_locations = [];
        foreach ($_user['locations'] as $location) {
            unset($location['api_token']);
            array_push($result_locations, $location);
        }
        $_user['locations'] = $result_locations;

        return $_user;
    }
}