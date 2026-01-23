<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class FollowController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Follow a user
     */
    public function follow($userId)
    {
        try {
            $response = $this->api->followUser($userId);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Berhasil mengikuti user!',
                    'data' => $data['data'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal mengikuti user.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Follow error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengikuti user.'
            ], 500);
        }
    }

    /**
     * Unfollow a user
     */
    public function unfollow($userId)
    {
        try {
            $response = $this->api->unfollowUser($userId);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Berhasil berhenti mengikuti user!',
                    'data' => $data['data'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal berhenti mengikuti user.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Unfollow error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat berhenti mengikuti user.'
            ], 500);
        }
    }

    /**
     * Get list of followers for a user
     */
    public function followers($userId)
    {
        try {
            $response = $this->api->getFollowers($userId);

            if ($response->successful()) {
                $followers = $response->json()['data'] ?? [];
                return view('follow.followers', compact('followers', 'userId'));
            }

            return back()->with('error', 'Gagal mengambil data followers.');
        } catch (\Exception $e) {
            Log::error("Get followers error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengambil data followers.');
        }
    }

    /**
     * Get list of users being followed
     */
    public function following($userId)
    {
        try {
            $response = $this->api->getFollowing($userId);

            if ($response->successful()) {
                $following = $response->json()['data'] ?? [];
                return view('follow.following', compact('following', 'userId'));
            }

            return back()->with('error', 'Gagal mengambil data following.');
        } catch (\Exception $e) {
            Log::error("Get following error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengambil data following.');
        }
    }
}
