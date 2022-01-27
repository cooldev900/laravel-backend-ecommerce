<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\NationalCodes;
use App\Models\UserLocation;
use App\Models\UserPermission;
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
        $_user['store_views'] = array_column($permissions_store_views_unique, 'store_views');
        $_user['roles'] = array_column($permissions_roles_unique, 'roles');

        $userLocations = UserLocation::where('user_id', $_user['id'])->get()->toArray();
        $_user['locations'] = array_column($userLocations, 'locations');
        foreach ($_user['locations'] as $key => $location) {
            unset($location['api_token']);
            $_user['locations'][$key] = $location;
        }

        return $_user;
    }
}