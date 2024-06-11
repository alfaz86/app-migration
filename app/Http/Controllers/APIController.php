<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class APIController extends Controller
{
    public function checkingAPI($api_url)
    {
        // checking api url and return response if available or not
        $response = Http::get($api_url);
        if ($response->status() == 200) {
            return response()->json([
                'status' => 'success',
                'message' => 'API is available'
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'API is not available'
            ]);
        }
    }
}
