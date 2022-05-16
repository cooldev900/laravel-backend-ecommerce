<?php

namespace App\Http\Controllers;
use App\Models\Attribute;
use App\Models\Company;
use Exception;
use Illuminate\Http\Request;

class AttributeController extends Controller
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
                'code' => 'nullable|string',
                'enhance_title' => 'nullable|string',
                'used_as_product_option' => 'boolean',
            ]);

            $inputs = $request->all();
            $newAttribute = new Attribute();
            foreach ($inputs as $key => $input) {
                if ($key === 'client_id') {  
                    $newAttribute['company_id'] = $input;                      
                    continue;
                }
                $newAttribute[$key] = $input;
            }
            $newAttribute->save();

            $params = $request->route()->parameters();
            $company = Company::find($params['companyId']);
            if ($company) {
                $company->attributes()->save($newAttribute);
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
                'code' => 'nullable|string',
                'used_as_product_option' => 'boolean',
                'enhance_title' => 'nullable|string',
            ]);

            $params = $request->route()->parameters();
            $attribute = Company::findOrFail($params['companyId'])->attributes()->findorFail($params['id'])->update([
                'name' => $request->input('name'),
                'code' => $request->input('code'),
                'used_as_product_option' => $request->input('used_as_product_option'),
                'enhance_title' => $request->input('enhance_title'),
            ]);           

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
            $attribute = Company::findOrFail($params['companyId'])->attributes()->findorFail($params['id'])->delete();

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
