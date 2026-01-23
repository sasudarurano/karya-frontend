<?php

namespace App\Http\Middleware;

use App\Helpers\TokenHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya jalankan jika user sudah login (ada access token)
        if (!Session::has('api_token')) {
            return $next($request);
        }

        $accessToken = Session::get('api_token');
        $refreshToken = Session::get('refresh_token');

        // Cek apakah access token sudah expired (dengan 2 menit buffer)
        if (TokenHelper::isTokenExpired($accessToken, 120)) {
            // Coba refresh token
            $api = app(\App\Services\KaryaApi::class);
            $refreshSuccess = $api->refreshAccessToken($refreshToken);

            if (!$refreshSuccess) {
                // Jika refresh gagal, redirect ke login
                return redirect()->route('login')->with('error', 'Sesi Anda telah expired. Silakan login kembali.');
            }
        }

        return $next($request);
    }
}

