<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Periksa header 'Accept' untuk menentukan tipe API
        $acceptHeader = $request->header('Accept');

        // Tentukan tipe API berdasarkan header 'Accept'
        if ($acceptHeader === 'application/json') {
            $apiType = 'json';
        } elseif ($acceptHeader === 'application/xml') {
            $apiType = 'xml';
        } else {
            // Tipe API tidak dikenali atau tidak didukung
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported API type'
            ], 406); // 406 Not Acceptable
        }

        // Tambahkan tipe API ke atribut request agar bisa diakses di controller
        $request->attributes->set('api_type', $apiType);

        return $next($request);
    }
}
