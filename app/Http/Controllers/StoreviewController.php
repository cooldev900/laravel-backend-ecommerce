<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Scope;
use App\Models\StoreView;
use App\Models\UserPermission;
use Exception;
use Illuminate\Http\Request;

class StoreviewController extends Controller
{
    public function allStoreviews(Request $request)
    {
        try {
            $client_id = $request->get('client_id');

            if (isset($client_id)) {
                $storeViews = StoreView::where('company_id', $client_id)->get()->toArray();
            } else {
                $storeViews = StoreView::all()->toArray();
            }

            $result = [];
            foreach ($storeViews as $storeView) {
                if ($storeView['company']) {
                    $storeView['company'] = [
                        'id' => $storeView['company']['id'],
                        'name' => $storeView['company']['name'],
                    ];
                }

                array_push($result, $storeView);
            }

            return response()->json([
                'status' => 'success',
                'data' => $result,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_storeviews',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStoreview(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $storeview = StoreView::find($params['id']);

            if ($storeview['company']) {
                $hiddenColumns = ['consumer_key', 'consumer_secret', 'token', 'token_secret', 'url', 'magento_id', 'email_password'];
                foreach ($hiddenColumns as $column) {
                    unset($storeview['company'][$column]);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $storeview,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     * path="/api/storeviews",
     * summary="Create storeview",
     * description="Create storeview",
     * operationId="storeviews",
     * tags={"Storeviews"},
     * security={{"bearer_token": {}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Create storeview",
     *    @OA\JsonContent(
     *       required={"code","company_id", "paypal", "stripe", "cybersource", "checkoutcom"},
     *       @OA\Property(property="code", type="string", example="31"),
     *       @OA\Property(property="store_id", type="numeric", example="1"),
     *       @OA\Property(property="company_id", type="numeric", example="1"),
     *       @OA\Property(property="payment_provider", type="string", example="paypal"),
     *       @OA\Property(property="api_key_1", type="string", example="123456"),
     *       @OA\Property(property="api_key_2", type="string", example="123456"),
     *       @OA\Property(property="api_key_3", type="string", example="123456"),
     *       @OA\Property(property="payment_additional_1", type="string", example="123456"),
     *       @OA\Property(property="payment_additional_2", type="string", example="123456"),
     *       @OA\Property(property="payment_additional_3", type="string", example="123456"),
     *       @OA\Property(property="es_url", type="string", example="https://es_url"),
     *       @OA\Property(property="es_index", type="string", example="glyn_index"),
     *       @OA\Property(property="es_username", type="string", example="Omni"),
     *       @OA\Property(property="es_password", type="string", example="123456"),
     *       @OA\Property(property="vsf_url", type="string", example=""),
     *       @OA\Property(property="vsf_preview", type="string", example=""),
     *       @OA\Property(property="email_domain", type="string", example=""),
     *       @OA\Property(property="email_password", type="string", example=""),
     *       @OA\Property(property="email_sender", type="string", example=""),
     *       @OA\Property(property="website_id", type="string", example=""),
     *       @OA\Property(property="whitelist", type="string", example=""),
     *       @OA\Property(property="webhook_token", type="string", example=""),
     *       @OA\Property(property="language", type="string", example=""),
     *       @OA\Property(property="currency", type="string", example=""),
     *       @OA\Property(property="paypal", type="object",  ref="#/components/schemas/Paypal"),
     *       @OA\Property(property="stripe", type="object",  ref="#/components/schemas/Stripe"),
     *       @OA\Property(property="cybersource", type="object",  ref="#/components/schemas/Cybersource"),
     *       @OA\Property(property="checkoutcom", type="object",  ref="#/components/schemas/CheckoutCom"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object", ref="#/components/schemas/StoreView"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="fail_create_storeview"),
     *       @OA\Property(property="message", type="string", example="")
     *        )
     *     )
     * )
     */

    public function createStoreview(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'store_id' => 'numeric',
                'company_id' => 'required|numeric',
                'payment_provider' => 'nullable|string',
                'api_key_1' => 'nullable|string',
                'api_key_2' => 'nullable|string',
                'payment_additional_1' => 'nullable|string',
                'payment_additional_2' => 'nullable|string',
                'payment_additional_3' => 'nullable|string',
                'es_url' => 'nullable|string',
                'es_index' => 'nullable|string',
                'es_username' => 'nullable|string',
                'es_password' => 'nullable|string',
                'vsf_url' => 'nullable|string',
                'vsf_preview' => 'nullable|string',
                'email_domain' => 'nullable|string',
                'email_password' => 'nullable|string',
                'email_sender' => 'nullable|string',
                'website_id' => 'nullable|string',
                'whitelist' => 'nullable|string',
                'webhook_token' => 'nullable|string',
                'language' => 'nullable|string',
                'currency' => 'nullable|string',
                'currency_code' => 'nullable|string',
            ]);

            $inputs = $request->all();
            $newStoreView = new StoreView();
            foreach ($inputs as $key => $input) {
                if ($key === 'paypal' || $key === 'stripe' || $key === 'cybersource' || $key === 'checkoutcom'  || $key === 'checkoutcom2'   || $key === 'barclaysenterprise')
                    continue;
                if (($key === 'api_key_1'
                    || $key === 'api_key_2' || $key === 'payment_additional_1'
                    || $key === 'payment_additional_2' || $key === 'payment_additional_3'
                    || $key === 'es_password' || $key === 'email_password') && ($input !== null && trim($input) !== '')) {
                    $newStoreView[$key] = encrypt($input);
                } else {
                    $newStoreView[$key] = $input;
                }
            }
            $newStoreView->save();
            
            $newStoreView->paypal()->create($this->encryptPaypalKeys($inputs['paypal']));
            $newStoreView->stripe()->create($this->encryptStripeKeys($inputs['stripe']));
            $newStoreView->cybersource()->create($this->encryptCybersourceKeys($inputs['cybersource']));
            $newStoreView->checkoutcom()->create($this->encryptCheckoutcomKeys($inputs['checkoutcom']));
            $newStoreView->checkoutcom2()->create($this->encryptCheckoutcom2Keys($inputs['checkoutcom2']));
            $newStoreView->barclaysenterprise()->create($this->encryptBarclaysEnterpriseKeys($inputs['barclaysenterprise']));

            return response()->json([
                'status' => 'success',
                'data' => $newStoreView,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_create_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function encryptPaypalKeys($values) {
        if (!sizeof($values)) return $values;
        foreach($values as $key => $value) {
            if ($key === 'client_id') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'client_secret') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'public_key') $values[$key] = encrypt(bin2hex($value));
        }
        return $values;
    }

    private function encryptStripeKeys($values) {
        if (!sizeof($values)) return $values;
        foreach($values as $key => $value) {
            if ($key === 'public_api_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'secret_api_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'webhook_secret') $values[$key] = encrypt(bin2hex($value));
        }
        return $values;
    }

    private function encryptCybersourceKeys($values) {
        if (!sizeof($values)) return $values;
        foreach($values as $key => $value) {
            if ($key === 'merchant_id') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'shared_secret_key') $values[$key] = encrypt(bin2hex($value));
        }
        return $values;
    }

    private function encryptCheckoutcomKeys($values) {
        if (!sizeof($values)) return $values;
        foreach($values as $key => $value) {
            if ($key === 'public_api_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'secret_api_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'webhook_secret') $values[$key] = encrypt(bin2hex($value));
        }
        return $values;
    }

    private function encryptCheckoutcom2Keys($values) {
        if (!sizeof($values)) return $values;
        foreach($values as $key => $value) {
            if ($key === 'public_api_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'secret_api_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'webhook_secret') $values[$key] = encrypt(bin2hex($value));
        }
        return $values;
    }

    private function encryptBarclaysEnterpriseKeys($values) {
        if (!sizeof($values)) return $values;
        foreach($values as $key => $value) {
            if ($key === 'client_id') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'secret_key') $values[$key] = encrypt(bin2hex($value));
            if ($key === 'enterprise') $values[$key] = encrypt(bin2hex($value));
        }
        return $values;
    }

    /**
     * @OA\Delete(
     * path="/api/storeviews/{id}",
     * summary="Delete a storeview",
     * description="Delete a storeview",
     * operationId="deleteStoreview",
     * security={{"bearer_token": {}}},
     *      @OA\Parameter(
     *          name="email",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     * tags={"Storeviews"},
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object", ref="#/components/schemas/StoreView"),
     *     )
     *  ),
     * @OA\Response(
     *    response=500,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="fail_create_storeview"),
     *       @OA\Property(property="message", type="string", example="")
     *        )
     *     )
     * )
     */

    public function deleteStoreview(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $userPermissions = UserPermission::where('store_view_id', $params['id']);
            $userPermissions->delete();

            $storeview = StoreView::find($params['id']);
            $storeview->paypal()->delete();
            $storeview->stripe()->delete();
            $storeview->cybersource()->delete();
            $storeview->checkoutcom()->delete();
            $storeview->checkoutcom2()->delete();
            $storeview->barclaysenterprise()->delete();
            $storeview->delete();

            return response()->json([
                'status' => 'success',
                'data' => $storeview,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_delete_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Put(
     * path="/api/storeviews/{id}",
     * summary="Update a storeview",
     * description="Update a storeview",
     * operationId="udpdateStoreviews",
     * tags={"Storeviews"},
     * security={{"bearer_token": {}}},
     *      @OA\Parameter(
     *          name="email",
     *          in="path",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     * @OA\RequestBody(
     *    required=true,
     *    description="Create storeview",
     *    @OA\JsonContent(
     *       required={"code","company_id", "paypal", "stripe", "cybersource", "checkoutcom"},
     *       @OA\Property(property="code", type="string", example="31"),
     *       @OA\Property(property="store_id", type="numeric", example="1"),
     *       @OA\Property(property="company_id", type="numeric", example="1"),
     *       @OA\Property(property="payment_provider", type="string", example="paypal"),
     *       @OA\Property(property="api_key_1", type="string", example="123456"),
     *       @OA\Property(property="api_key_2", type="string", example="123456"),
     *       @OA\Property(property="api_key_3", type="string", example="123456"),
     *       @OA\Property(property="payment_additional_1", type="string", example="123456"),
     *       @OA\Property(property="payment_additional_2", type="string", example="123456"),
     *       @OA\Property(property="payment_additional_3", type="string", example="123456"),
     *       @OA\Property(property="es_url", type="string", example="https://es_url"),
     *       @OA\Property(property="es_index", type="string", example="glyn_index"),
     *       @OA\Property(property="es_username", type="string", example="Omni"),
     *       @OA\Property(property="es_password", type="string", example="123456"),
     *       @OA\Property(property="vsf_url", type="string", example=""),
     *       @OA\Property(property="vsf_preview", type="string", example=""),
     *       @OA\Property(property="email_domain", type="string", example=""),
     *       @OA\Property(property="email_password", type="string", example=""),
     *       @OA\Property(property="email_sender", type="string", example=""),
     *       @OA\Property(property="website_id", type="string", example=""),
     *       @OA\Property(property="whitelist", type="string", example=""),
     *       @OA\Property(property="webhook_token", type="string", example=""),
     *       @OA\Property(property="language", type="string", example=""),
     *       @OA\Property(property="currency", type="string", example=""),
     *       @OA\Property(property="paypal", type="object",  ref="#/components/schemas/Paypal"),
     *       @OA\Property(property="stripe", type="object",  ref="#/components/schemas/Stripe"),
     *       @OA\Property(property="cybersource", type="object",  ref="#/components/schemas/Cybersource"),
     *       @OA\Property(property="checkoutcom", type="object",  ref="#/components/schemas/CheckoutCom"),
     *    ),
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Success",
     *     @OA\JsonContent(
     *        @OA\Property(property="status", type="string", example="success"),
     *        @OA\Property(property="data", type="object", ref="#/components/schemas/StoreView"),
     *     )
     *  ),
     * @OA\Response(
     *    response=422,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="status", type="string", example="error"),
     *       @OA\Property(property="error", type="string", example="fail_create_storeview"),
     *       @OA\Property(property="message", type="string", example="")
     *        )
     *     )
     * )
     */

    public function updateStoreview(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string',
                'store_id' => 'numeric',
                'company_id' => 'required|numeric',
                'payment_provider' => 'nullable|string',
                'api_key_1' => 'nullable|string',
                'api_key_2' => 'nullable|string',
                'payment_additional_1' => 'nullable|string',
                'payment_additional_2' => 'nullable|string',
                'payment_additional_3' => 'nullable|string',
                'es_url' => 'nullable|string',
                'es_index' => 'nullable|string',
                'es_username' => 'nullable|string',
                'es_password' => 'nullable|string',
                'vsf_url' => 'nullable|string',
                'vsf_preview' => 'nullable|string',
                'email_domain' => 'nullable|string',
                'email_password' => 'nullable|string',
                'email_sender' => 'nullable|string',
                'website_id' => 'nullable|string',
                'whitelist' => 'nullable|string',
                'webhook_token' => 'nullable|string',
                'language' => 'nullable|string',
                'currency' => 'nullable|string',
                'currency_code' => 'nullable|string',
            ]);

            $params = $request->route()->parameters();
            $originStoreview = StoreView::find($params['id']);
            $storeview = $originStoreview->update([
                'code' => $request->input('code'),
                'store_id' => $request->input('store_id'),
                'company_id' => $request->input('company_id'),
                'payment_provider' => $request->input('payment_provider') ?? $originStoreview->payment_provider,
                'api_key_1' => $request->input('api_key_1') ?
                                encrypt($request->input('api_key_1')) : $originStoreview->api_key_1,
                'api_key_2' => $request->input('api_key_2') ?
                                encrypt($request->input('api_key_2')) : $originStoreview->api_key_2,
                'payment_additional_1' => $request->input('payment_additional_1') ?
                                            encrypt($request->input('payment_additional_1')) : $originStoreview->payment_additional_1,
                'payment_additional_2' => $request->input('payment_additional_2') ?
                                            encrypt($request->input('payment_additional_2')) : $originStoreview->payment_additional_3,
                'payment_additional_3' => $request->input('payment_additional_3') ?
                                            encrypt($request->input('payment_additional_3')) : $originStoreview->payment_additional_3,
                'es_url' => $request->input('es_url') ?? $originStoreview->es_url,
                'es_index' => $request->input('es_index') ?? $originStoreview->es_index,
                'es_username' => $request->input('es_username') ?? $originStoreview->es_username,
                'es_password' => $request->input('es_password') ?
                                    encrypt($request->input('es_password')) : $originStoreview->es_password,
                'vsf_url' => $request->input('vsf_url') ?? $originStoreview->vsf_url,
                'vsf_preview' => $request->input('vsf_preview') ?? $originStoreview->vsf_preview,
                'email_domain' => $request->input('email_domain') ?? $originStoreview->email_domain,
                'email_password' => $request->input('email_password') ?? encrypt($originStoreview->email_password),
                'email_sender' => $request->input('email_sender') ?? $originStoreview->email_sender,
                'website_id' => $request->input('website_id') ?? $originStoreview->website_id,
                'whitelist' => $request->input('whitelist') ?? $originStoreview->whitelist,
                'webhook_token' => $request->input('webhook_token') ?? $originStoreview->webhook_token,
                'language' => $request->input('language') ?? $originStoreview->language,
                'currency' => $request->input('currency') ?? $originStoreview->currency,
                'currency_code' => $request->input('currency_code') ?? $originStoreview->currency_code,
                'shipment_with_label' => $request->input('shipment_with_label') ?? $originStoreview->shipment_with_label,
                'shipment_without_label' => $request->input('shipment_without_label') ?? $originStoreview->shipment_without_label,
                'shipment_without_tracking' => $request->input('shipment_without_tracking') ?? $originStoreview->shipment_without_tracking,
                'default_provider' => $request->input('default_provider') ?? $originStoreview->default_provider,
            ]);

            if (!is_null($originStoreview['paypal'])) $originStoreview->paypal()->update($this->encryptPaypalKeys($request->input('paypal')));
            else $originStoreview->paypal()->create($this->encryptPaypalKeys($request->input('paypal')));
            if (!is_null($originStoreview['stripe'])) $originStoreview->stripe()->update($this->encryptStripeKeys($request->input('stripe')));
            else $originStoreview->stripe()->create($this->encryptStripeKeys($request->input('stripe')));
            if (!is_null($originStoreview['cybersource'])) $originStoreview->cybersource()->update($this->encryptCybersourceKeys($request->input('cybersource')));
            else $originStoreview->cybersource()->create($this->encryptCybersourceKeys($request->input('cybersource')));
            if (!is_null($originStoreview['checkoutcom'])) $originStoreview->checkoutcom()->update($this->encryptCheckoutcomKeys($request->input('checkoutcom')));
            else $originStoreview->checkoutcom()->create($this->encryptCheckoutcomKeys($request->input('checkoutcom')));
            if (!is_null($originStoreview['checkoutcom2'])) $originStoreview->checkoutcom2()->update($this->encryptCheckoutcom2Keys($request->input('checkoutcom2')));
            else $originStoreview->checkoutcom2()->create($this->encryptCheckoutcom2Keys($request->input('checkoutcom2')));
            if (!is_null($originStoreview['barclaysenterprise'])) $originStoreview->barclaysenterprise()->update($this->encryptBarclaysEnterpriseKeys($request->input('barclaysenterprise')));
            else $originStoreview->barclaysenterprise()->create($this->encryptBarclaysEnterpriseKeys($request->input('barclaysenterprise')));

            return response()->json([
                'status' => 'success',
                'data' => $originStoreview,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_update_storeview',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function allRoles()
    {
        $result = Role::all()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }

    public function allScopes()
    {
        $result = Scope::all()->toArray();

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ], 200);
    }
}
