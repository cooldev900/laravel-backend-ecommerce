<?php

namespace App\Http\Controllers;

use App\Models\Company;

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
}