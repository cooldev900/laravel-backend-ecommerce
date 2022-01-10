<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Exception;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function allCompanies()
    {
        $companies = Company::all();

        $result = [];
        foreach ($companies as $company) {
            $company->consumer_key = decrypt($company->consumer_key);
            $company->consumer_secret = decrypt($company->consumer_secret);
            $company->token = decrypt($company->token);
            $company->token_secret = decrypt($company->token_secret);
            $company->url = decrypt($company->url);

            array_push($result, $company);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }

    public function deleteCompany(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $company = Company::find($params['id']);
            $company->delete();

            return response()->json([
                'status' => 'success',
                'data' => $company,
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