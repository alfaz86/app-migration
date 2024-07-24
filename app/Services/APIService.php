<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class APIService
{
    public function sendAPI($migration, $url): Response
    {
        $httpMethod = $migration['http_method'];
        $authType = $migration['auth_type'];
        if (is_string($migration['authData'])) {
            $authData = $migration['auth_data'];
        } else {
            $authData = json_decode($migration['auth_data'], true);
        }

        $client = Http::withOptions(['verify' => false]);

        switch ($authType) {
            case 'basic':
                $client = $client->withBasicAuth($authData['username'], $authData['password']);
                break;
            case 'bearer':
                $client = $client->withToken($authData['token']);
                break;
            case 'apikey':
                $client = $client->withHeaders([$authData['key'] => $authData['value']]);
                break;
            case 'oauth2':
                // Handle OAuth 2.0 specific logic here
                break;
            case 'none':
            default:
                // No additional authentication
                break;
        }

        $response = $client->send($httpMethod, $url);

        return $response;
    }
}