<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Elasticsearch\ClientBuilder;
use GuzzleHttp\Client;
use Exception;

class ReportController extends Controller
{
    private $elasticsearch;

    public function __construct()
    {
        try {
            $hosts = [
                [
                    'host' => env('ELASTIC_VEHICLE_HOST'),
                    'port' => '9243',
                    'scheme' => 'https',
                    'user' => env('ELASTIC_VEHICLE_USER'),
                    'pass' => env('ELASTIC_VEHICLE_PASS'),
                ]
            ];

            $this->elasticsearch = ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_connect_es',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getReportData(Request $request)
    {
        try {
            $request->validate([
                'payload' => 'nullable|string'
            ]);

            $params = $request->all();
            $body = $params['payload'];

            $url = "https://" . env('ELASTIC_VEHICLE_HOST') . ":9243/reglookup/_search";
            $secret = env('ELASTIC_VEHICLE_USER') . ':' . env('ELASTIC_VEHICLE_PASS');
            $token = base64_encode($secret);

            $client = new Client();
            $response = $client->request(
                'POST',
                $url,
                [
                    'headers' =>
                    [
                        'Accept' => 'application/json',
                        'Accept-Language' => 'en_US',
                        'Content-Type' =>  'application/json',
                        'Authorization' => "Basic {$token}"
                    ],
                    'body' => $body
                ]
            );
            $data = json_decode($response->getBody(), true);

            $result = $data['aggregations']['groupByMake']['buckets'];

            return response()->json([
                'status' => 'success',
                'data' => $result
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'could_not_get_data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
