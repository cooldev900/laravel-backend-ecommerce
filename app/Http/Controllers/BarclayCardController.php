<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Company;
use App\Models\StoreView;

class BarclayCardController extends Controller
{
    public function getTransaction(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $queries = $request->query();
            $httpClient = new Client();
            $user = JWTAuth::user();
            $company = Company::where('name', $user->company_name)->firstOrFail();
            $db_store_view = StoreView::where('company_id', $company->id)->where('code', $params['store_view'])->firstOrFail();

            $response = $httpClient->post('https://mdepayments.epdq.co.uk/ncol/test/querydirect.asp', [
                'headers' => ['Accept' => 'application/xml'],
                'form_params' => [
                    'PAYID' => $queries['payId'],
                    'PSPID' => decrypt($db_store_view->payment_additional_1),
                    'PSWD' => decrypt($db_store_view->api_key_2),
                    'USERID' => decrypt($db_store_view->api_key_1)
                ],
                'timeout' => 120
            ])->getBody()->getContents();
            $xmlData = simplexml_load_string($response);

            return response()->json([
                'status' => 'success',
                'data' => $xmlData->attributes(),
            ], 200);
        } catch (GuzzleException $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createRefund(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $payId = $request->input('payment_id');
            $isFullAmount = $request->input('is_full_amount');
            $user = JWTAuth::user();
            $company = Company::where('name', $user->company_name)->firstOrFail();
            $db_store_view = StoreView::where('company_id', $company->id)->where('code', $params['store_view'])->firstOrFail();
            $pass_phrase = '99f04182-b208-42dc-87c3-1c1317449733';
            $PSPID = decrypt($db_store_view->payment_additional_1);
            $PSWD = decrypt($db_store_view->api_key_2);
            $USERID = decrypt($db_store_view->api_key_1);
            $OPERATION = $isFullAmount ? 'RFS' : 'RFD';
            $AMOUNT = $request->input('amount_to_capture');

            $sha_signature = "AMOUNT={$AMOUNT}{$pass_phrase}OPERATION={$OPERATION}{$pass_phrase}PAYID={$payId}{$pass_phrase}PSPID={$PSPID}{$pass_phrase}PSWD={$PSWD}{$pass_phrase}USERID={$USERID}{$pass_phrase}";
            $sha_sign = strtoupper(hash('sha256', $sha_signature));

            $httpClient = new Client();
            $response = $httpClient->post('https://mdepayments.epdq.co.uk/ncol/test/maintenancedirect.asp', [
                'headers' => ['Accept' => 'application/xml'],
                'form_params' => [
                    'AMOUNT' => $AMOUNT,
                    'OPERATION' => $OPERATION,
                    'PAYID' => $payId,
                    'PSPID' => $PSPID,
                    'PSWD' => $PSWD,
                    'USERID' => $USERID,
                    'SHASIGN' => $sha_sign,
                ],
                'timeout' => 500
            ])->getBody()->getContents();
            $xmlData = simplexml_load_string($response);

            return response()->json([
                'status' => 'success',
                'data' => $xmlData->attributes(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function capturePaymentIntent(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $payId = $request->input('payment_id');
            $isFullAmount = $request->input('is_full_amount');
            $user = JWTAuth::user();
            $company = Company::where('name', $user->company_name)->firstOrFail();
            $db_store_view = StoreView::where('company_id', $company->id)->where('code', $params['store_view'])->firstOrFail();
            $pass_phrase = '99f04182-b208-42dc-87c3-1c1317449733';
            $PSPID = decrypt($db_store_view->payment_additional_1);
            $PSWD = decrypt($db_store_view->api_key_2);
            $USERID = decrypt($db_store_view->api_key_1);
            $OPERATION = $isFullAmount ? 'SAS' : 'SAL';
            $AMOUNT = $request->input('amount_to_capture');

            $sha_signature = "AMOUNT={$AMOUNT}{$pass_phrase}OPERATION={$OPERATION}{$pass_phrase}PAYID={$payId}{$pass_phrase}PSPID={$PSPID}{$pass_phrase}PSWD={$PSWD}{$pass_phrase}USERID={$USERID}{$pass_phrase}";
            $sha_sign = strtoupper(hash('sha256', $sha_signature));

            $httpClient = new Client();
            $response = $httpClient->post('https://mdepayments.epdq.co.uk/ncol/test/maintenancedirect.asp', [
                'headers' => ['Accept' => 'application/xml'],
                'form_params' => [
                    'AMOUNT' => $AMOUNT,
                    'OPERATION' => $OPERATION,
                    'PAYID' => $payId,
                    'PSPID' => $PSPID,
                    'PSWD' => $PSWD,
                    'USERID' => $USERID,
                    'SHASIGN' => $sha_sign,
                ],
                'timeout' => 500
            ])->getBody()->getContents();
            $xmlData = simplexml_load_string($response);

            return response()->json([
                'status' => 'success',
                'data' => $xmlData->attributes(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
