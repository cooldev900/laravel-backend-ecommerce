<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Checkout\CheckoutApiException;
use Checkout\CheckoutArgumentException;
use Checkout\CheckoutAuthorizationException;
use Checkout\CheckoutDefaultSdk;
use Checkout\Environment;
use Checkout\Payments\CaptureRequest;
use Checkout\Payments\RefundRequest;
use Checkout\Payments\VoidRequest;

class CheckoutComController extends Controller
{
    protected function getApi($public_key, $secret_key) {
        $builder = CheckoutDefaultSdk::staticKeys();
        $builder->setPublicKey($public_key);
        $builder->setSecretKey($secret_key);
        $builder->setEnvironment(Environment::sandbox()); // or Environment::production()
        return $builder->build();        
    }

    public function capturePayment(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
                'reference' => 'string | nullable',
                'amount' => 'numeric | nullable'
            ]);

            $params = $request->route()->parameters();
            // $client = $this->makePaypalClient($params['store_view']);
            $id = $request->input('id');

            $api = $this->getApi("public_key", "secret_key");

            $captureRequest = new CaptureRequest();
            $captureRequest->reference = $request->input('reference');
            $captureRequest->amount = $request->input('amount');
            
            $response = $api->getPaymentsClient()->capturePayment($id, $captureRequest);

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function refundPayment(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
                'reference' => 'string | nullable',
                'amount' => 'numeric | nullable'
            ]);

            $params = $request->route()->parameters();
            // $client = $this->makePaypalClient($params['store_view']);
            $id = $request->input('id');

            $api = $this->getApi("public_key", "secret_key");

            $refundRequest = new RefundRequest();
            $refundRequest->reference = $request->input('reference');
            $refundRequest->amount = $request->input('amount');
            
            $response = $api->getPaymentsClient()->refundPayment($id, $refundRequest);

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function voidPayment(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
                'reference' => 'string | nullable',
            ]);

            $params = $request->route()->parameters();
            // $client = $this->makePaypalClient($params['store_view']);
            $id = $request->input('id');

            $api = $this->getApi("public_key", "secret_key");

            $voidRequest = new VoidRequest();
            $voidRequest->reference = $request->input('reference');
            
            $response = $api->getPaymentsClient()->refundPayment($id, $voidRequest);

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPaymentDetails(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|string',
            ]);

            $params = $request->route()->parameters();
            // $client = $this->makePaypalClient($params['store_view']);
            $id = $request->input('id');

            $api = $this->getApi("public_key", "secret_key");
            
            $response = $api->getPaymentsClient()->getPaymentDetails($id);

            return response()->json([
                'status' => 'success',
                'data' => $response,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_transaction',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
