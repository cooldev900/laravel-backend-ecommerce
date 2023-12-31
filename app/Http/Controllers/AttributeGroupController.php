<?php

namespace App\Http\Controllers;
use App\Models\AttributeGroup;
use App\Models\Company;
use App\Models\AttributeGroupStoreView;
use Exception;
use Illuminate\Http\Request;

class AttributeGroupController extends Controller
{

    public function getAttribute(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $attribute = Company::find($params('company_id'))->attributes();

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
                'attribute_id' => 'nullable|string',
                'store_views' => 'nullable|array',
                'product_tool_1' => 'nullable|boolean',
                'product_tool_2' => 'nullable|boolean',
                'product_tool_3' => 'nullable|boolean',
                'product_tool_4' => 'nullable|boolean',
            ]);

            $inputs = $request->all();
            $newAttribute = new AttributeGroup();
            foreach ($inputs as $key => $input) {
                if ($key == 'store_views') continue;
                $newAttribute[$key] = $input;
            }
            $newAttribute->save();

            if (sizeof($request->input('store_views')) > 0)
                foreach($request->input('store_views') as $storeview) {
                    $new = new AttributeGroupStoreView();
                    $new->store_view =  $storeview;
                    $new->save();
                    $newAttribute->storeviews()->save($new);
                }            

            $params = $request->route()->parameters();
            $company = Company::find($params['companyId']);
            if ($company) {
                $company->attribute_groups()->save($newAttribute);
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
                'attribute_id' => 'nullable|string',
                'store_views' => 'nullable|array',
                'product_tool_1' => 'nullable|boolean',
                'product_tool_2' => 'nullable|boolean',
                'product_tool_3' => 'nullable|boolean',
                'product_tool_4' => 'nullable|boolean',
            ]);

            $params = $request->route()->parameters();
            $attribute = Company::findOrFail($params['companyId'])->attribute_groups()->findorFail($params['id']);
            if ($attribute) {
                $attribute->update([
                    'name' => $request->input('name'),
                    'attribute_id' => $request->input('attribute_id'),
                    'product_tool_1' => $request->input('product_tool_1'),
                    'product_tool_2' => $request->input('product_tool_2'),
                    'product_tool_3' => $request->input('product_tool_3'),
                    'product_tool_4' => $request->input('product_tool_4'),
                ]);
    
                if ($attribute) $attribute->storeviews()->delete();

                if (sizeof($request->input('store_views')) > 0)
                    foreach($request->input('store_views') as $storeview) {
                        $new = new AttributeGroupStoreView();
                        $new->store_view =  $storeview;
                        $new->save();
                        $attribute->storeviews()->save($new);
                    }
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
            $attribute = Company::findOrFail($params['companyId'])->attribute_groups()->findorFail($params['id'])->delete();

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
