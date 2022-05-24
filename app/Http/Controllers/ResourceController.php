<?php

namespace App\Http\Controllers;
use App\Models\Technician;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;

class ResourceController extends Controller
{

    public function getAttribute(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $attribute = Company::find($params('company_id'))->technicians();

            return response()->json([
                'status' => 'success',
                'data' => $attribute,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createAttribute(Request $request)
    {
        try {
            $request->validate([
                'name' => 'nullable|string',
                'timezone' => 'nullable|string',
                'working_days' => 'nullable|string',
                'store_views' => 'nullable|string',
            ]);

            $inputs = $request->all();
            $newAttribute = new Technician();
            foreach ($inputs as $key => $input) {
                $newAttribute[$key] = $input;
            }
            $newAttribute->save();

            // if (sizeof($request->input('store_views')) > 0)
            //     foreach($request->input('store_views') as $storeview) {
            //         $new = new AttributeGroupStoreView();
            //         $new->store_view =  $storeview;
            //         $new->save();
            //         $newAttribute->storeviews()->save($new);
            //     }            

            $params = $request->route()->parameters();
            $company = Company::find($params['companyId']);
            if ($company) {
                $company->technicians()->save($newAttribute);
            }

            return response()->json([
                'status' => 'success',
                'data' => $newAttribute,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_save_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateAttribute(Request $request)
    {
        try {
            $request->validate([
                'name' => 'nullable|string',
                'timezone' => 'nullable|string',
                'working_days' => 'nullable|string',
                'store_views' => 'nullable|string',
            ]);

            $params = $request->route()->parameters();
            $attribute = Company::findOrFail($params['companyId'])->technicians()->findorFail($params['id']);
            if ($attribute) {
                $attribute->update([
                    'name' => $request->input('name'),
                    'timezone' => $request->input('timezone'),
                    'working_days' => $request->input('working_days'),
                    'store_views' => $request->input('store_views'),
                ]);
    
                // if ($attribute) $attribute->storeviews()->delete();

                // if (sizeof($request->input('store_views')) > 0)
                //     foreach($request->input('store_views') as $storeview) {
                //         $new = new AttributeGroupStoreView();
                //         $new->store_view =  $storeview;
                //         $new->save();
                //         $attribute->storeviews()->save($new);
                //     }
            }

            return response()->json([
                'status' => 'success',
                'data' => $attribute,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_update_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteAttribute(Request $request)
    {
        try {

            $params = $request->route()->parameters();
            $attribute = Company::findOrFail($params['companyId'])->technicians()->findorFail($params['id'])->delete();

            return response()->json([
                'status' => 'success',
                'data' => $attribute,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_Attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
