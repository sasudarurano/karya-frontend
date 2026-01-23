<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    public function showLogin() {
        // Cek jika user sudah login, langsung ke dashboard
        if (Session::has('api_token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showForgotPassword() {
        // Cek jika user sudah login, redirect ke dashboard
        if (Session::has('api_token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.forgot-password');
    }

    public function showResetPassword() {
        // Cek jika user sudah login, redirect ke dashboard
        if (Session::has('api_token')) {
            return redirect()->route('dashboard');
        }
        return view('auth.reset-password');
    }

    public function login(Request $request) {
        // Validasi input
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required'
        ]);

        // Panggil API dengan parameter email (identifier bisa berupa email atau username)
        $response = $this->api->login($request->identifier, $request->password);

        if ($response->successful()) {
            // Struktur Response dari Backend:
            // {
            //    "status": "success",
            //    "data": { 
            //       "user": {...}, 
            //       "accessToken": "...", 
            //       "refreshToken": "..." 
            //    }
            // }
            
            $responseData = $response->json();
            $data = $responseData['data'];
            
            // Simpan ke Session Laravel
            Session::put('api_token', $data['accessToken']);
            Session::put('refresh_token', $data['refreshToken']);
            Session::put('user', $data['user']);
            
            // Ekstrak expiration time dari JWT token
            $this->storeTokenExpiration($data['accessToken']);
            
            // Fetch dan simpan profile picture ke session
            $this->storeProfilePicture($data['accessToken']);
            
            // Fetch dan simpan user stats (post count, following count)
            $this->storeUserStats();

            return redirect()->route('dashboard')->with('success', 'Login berhasil!');
        }

        // Jika gagal (401/400)
        return back()
            ->withInput($request->only('identifier'))
            ->withErrors(['identifier' => $response->json()['message'] ?? 'Login gagal. Periksa kredensial Anda.']);
    }

    /**
     * Ekstrak waktu expiration dari JWT token dan simpan di session
     */
    private function storeTokenExpiration($token)
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) === 3) {
                $payload = json_decode(
                    base64_decode(strtr($parts[1], '-_', '+/')),
                    true
                );
                
                if (isset($payload['exp'])) {
                    Session::put('token_expiration', $payload['exp']);
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Fetch dan simpan profile picture ke session
     */
    private function storeProfilePicture($accessToken)
    {
        try {
            $response = $this->api->getCurrentUserProfile();
            if ($response->successful()) {
                $profileData = $response->json()['data'] ?? null;
                
                if ($profileData && isset($profileData['profile_picture']['file_url'])) {
                    $cleanPath = str_replace('\\', '/', $profileData['profile_picture']['file_url']);
                    $profilePicture = str_starts_with($cleanPath, 'http') 
                        ? $cleanPath 
                        : rtrim(env('BACKEND_API_URL'), '/') . '/' . ltrim($cleanPath, '/');
                    
                    Session::put('profile_picture', $profilePicture);
                }
            }
        } catch (\Exception $e) {
            // Silent fail - use fallback avatar
        }
    }

    /**
     * Fetch dan simpan user stats (posts count dan following count)
     */
    private function storeUserStats()
    {
        try {
            $token = session('api_token');
            
            // Fetch my posts to count published posts
            $postsResponse = $this->api->getMyPosts($token);
            $publishedPostsCount = 0;
            
            if ($postsResponse->successful()) {
                $posts = $postsResponse->json()['data'] ?? [];
                // Count only published posts
                $publishedPostsCount = collect($posts)->filter(fn($post) => $post['is_published'] === true)->count();
            }
            
            // Fetch following list using current user ID
            $user = session('user');
            $followingCount = 0;
            
            if ($user && isset($user['id'])) {
                $followingResponse = $this->api->getFollowing($user['id']);
                
                if ($followingResponse->successful()) {
                    $followingUsers = $followingResponse->json()['data'] ?? [];
                    $followingCount = count($followingUsers);
                }
            }
            
            Session::put('userStats', [
                'postsCount' => $publishedPostsCount,
                'followingCount' => $followingCount
            ]);
        } catch (\Exception $e) {
            // Silent fail - use defaults
            Session::put('userStats', [
                'postsCount' => 0,
                'followingCount' => 0
            ]);
        }
    }

    public function logout() {
        Session::flush(); // Hapus semua session
        return redirect()->route('login')->with('success', 'Berhasil logout.');
    }
}