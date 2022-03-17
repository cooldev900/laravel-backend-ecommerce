<?php

namespace App\Http\Controllers;

use App\Models\CompanyLocation;
use App\Models\Location;
use App\Models\UserLocation;
use Exception;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function allLocations(Request $request)
    {
        $params = $request->route()->parameters();
        $companyLocations = CompanyLocation::where('company_id', $params['companyId'])->get()->toArray();
        $allLocations = array_column($companyLocations, 'locations');
        foreach ($allLocations as $key => $location) {
            unset($location['api_token']);
            $allLocations[$key] = $location;
        };

        $result = [];
        $vsf_code = $request->get('vsf_code');

        if (isset($vsf_code)) {
            foreach ($allLocations as $location) {
                if ((int) $location['vsf_store_id'] === (int) $vsf_code) {
                    array_push($result, $location);
                }

            }
        } else {
            $result = $allLocations;
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }

    public function getLocation(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $location = Location::find($params['id'])->makeHidden(['api_token']);

            return response()->json([
                'status' => 'success',
                'data' => $location,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createLocation(Request $request)
    {
        try {
            $request->validate([
                'location_name' => 'required|string',
                'location_order_id' => 'nullable|numeric',
                'vsf_store_id' => 'nullable|numeric',
                'country' => 'nullable|string',
                'region' => 'nullable|string',
                'city' => 'nullable|string',
                'street' => 'nullable|string',
                'postcode' => 'nullable|string',
                'phone' => 'nullable|string',
                'is_hub' => 'nullable|numeric',
                'collection' => 'nullable|numeric',
                'fitment' => 'nullable|numeric',
                'delivery' => 'nullable|numeric',
                'brand' => 'nullable|string',
                'longitude' => 'nullable|string',
                'latitude' => 'nullable|string',
                'print_label' => 'nullable|numeric',
                'api_url' => 'nullable|string',
                'api_token' => 'nullable|string',
                'api_user' => 'nullable|string',
            ]);

            $inputs = $request->all();
            $newLocation = new Location();
            foreach ($inputs as $key => $input) {
                $newLocation[$key] = $input;
            }
            $newLocation->save();

            $params = $request->route()->parameters();
            $companyLocationRelationship = new CompanyLocation();
            $companyLocationRelationship->company_id = $params['companyId'];
            $companyLocationRelationship->location_id = $newLocation->id;
            $companyLocationRelationship->save();

            return response()->json([
                'status' => 'success',
                'data' => $newLocation,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateLocation(Request $request)
    {
        try {
            $request->validate([
                'location_name' => 'required|string',
                'location_order_id' => 'nullable|numeric',
                'vsf_store_id' => 'nullable|numeric',
                'country' => 'nullable|string',
                'region' => 'nullable|string',
                'city' => 'nullable|string',
                'street' => 'nullable|string',
                'postcode' => 'nullable|string',
                'phone' => 'nullable|string',
                'is_hub' => 'nullable|numeric',
                'collection' => 'nullable|numeric',
                'fitment' => 'nullable|numeric',
                'delivery' => 'nullable|numeric',
                'brand' => 'nullable|string',
                'longitude' => 'nullable|string',
                'latitude' => 'nullable|string',
                'print_label' => 'nullable|numeric',
                'api_url' => 'nullable|string',
                'api_token' => 'nullable|string',
                'api_user' => 'nullable|string',
            ]);

            $params = $request->route()->parameters();
            $location = Location::find($params['id'])->update([
                'location_name' => $request->input('location_name'),
                'location_order_id' => $request->input('location_order_id'),
                'vsf_store_id' => $request->input('vsf_store_id'),
                'country' => $request->input('country'),
                'region' => $request->input('region'),
                'city' => $request->input('city'),
                'street' => $request->input('street'),
                'postcode' => $request->input('postcode'),
                'phone' => $request->input('phone'),
                'is_hub' => $request->input('is_hub'),
                'collection' => $request->input('collection'),
                'fitment' => $request->input('fitment'),
                'delivery' => $request->input('delivery'),
                'brand' => $request->input('brand'),
                'longitude' => $request->input('longitude'),
                'latitude' => $request->input('latitude'),
                'print_label' => $request->input('print_label'),
                'api_url' => $request->input('api_url'),
                'api_token' => $request->input('api_token'),
                'api_user' => $request->input('api_user'),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $location,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_update_location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteLocation(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $userLocations = UserLocation::where('location_id', $params['id']);
            $userLocations->delete();

            $companyLocation = CompanyLocation::where('location_id', $params['id']);
            $companyLocation->delete();

            $location = Location::find($params['id']);
            $location->delete();

            return response()->json([
                'status' => 'success',
                'data' => $location,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_location',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
