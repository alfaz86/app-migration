<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class APIController extends Controller
{
    public function checkingAPI(Request $request)
    {
        $api_url = $request->input('url');
        $http_method = $request->input('http_method');
        $auth_type = $request->input('auth_type');
        $auth_data = $request->input('auth_data', []);

        $client = Http::withOptions(['verify' => false]);

        switch ($auth_type) {
            case 'basic':
                $client = $client->withBasicAuth($auth_data['username'], $auth_data['password']);
                break;
            case 'bearer':
                $client = $client->withToken($auth_data['token']);
                break;
            case 'apikey':
                // Assuming the API key should be added as a header
                $client = $client->withHeaders([$auth_data['key'] => $auth_data['value']]);
                break;
            case 'oauth2':
                // Handle OAuth 2.0 specific logic
                break;
            case 'none':
            default:
                // No additional authentication
                break;
        }

        try {
            $response = $client->send($http_method, $api_url);
            if ($response->successful()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'API is available',
                    'data' => $response->json()
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'API is not available',
                    'data' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Error occurred: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function collectionKeyOfObjectData(Request $request)
    {
        try {
            $keys = $this->getAllKeys($request->object_data);
            return response()->json([
                'status' => 'success',
                'message' => 'Keys of object data',
                'data' => $keys
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to get keys of object data',
                'data' => []
            ]);
        }
    }

    private function getAllKeys($data, $prefix = '') {
        $keys = [];
        foreach ($data as $key => $value) {
            $newKey = $prefix . ($prefix ? '.' : '') . $key;
            $keys[] = $newKey;
            if (is_array($value) || is_object($value)) {
                $keys = array_merge($keys, self::getAllKeys((array)$value, $newKey));
            }
        }
        return $keys;
    }
}
