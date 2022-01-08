<?php

namespace App\Http\Controllers;

use App\Models\CompanyLocation;
use App\Models\Location;
use App\Models\UserLocation;
use Exception;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function allLocations()
    {
        $locations = Location::all();

        $result = [];
        foreach ($locations as $location) {
            array_push($result, $location);
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
            $location = Location::find($params['id']);

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
                'location_order_id' => 'numeric',
                'vsf_store_id' => 'numeric',
                'address' => 'string',
                'phone' => 'string',
                'is_hub' => 'numeric',
                'collection' => 'numeric',
                'fitment' => 'numeric',
                'delivery' => 'numeric',
                'brand' => 'string',
                'longitude' => 'string',
                'latitude' => 'string',
            ]);

            $newLocation = new Location();
            $newLocation->location_name = $request->input('location_name');
            $newLocation->location_order_id = $request->input('location_order_id');
            $newLocation->vsf_store_id = $request->input('vsf_store_id');
            $newLocation->address = $request->input('address');
            $newLocation->phone = $request->input('phone');
            $newLocation->is_hub = $request->input('is_hub');
            $newLocation->collection = $request->input('collection');
            $newLocation->fitment = $request->input('fitment');
            $newLocation->delivery = $request->input('delivery');
            $newLocation->brand = $request->input('brand');
            $newLocation->longitude = $request->input('longitude');
            $newLocation->latitude = $request->input('latitude');
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
                'location_order_id' => 'numeric',
                'vsf_store_id' => 'numeric',
                'address' => 'string',
                'phone' => 'string',
                'is_hub' => 'numeric',
                'collection' => 'numeric',
                'fitment' => 'numeric',
                'delivery' => 'numeric',
                'brand' => 'string',
                'longitude' => 'string',
                'latitude' => 'string',
            ]);
            $params = $request->route()->parameters();
            $location = Location::find($params['id'])->update([
                'location_name' => $request->input('location_name'),
                'location_order_id' => $request->input('location_order_id'),
                'vsf_store_id' => $request->input('vsf_store_id'),
                'address' => $request->input('address'),
                'phone' => $request->input('phone'),
                'is_hub' => $request->input('is_hub'),
                'collection' => $request->input('collection'),
                'fitment' => $request->input('fitment'),
                'delivery' => $request->input('delivery'),
                'brand' => $request->input('brand'),
                'longitude' => $request->input('longitude'),
                'latitude' => $request->input('latitude'),
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

            $companyLocation = CompanyLocation::where('location_id', $params['id']);
            $companyLocation->delete();

            $userLocation = UserLocation::where('location_id', $params['id']);
            $userLocation->delete();

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