<?php

namespace App\Http\Controllers;

use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use PhpParser\Node\Expr;

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

    public function gqlProducts(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeGraphqlClient($params['store_view']);

            $search = $request->get('search') ?? '';
            $pageSize = $request->get('pageSize') ?? 25;
            $currentPage = $request->get('currentPage') ?? 1;
            $filter = json_decode($request->get('filter')) ?? json_decode('{}');

            $query = '
                query productsList($search: String = "", $filter: ProductAttributeFilterInput, $pageSize: Int = 25, $currentPage: Int = 1){
                    products(
                        search: $search
                        filter: $filter
                        pageSize: $pageSize
                        currentPage: $currentPage
                    ) {
                        total_count                        
                        page_info {
                            current_page
                            page_size
                            total_pages
                        }
                        items {
                            id
                            sku
                            name
                            attribute_set_id
                            enhanced_title
                            price {
                                regularPrice {
                                    amount {
                                        value
                                        currency
                                    }
                                    adjustments {
                                        amount {
                                            value
                                            currency
                                        }
                                        code
                                        description
                                    }
                                }
                            }
                            type_id
                            created_at
                            updated_at
                            ... on PhysicalProductInterface {
                                weight
                            }
                            media_gallery_entries {
                                file
                            }
                        }
                    }
                }
            ';

            $response = $client->request('POST', '', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'query' => $query,
                    'variables' => [
                        'pageSize' => $pageSize,
                        'currentPage' => $currentPage,
                        'filter' => $filter,
                        'search' => $search
                    ]
                ]
            ]);

            $data = json_decode($response->getBody()->getContents())->data;
            return response()->json([
                'status' => 'success',
                'data' => $data->products ?? [],
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
            $storeId = $request->input('storeId') ?? '';
            $client = $this->makeHttpClient($params['store_view']);
            $response = $client->request('GET', $storeId ? 'products/' . $params['sku'].'?storeId='.$storeId : 'products/' . $params['sku']);
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
            // $deleteClient = $this->makeHttpClient('all');

            $files = $request->input('files');
            // $imageIds = $request->input('imageIds');

            // foreach ($imageIds as $id) {
            //     $deleteClient->request('DELETE', 'products/' . $params['sku'] . '/media/' . $id);
            // }

            $count = 0;
            foreach ($files as $file) {
                if ($file['action'] === 'new') {
                    $extension = explode('/', mime_content_type($file['data']['fileBase64']))[1];
                    $base64Content = explode('base64,', $file['data']['fileBase64']);
                    $payload = [];
                    if ($file['data']['position'] === 0) {
                        $payload = [
                            'entry' => [
                                'media_type' => 'image',
                                'position' => $file['data']['position'],
                                'label' => $file['name'],
                                'disabled' => false,
                                'types' => ['image', 'small_image', 'thumbnail', 'swatch_image'],
                                // 'id' => $file['data']['magento_id'],
                                'content' => [
                                    'base64_encoded_data' => $base64Content[1],
                                    'type' => 'image/' . $extension,
                                    'name' => implode(explode(' ', $file['name'])),
                                ],
                            ]
                        ];
                    } else {
                        $payload =  [
                            'entry' => [
                                'media_type' => 'image',
                                'position' => $file['data']['position'],
                                'label' => $file['name'],
                                // 'id' => $file['data']['magento_id'],
                                'content' => [
                                    'base64_encoded_data' => $base64Content[1],
                                    'type' => 'image/' . $extension,
                                    'name' => implode(explode(' ', $file['name'])),
                                ],
                            ]
                        ];
                    }
                    $client->request('POST', 'products/' . $params['sku'] . '/media', [
                        'headers' => ['Content-Type' => 'application/json'],
                        'body' => json_encode($payload),
                    ]);
                } else if ($file['action'] === 'remove') {
                    $client->request('DELETE', 'products/' . $params['sku'] . '/media/' . $file['data']['magento_id']);
                    $count--;
                } else if ($file['action'] === 'keep') {
                    if ($file['data']['position'] === 0) {
                        $payload = [
                            'entry' => [
                                'media_type' => 'image',
                                'position' => $file['data']['position'],
                                'label' => $file['name'],
                                'disabled' => false,
                                'types' => ['image', 'small_image', 'thumbnail', 'swatch_image'],
                                'id' => $file['data']['magento_id']
                            ]
                        ];
                    } else {
                        $payload =  [
                            'entry' => [
                                'media_type' => 'image',
                                'position' => $file['data']['position'],
                                'id' => $file['data']['magento_id'],
                            ]
                        ];
                    }

                    $client->request('PUT', 'products/' . $params['sku'] . '/media/' . $file['data']['magento_id'], [
                        'headers' => ['Content-Type' => 'application/json'],
                        'body' => json_encode($payload),
                    ]);
                }
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
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
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

    public function getAttributeSets(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
            $params = $request->route()->parameters();
            $response = $client->request('GET', 'products/attribute-sets/' . $params['attributeSetId'] . '/attributes');
            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_get_attributes_sets',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createAttributes(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
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
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
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
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);
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

    public function assignChildProducts(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            /***
             * sample payload
             * {
                "childSku": "MS-Champ-S"
                }
             */

            $response = $client->request('POST', 'configurable-products/' . $params['sku'] . '/child', [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => json_encode($request->all()),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_assign_configurable_products',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function setConfigurableAttribute(Request $request)
    {
        try {
            $params = $request->route()->parameters();
            $client = $this->makeHttpClient($params['store_view']);

            $options = $request->input('options');
            /***
                [
                    {
                        "option": {
                            "attribute_id": "141",
                            "label": "Size",
                            "position": 0,
                            "is_use_default": true,
                                "values": [
                                {
                                    "value_index": 9
                                }
                            ]
                        }
                    },
                    ...
                ]
             */
            foreach ($options as $option) {
                $response = $client->request('POST', 'configurable-products/' . $params['sku'] . '/options', [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode([
                        'option' => $option
                    ]),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => json_decode($response->getBody()),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'fail_assign_configurable_products',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
