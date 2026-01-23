<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Session;

class TokenHelper
{
    /**
     * Ekstrak payload dari JWT token tanpa verifikasi signature
     */
    public static function decodeToken($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            return json_decode(
                base64_decode(strtr($parts[1], '-_', '+/')),
                true
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cek apakah token sudah expired
     * Dengan buffer time untuk memudahkan refresh
     */
    public static function isTokenExpired($token, $bufferSeconds = 120)
    {
        $payload = self::decodeToken($token);

        if (!$payload || !isset($payload['exp'])) {
            return false;
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
