<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get Magento data.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */

    public function allProducts(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $search_criteria = json_decode($request->get('searchCriteria'));

            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : ''
                ],
            ];
            $response = $client->request('GET', 'products', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_products',
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    /**
     * Get Magento data.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */

    public function getProduct(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $response = $client->request('GET', 'products/' . $params['sku']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_product',
                'message' => $e->getMessage(),
            ], 500);

        }
    }

    public function updateProduct(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $body = [
                'product' => $request->all(),
                'saveOptions' => true,
            ];

            $response = $client->request('PUT', 'products/' . $params['sku'], [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($body),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_update_product',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteProduct(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $client->request('DELETE', 'products/' . $params['sku']);

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_delete_product',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createProduct(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $product = $request->input('product');
            $payload = [
                'product' => $product,
            ];

            $response = $client->request('POST', 'products', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_create_product',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateMedia(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $deleteClient = $this->makeHttpClient('all');

            $files = $request->input('files');
            $imageIds = $request->input('imageIds');

            foreach ($imageIds as $id) {
                $deleteClient->request('DELETE', 'products/' . $params['sku'] . '/media/' . $id);
            }

            $count = 0;
            foreach ($files as $file) {
                $extension = explode('/', mime_content_type($file['data']['fileBase64']))[1];
                $base64Content = explode('base64,', $file['data']['fileBase64']);

                $payload = [
                    'entry' => [
                        'media_type' => 'image',
                        'position' => $count++,
                        'label' => 'new_picture',
                        'disabled' => false,
                        'types' => ['image'],
                        'file' => $params['sku'] . implode(explode(' ', $file['name'])),
                        'content' => [
                            'base64_encoded_data' => $base64Content[1],
                            'type' => 'image/' . $extension,
                            'name' => implode(explode(' ', $file['name'])),
                        ],
                    ],
                ];

                $client->request('POST', 'products/' . $params['sku'] . '/media', [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($payload),
                ]);
            }

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_delete_product',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAttributes(Request $request)
    {
        try {
            $client = $this->makeHttpClient('default');
            $search_criteria = json_decode($request->get('searchCriteria'));
            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : '',
                ],
            ];
            $response = $client->request('GET', 'products/attributes', $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_attributes',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createAttributes(Request $request)
    {
        try {
            $client = $this->makeHttpClient('default');
            $attribute = $request->input('attribute');
            $payload = [
                'attribute' => $attribute,
            ];

            $response = $client->request('POST', 'products/attributes', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_create_attributes',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAttributeOptions(Request $request)
    {
        try {
            $client = $this->makeHttpClient('default');
            $params = $request->route()->parameters();

            $response = $client->request('GET', 'products/attributes/' . $params['attributeCode'] . '/options');

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_attributes_options',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createAttributeOptions(Request $request)
    {
        try {
            $client = $this->makeHttpClient('default');
            $params = $request->route()->parameters();

            $option = $request->input('option');
            $payload = [
                'option' => $option,
            ];

            $response = $client->request('POST', 'products/attributes/' . $params['attributeCode'] . '/options', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($payload),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_attribute',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
