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
            $company = Company::find($params['id'])
                ->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);
            $company->url = decrypt($company->url);
            $locations = CompanyLocation::where('company_id', $params['id'])->get()->toArray();
            $users = User::where('company_name', $company->name)->get()
                ->makeHidden(['password', 'created_at', 'updated_at'])->toArray();
            $company->locations = array_column($locations, 'locations');

            $resultUser = [];
            foreach ($users as $user) {
                array_push($resultUser, $this->getPermission($user));
            }
            $company->users = $resultUser;

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

    public function createCompany(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'url' => 'string',
                'consumer_key' => 'string',
                'consumer_secret' => 'string',
                'token' => 'string',
                'token_secret' => 'string',
            ]);
            $inputs = $request->all();

            $company = new Company();
            foreach ($inputs as $key => $input) {
                if ($key === 'name' || $key === 'id') {
                    $company[$key] = $input;
                } else {
                    $company[$key] = encrypt($input);

                }
            };
            $company->save();

            $result = $company->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);
            $result->url = decrypt($result->url);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_create_client',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateCompany(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'url' => 'string',
                'consumer_key' => 'string',
                'consumer_secret' => 'string',
                'token' => 'string',
                'image_base_url' => 'string',
                'token_secret' => 'string',
            ]);

            $params = $request->route()->parameters();
            $inputs = $request->all();

            $company = Company::find($params['id'])
                ->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);
            foreach ($inputs as $key => $input) {
                if ($key === 'name' || $key === 'id' || $key === 'image_base_url') {
                    $company[$key] = $input;
                } else {
                    if ($input !== '') {
                        $company[$key] = encrypt($input);
                    }
                }
            };
            $company->save();

            $result = $company->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);
            $result->url = decrypt($result->url);

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_update_client',
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
