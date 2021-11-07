<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function allProducts(Request $request)
    {
        try {
            $client = $this->makeHttpClient();
            $params = $request->route()->parameters();
            $search_criteria = json_decode($request->get('searchCriteria'));

            $query = [
                'query' => [
                    'searchCriteria' => $search_criteria ? $search_criteria : '',
                ],
            ];
            $response = $client->request('GET', $params['scope'], $query);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_products',
                'message' => $e->getMessage(),
            ], $e->getCode());

        }
    }

    /**
     * Get Magento data.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getProduct(Request $request)
    {

        try {
            $client = $this->makeHttpClient();
            $params = $request->route()->parameters();
            $response = $client->request('GET', $params['scope'] . '/' . $params['sku']);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_product',
                'message' => $e->getMessage(),
            ], $e->getCode());

        }
    }

    public function updateProduct(Request $request)
    {
        try {
            $client = $this->makeHttpClient();
            $params = $request->route()->parameters();
            $body = [
                'product' => $request->all(),
                'saveOptions' => true,
            ];

            $response = $client->request('PUT', $params['scope'] . '/' . $params['sku'], [
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
            ], $e->getCode());
        }
    }

    public function deleteProduct(Request $request)
    {
        try {
            $client = $this->makeHttpClient();
            $params = $request->route()->parameters();

            $client->request('DELETE', $params['scope'] . '/' . $params['sku']);

            return response()->json([
                'status' => 'success',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_delete_product',
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    public function updateMedia(Request $request)
    {
        try {
            $client = $this->makeHttpClient();
            $params = $request->route()->parameters();
            $files = $request->input('files');
            $imageIds = $request->input('imageIds');

            foreach ($imageIds as $id) {
                $client->request('DELETE', $params['scope'] . '/' . $params['sku'] . '/media/' . $id);
            }

            $count = 0;
            foreach ($files as $file) {
                $extension = explode('/', mime_content_type($file['data']['fileBase64']))[1];
                $base64Content = explode('base64,', $file['data']['fileBase64']);

                $params = [
                    'entry' => [
                        'media_type' => 'image',
                        'position' => $count++,
                        'label' => 'new_picture',
                        'disabled' => false,
                        'types' => ['image'],
                        'file' => implode(explode(' ', $file['name'])),
                        'content' => [
                            'base64_encoded_data' => $base64Content[1],
                            'type' => 'image/' . $extension,
                            'name' => implode(explode(' ', $file['name'])),
                        ],
                    ],
                ];

                $client->request('POST', $params['scope'] . '/' . $params['sku'] . '/media', [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($params),
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
            ], $e->getCode());
        }
    }
}