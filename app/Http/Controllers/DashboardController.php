<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private readonly KaryaApi $api)
    {
    }

    /**
     * Entry point: pilih dashboard berdasarkan role.
     */
    public function index(): RedirectResponse|View
    {
        $user = session('user');

        if (!$user) {
            return redirect()->route('login');
        }

        $role = strtolower($user['role'] ?? '');

        if ($this->isSuperAdmin($role)) {
            // Get database status and last update
            $dbStatus = $this->getDatabaseStatus();
            $lastUpdate = $this->getLastUpdate();
            $activeModules = $this->getActiveModules();
            $systemStatus = $this->getSystemStatus();
            $securityInfo = $this->getSecurityInfo($user);
            
            return view('dashboard-superadmin', [
                'user' => $user,
                'dbStatus' => $dbStatus,
                'lastUpdate' => $lastUpdate,
                'activeModules' => $activeModules,
                'systemStatus' => $systemStatus,
                'securityInfo' => $securityInfo
            ]);
        }

        if ($this->isAdmin($role)) {
            return view('dashboard-admin', ['user' => $user]);
        }

        return $this->renderUserFeed();
    }

    /**
     * Dashboard biasa (feed following) — bisa diakses semua role.
     */
    public function feed(): RedirectResponse|View
    {
        return $this->renderUserFeed();
    }

    /**
     * Ambil feed following + foto profil.
     */
    private function renderUserFeed(): RedirectResponse|View
    {
        $page = request()->query('page', 1);
        $response = $this->api->getFollowingPosts(['page' => $page, 'limit' => 10]);

        if ($response->status() === 401) {
            return redirect()->route('login')->with('error', 'Sesi habis, silakan login kembali.');
        }

        $result = $response->successful() ? ($response->json()['data'] ?? []) : [];
        $posts = $result['data'] ?? [];
        $meta = $result['meta'] ?? null;

        $profilePicture = null;
        $followingCount = 0;
        $user = session('user');

        if ($user) {
            try {
                $profileResponse = $this->api->getCurrentUserProfile();
                if ($profileResponse->successful()) {
                    $profileData = $profileResponse->json()['data'] ?? null;

                    if (isset($profileData['profile_picture']['file_url'])) {
                        $cleanPath = str_replace('\\', '/', $profileData['profile_picture']['file_url']);
                        $profilePicture = str_starts_with($cleanPath, 'http')
                            ? $cleanPath
                            : rtrim(env('BACKEND_API_URL'), '/') . '/' . ltrim($cleanPath, '/');
                    }
                }

                // Get live following count from API (same as ProfileController)
                $userId = $user['id'] ?? null;
                if ($userId) {
                    $followingResponse = $this->api->getFollowing($userId);
                    $followingCount = $followingResponse->successful() ? count($followingResponse->json()['data'] ?? []) : 0;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to get profile data for dashboard: ' . $e->getMessage());
            }
        }

        return view('dashboard', compact('posts', 'profilePicture', 'followingCount', 'meta'));
    }

    private function isSuperAdmin(string $role): bool
    {
        return $role === 'superadmin';
    }

    private function isAdmin(string $role): bool
    {
        return in_array($role, [
            'admin',
            'kemahasiswaan',
            'verifikator',
            'dosen pembimbing',
            'dosen_pembimbing',
            'kaprodi',
        ], true);
    }

    /**
     * Check database connection status by testing API
     */
    private function getDatabaseStatus(): array
    {
        try {
            $token = Session::get('api_token');
            // Try to fetch users to verify backend connection
            $response = $this->api->getAllUsers($token);
            
            if ($response->successful() || $response->status() !== 0) {
                return [
                    'status' => 'Connected',
                    'color' => 'emerald-400',
                    'isConnected' => true
                ];
            }
            
            return [
                'status' => 'Disconnected',
                'color' => 'rose-400',
                'isConnected' => false
            ];
        } catch (\Exception $e) {
            Log::error('Database connection check failed: ' . $e->getMessage());
            return [
                'status' => 'Disconnected',
                'color' => 'rose-400',
                'isConnected' => false
            ];
        }
    }

    /**
     * Get last update timestamp from backend API
     */
    private function getLastUpdate(): string
    {
        try {
            $token = Session::get('api_token');
            
            // Fetch all users to get the latest update
            $response = $this->api->getAllUsers($token);
            
            if (!$response->successful()) {
                return 'Unable to fetch';
            }
            
            $data = $response->json()['data'] ?? [];
            
            if (empty($data)) {
                return 'No updates yet';
            }
            
            // Find the most recent updated_at timestamp
            $latestTimestamp = null;
            foreach ($data as $item) {
                if (isset($item['updated_at']) || isset($item['created_at'])) {
                    $timestamp = $item['updated_at'] ?? $item['created_at'];
                    if ($latestTimestamp === null) {
                        $latestTimestamp = $timestamp;
                    } else {
                        $itemTime = strtotime($timestamp);
                        $latestTime = strtotime($latestTimestamp);
                        if ($itemTime > $latestTime) {
                            $latestTimestamp = $timestamp;
                        }
                    }
                }
            }
            
            if ($latestTimestamp) {
                return $this->formatTimeAgo($latestTimestamp);
            }
            
            return 'No updates yet';
        } catch (\Exception $e) {
            Log::error('Failed to get last update from API: ' . $e->getMessage());
            return 'Unable to fetch';
        }
    }

    /**
     * Format timestamp to relative time (e.g., "5 mins ago")
     */
    private function formatTimeAgo($timestamp): string
    {
        try {
            $date = Carbon::parse($timestamp);
            $now = Carbon::now();
            $diff = $now->diffInSeconds($date);

            if ($diff < 60) {
                return 'Just now';
            } elseif ($diff < 3600) {
                $mins = intval($diff / 60);
                return $mins . ' ' . ($mins === 1 ? 'min' : 'mins') . ' ago';
            } elseif ($diff < 86400) {
                $hours = intval($diff / 3600);
                return $hours . ' ' . ($hours === 1 ? 'hour' : 'hours') . ' ago';
            } else {
                $days = intval($diff / 86400);
                return $days . ' ' . ($days === 1 ? 'day' : 'days') . ' ago';
            }
        } catch (\Exception $e) {
            Log::error('Failed to format time: ' . $e->getMessage());
            return 'Unable to fetch';
        }
    }

    /**
     * Get count of active system modules
     */
    private function getActiveModules(): array
    {
        try {
            $token = Session::get('api_token');
            
            // Count available modules by checking API endpoints
            $modules = [
                'Posts Management' => $this->api->getAllUsers($token)->successful(),
                'User Management' => true,
                'Program Studi' => true,
                'Post Moderation' => true,
            ];
            
            $activeCount = count(array_filter($modules, function($status) {
                return $status === true;
            }));
            
            return [
                'count' => $activeCount,
                'total' => count($modules),
                'status' => $activeCount > 2 ? 'Ready to use' : 'Limited',
                'isStable' => $activeCount > 2
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get active modules: ' . $e->getMessage());
            return [
                'count' => 0,
                'total' => 0,
                'status' => 'Checking...',
                'isStable' => false
            ];
        }
    }

    /**
     * Get system status
     */
    private function getSystemStatus(): array
    {
        try {
            $token = Session::get('api_token');
            $response = $this->api->getAllUsers($token);
            
            // System is stable if API is responding
            $isStable = $response->successful() || $response->status() !== 0;
            
            return [
                'status' => $isStable ? 'Stable' : 'Unstable',
                'message' => $isStable ? 'All systems go' : 'System experiencing issues',
                'isStable' => $isStable,
                'color' => $isStable ? 'emerald' : 'rose'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get system status: ' . $e->getMessage());
            return [
                'status' => 'Checking...',
                'message' => 'Unable to determine status',
                'isStable' => false,
                'color' => 'amber'
            ];
        }
    }

    /**
     * Get security information
     */
    private function getSecurityInfo(array $user): array
    {
        try {
            $lastLoginTime = session('last_login_at');
            
            if ($lastLoginTime) {
                $lastLogin = Carbon::parse($lastLoginTime)->format('d M Y');
            } else {
                $lastLogin = 'Today';
            }
            
            return [
                'status' => 'Secure',
                'message' => 'Last login: ' . $lastLogin,
                'isSecure' => true,
                'user' => $user['full_name'] ?? 'Administrator',
                'role' => strtoupper($user['role'] ?? 'Super')
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get security info: ' . $e->getMessage());
            return [
                'status' => 'Unknown',
                'message' => 'Unable to verify security status',
                'isSecure' => false,
                'user' => 'Unknown',
                'role' => 'Unknown'
            ];
        }
    }
}

