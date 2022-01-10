<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function allCompanies()
    {
        $companies = Company::all()->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);

        $result = [];
        foreach ($companies as $company) {
            $company->url = decrypt($company->url);

            array_push($result, $company);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }

    public function getCompany(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $company = Company::find($params['id'])->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);
            $company->url = decrypt($company->url);
            $locations = CompanyLocation::where('company_id', $params['id'])->get()->toArray();
            $users = User::where('company_name', $company->name)->get()->makeHidden(['password', 'created_at', 'updated_at'])->toArray();
            $company->locations = array_column($locations, 'locations');
            $company->users = $users;

            return response()->json([
                'status' => 'success',
                'data' => $company,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteCompany(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $company = Company::find($params['id']);
            $users = User::where('company_name', $company->name);
            $companyLocations = CompanyLocation::where('company_id', $params['id']);

            $users->delete();
            $companyLocations->delete();
            $company->delete();

            return response()->json([
                'status' => 'success',
                'data' => $company,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}