<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Scope;
use App\Models\StoreView;
use App\Models\UserPermission;
use Exception;
use Illuminate\Http\Request;

class StoreviewController extends Controller
{
    public function allStoreviews()
    {
        try {
            $storeViews = StoreView::all()->toArray();

            $result = [];
            foreach ($storeViews as $storeView) {
                if ($storeView['company']) {
                    $storeView['company'] = [
                        'id' => $storeView['company']['id'],
                        'name' => $storeView['company']['name'],
                    ];
                }

                array_push($result, $storeView);
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_storeviews',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStoreview(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $storeview = StoreView::find($params['id']);

            if ($storeview['company']) {
                $hiddenColumns = ['consumer_key', 'consumer_secret', 'token', 'token_secret', 'url', 'magento_id'];
                foreach ($hiddenColumns as $column) {
                    unset($storeview['company'][$column]);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $storeview,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createStoreview(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'store_id' => 'numeric',
                'company_id' => 'required|numeric',
                'payment_provider' => 'nullable|string',
                'api_key_1' => 'nullable|string',
                'api_key_2' => 'nullable|string',
                'payment_additional_1' => 'nullable|string',
                'payment_additional_2' => 'nullable|string',
                'payment_additional_3' => 'nullable|string',
            ]);

            $inputs = $request->all();
            $newStoreView = new StoreView();
            foreach ($inputs as $key => $input) {
                if ($key === 'api_key_1'
                    || $key === 'api_key_2' || $key === 'payment_additional_1'
                    || $key === 'payment_additional_2' || $key === 'payment_additional_3') {
                    $newStoreView[$key] = encrypt($input);
                } else {
                    $newStoreView[$key] = $input;

                }

            }
            $newStoreView->save();

            return response()->json([
                'status' => 'success',
                'data' => $newStoreView,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_create_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteStoreview(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $userPermissions = UserPermission::where('store_view_id', $params['id']);
            $userPermissions->delete();

            $storeview = StoreView::find($params['id']);
            $storeview->delete();

            return response()->json([
                'status' => 'success',
                'data' => $storeview,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStoreview(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'store_id' => 'numeric',
                'company_id' => 'required|numeric',
                'payment_provider' => 'nullable|string',
                'api_key_1' => 'nullable|string',
                'api_key_2' => 'nullable|string',
                'payment_additional_1' => 'nullable|string',
                'payment_additional_2' => 'nullable|string',
                'payment_additional_3' => 'nullable|string',
            ]);

            $params = $request->route()->parameters();
            $storeview = StoreView::find($params['id'])->update([
                'code' => $request->input('code'),
                'store_id' => $request->input('store_id'),
                'company_id' => $request->input('company_id'),
                'payment_provider' => $request->input('payment_provider'),
                'api_key_1' => encrypt($request->input('api_key_1')),
                'api_key_2' => encrypt($request->input('api_key_2')),
                'payment_additional_1' => encrypt($request->input('payment_additional_1')),
                'payment_additional_2' => encrypt($request->input('payment_additional_2')),
                'payment_additional_3' => encrypt($request->input('payment_additional_3')),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $storeview,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_update_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function allRoles()
    {
        $result = Role::all()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);

    }

    public function allScopes()
    {
        $result = Scope::all()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }
}
