<?php

namespace App\Helpers;

/**
 * Helper functions for profile picture handling
 */
class ProfileHelper
{
    /**
     * Get public profile picture URL
     * Converts relative paths to full URLs
     * 
     * @param string|array|null $profilePicture
     * @return string|null
     */
    public static function getProfilePictureUrl($profilePicture)
    {
        if (!$profilePicture) {
            return null;
        }

        // If it's an array with file_url key
        if (is_array($profilePicture) && isset($profilePicture['file_url'])) {
            $path = $profilePicture['file_url'];
        } elseif (is_string($profilePicture)) {
            $path = $profilePicture;
        } else {
            return null;
        }

        if (!$path) {
            return null;
        }

        // Clean the path
        $cleanPath = str_replace('\\', '/', $path);

        // If it's already a full URL, return as is
        if (str_starts_with($cleanPath, 'http')) {
            return $cleanPath;
        }

        // Convert to full URL using backend base URL
        $baseUrl = rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/');
        $fileBaseUrl = str_replace('/api', '', $baseUrl);

        return $fileBaseUrl . '/' . ltrim($cleanPath, '/');
    }

    /**
     * Get initials from name for avatar fallback
     * 
     * @param string|null $name
     * @return string
     */
    public static function getNameInitials($name = null)
    {
        if (!$name) {
            return 'U';
        }

        $parts = explode(' ', trim($name));
        $initials = '';

        foreach ($parts as $part) {
            if (!empty($part)) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }

        return $initials ?: 'U';
    }
}
