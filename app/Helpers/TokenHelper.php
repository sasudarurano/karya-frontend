<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class TokenHelper
{
    /**
     * Ekstrak payload dari JWT token dan validasi signature HS256
     */
    public static function decodeToken($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            list($headerB64, $payloadB64, $signatureB64) = $parts;

            // 1. Verifikasi Header
            $headerJson = base64_decode(strtr($headerB64, '-_', '+/'));
            $header = json_decode($headerJson, true);
            if (!$header || !isset($header['alg']) || $header['alg'] !== 'HS256') {
                return null; // Tolak algoritma selain HS256 untuk mencegah signature evasion
            }

            // 2. Verifikasi Signature menggunakan JWT_SECRET
            $secret = env('JWT_SECRET');
            if (empty($secret)) {
                return null; // Pastikan JWT_SECRET terkonfigurasi di env
            }

            $signature = base64_decode(strtr($signatureB64, '-_', '+/'));
            $expectedSignature = hash_hmac('sha256', "$headerB64.$payloadB64", $secret, true);

            // Mencegah timing attack dengan hash_equals
            if (!hash_equals($signature, $expectedSignature)) {
                return null; // Tanda tangan tidak cocok
            }

            // 3. Ekstrak Payload
            $payloadJson = base64_decode(strtr($payloadB64, '-_', '+/'));
            return json_decode($payloadJson, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cek apakah token sudah expired atau tidak valid
     * Dengan buffer time untuk memudahkan refresh
     */
    public static function isTokenExpired($token, $bufferSeconds = 120)
    {
        $payload = self::decodeToken($token);

        if (!$payload || !isset($payload['exp'])) {
            return true; // Anggap token yang tidak valid/tidak bertanda tangan sah sebagai expired
        }

        $expirationTime = $payload['exp'];
        $currentTime = time();

        return ($currentTime + $bufferSeconds) > $expirationTime;
    }

    /**
     * Dapatkan waktu sisa token dalam detik
     */
    public static function getTokenTimeRemaining($token)
    {
        $payload = self::decodeToken($token);

        if (!$payload || !isset($payload['exp'])) {
            return null;
        }

        return $payload['exp'] - time();
    }

    /**
     * Dapatkan informasi token dari session
     */
    public static function getTokenInfo()
    {
        $token = Session::get('api_token');
        
        if (!$token) {
            return null;
        }

        return [
            'token' => $token,
            'payload' => self::decodeToken($token),
            'isExpired' => self::isTokenExpired($token),
            'timeRemaining' => self::getTokenTimeRemaining($token),
        ];
    }
}
