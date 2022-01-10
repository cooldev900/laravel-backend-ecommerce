<?php

namespace App\Http\Controllers;

use App\Models\StoreView;
use App\Models\UserPermission;
use Exception;
use Illuminate\Http\Request;

class StoreviewController extends Controller
{
    public function allStoreviews()
    {
        $storeViews = StoreView::all();

        $result = [];
        foreach ($storeViews as $storeView) {
            array_push($result, $storeView);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }

    public function getStoreview(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $storeview = StoreView::find($params['id']);

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
            ]);

            $newStoreView = new StoreView();
            $newStoreView->code = $request->input('code');
            $newStoreView->save();

            return response()->json([
                'status' => 'success',
                'data' => $newStoreView,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_storeview',
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
            ]);

            $params = $request->route()->parameters();
            $storeview = StoreView::find($params['id'])->update([
                'code' => $request->input('code'),
            ]);

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
}