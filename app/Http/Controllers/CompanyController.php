<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyLocation;
use App\Models\User;
use App\Models\Attribute;
use Exception;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Boolean;

class CompanyController extends Controller
{
    public function allCompanies()
    {
        try {
            $companies = Company::all()->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret'])->toArray();

            return response()->json([
                'status' => 'success',
                'data' => $companies,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_clients',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCompany(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $company = Company::find($params['id'])
                ->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);
            $locations = CompanyLocation::where('company_id', $params['id'])->get()->toArray();
            $users = User::where('company_name', $company->name)->get()
                ->makeHidden(['password', 'created_at', 'updated_at'])->toArray();
            $company->locations = array_column($locations, 'locations');
            $_locations = [];
            foreach ($company->locations as $location) {
                unset($location['api_token']);
                array_push($_locations, $location);
            }
            $return_data = $company->get()->toArray();
            // $company->locations = $_locations;
            $return_data['locations'] = $return_data;

            $resultUser = [];
            foreach ($users as $user) {
                array_push($resultUser, $this->getPermission($user));
            }
            // $company->users = $resultUser;
            $return_data['users'] = $resultUser;
            $return_data['attributes'] = $company->attributes;

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
                'url' => 'nullable|string',
                'consumer_key' => 'nullable|string',
                'consumer_secret' => 'nullable|string',
                'token' => 'nullable|string',
                'token_secret' => 'nullable|string',
                'image_base_url' => 'nullable|string'
            ]);
            $inputs = $request->all();

            $company = new Company();
            $nonEncryptedFields = ['name', 'id', 'image_base_url', 'url'];
            foreach ($inputs as $key => $input) {
                if (array_search($key, $nonEncryptedFields) !== false) {
                    $company[$key] = $input;
                } else {
                    $company[$key] = encrypt($input);

                }
            };
            $company->save();

            $result = $company->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);

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
                'url' => 'nullable|string',
                'consumer_key' => 'nullable|string',
                'consumer_secret' => 'nullable|string',
                'token' => 'nullable|string',
                'token_secret' => 'nullable|string',
                'image_base_url' => 'nullable|string'
            ]);

            $params = $request->route()->parameters();
            $inputs = $request->all();

            $nonEncryptedFields = ['name', 'id', 'image_base_url', 'url'];
            $company = Company::find($params['id']);

            foreach ($inputs as $key => $value) {
                if (array_search($key, $nonEncryptedFields) !== false) {
                    $company[$key] = $value;
                } else {
                    if ($value !== '' && $value) {
                        $company[$key] = encrypt($value);
                    }
                }
            };
            $company->save();

            $result = $company->makeHidden(['consumer_key', 'consumer_secret', 'token', 'token_secret']);

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
