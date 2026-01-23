<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ProfileController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Display user profile
     */
    public function show($userId)
    {
        try {
            $currentUser = Session::get('user');
            // AMBIL TOKEN: Penting untuk sinkronisasi status Follow & Like
            $token = Session::get('api_token');

            Log::info("Profile show attempt", ['userId' => $userId, 'currentUserId' => $currentUser['id'] ?? null]);
            $isOwnProfile = $currentUser && $currentUser['id'] == $userId;
            
            // 1. Get User Profile
            // Kirim token (param ke-2) agar backend bisa cek status "is_followed"
            $profileResponse = $this->api->getUserProfile($userId, $token);
            
            Log::info("Profile API response", ['status' => $profileResponse->status(), 'successful' => $profileResponse->successful()]);
            
            if ($profileResponse->successful()) {
                $responseData = $profileResponse->json();
                $profileData = $responseData['data'] ?? $responseData ?? null;
                
                // Merge profile data with user data if available
                if ($profileData && isset($profileData['user'])) {
                    $profile = array_merge($profileData['user'], $profileData);
                    $profile['user_data'] = $profileData['user'];
                } else {
                    $profile = $profileData;
                }
                
                // If own profile and data is incomplete, merge with session
                if ($isOwnProfile && $currentUser) {
                    $profile = array_merge($currentUser, $profile ?? []);
                }
            } else {
                // Fallback: Extract profile from user's posts
                Log::info("Fallback: Getting profile from posts");
                $postsResponse = $this->api->getPosts(['user_id' => $userId, 'limit' => 1], $token);
                
                if ($postsResponse->successful() && !empty($postsResponse->json()['data'] ?? [])) {
                    $firstPost = $postsResponse->json()['data'][0];
                    $profile = $firstPost['author'] ?? null;
                    
                    // Try to get profile picture
                    try {
                        $userProfileResponse = $this->api->getUserById($userId);
                        if ($userProfileResponse->successful()) {
                            $userData = $userProfileResponse->json()['data'] ?? null;
                            if ($userData && isset($userData['profile_picture'])) {
                                $profile['profile_picture'] = $userData['profile_picture'];
                            } elseif ($userData && isset($userData['pp_id'])) {
                                $profile['pp_id'] = $userData['pp_id'];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Could not fetch profile picture for user $userId");
                    }
                    
                    if ($isOwnProfile && $currentUser) {
                        $profile = array_merge($currentUser, $profile ?? []);
                        if (Session::has('profile_picture')) {
                            $profile['profile_picture'] = Session::get('profile_picture');
                        }
                    }
                } else {
                    return redirect()->route('home')->with('error', 'Profil pengguna tidak ditemukan.');
                }
            }
            
            // Validate profile data
            if (!$profile || !isset($profile['id'])) {
                Log::error("Profile data invalid for userId: $userId");
                return redirect()->route('home')->with('error', 'Profil tidak ditemukan atau format data tidak valid.');
            }

            // Fetch profile picture publicly (no auth required)
            try {
                $ppResponse = $this->api->getProfilePicture($userId);
                if ($ppResponse->successful()) {
                    $ppData = $ppResponse->json()['data'] ?? null;
                    if ($ppData && isset($ppData['file_url'])) {
                        $profile['profile_picture'] = ['file_url' => $ppData['file_url']];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Could not fetch profile picture for user $userId: " . $e->getMessage());
            }

            // 2. Get User's Posts
            // Kirim token agar status "isLiked" pada post list sinkron
            if ($isOwnProfile) {
                $postsResponse = $this->api->getMyPosts($token);
            } else {
                $postsResponse = $this->api->getPosts(['user_id' => $userId, 'limit' => 100], $token);
            }
            
            $posts = [];
            if ($postsResponse->successful()) {
                $responseBody = $postsResponse->json();
                if (isset($responseBody['data'])) {
                    $posts = is_array($responseBody['data']) ? $responseBody['data'] : [];
                } elseif (is_array($responseBody)) {
                    $posts = $responseBody;
                }
            }
            
            // Filter only published posts
            $posts = array_filter($posts, fn($post) => ($post['is_published'] ?? false) === true || ($post['is_published'] ?? false) === 1);
            $posts = array_values($posts); // Re-index array
            
            // 3. Get Stats
            $followersResponse = $this->api->getFollowers($userId);
            $followingResponse = $this->api->getFollowing($userId);
            
            $followersCount = $followersResponse->successful() ? count($followersResponse->json()['data'] ?? []) : 0;
            $followingCount = $followingResponse->successful() ? count($followingResponse->json()['data'] ?? []) : 0;

            return view('profile.show', compact('profile', 'posts', 'followersCount', 'followingCount', 'isOwnProfile', 'userId'));
        } catch (\Exception $e) {
            Log::error("Profile show error: " . $e->getMessage());
            return redirect()->route('home')->with('error', 'Terjadi kesalahan saat mengambil profil.');
        }
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Session::get('user');
        $token = Session::get('api_token');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        try {
            // Gunakan method spesifik getMe/getCurrentUserProfile dengan token
            $response = $this->api->getCurrentUserProfile($token);
            
            if ($response->successful()) {
                $responseData = $response->json();
                $profileData = $responseData['data'] ?? $responseData ?? null;
                
                $profile = array_merge($user, $profileData ?? []);
                
                if (isset($profileData['user'])) {
                    $profile = array_merge($profile, $profileData['user']);
                }
                
                $prodiResponse = $this->api->getAllProgramStudi();
                $programStudiList = $prodiResponse->successful() ? ($prodiResponse->json()['data'] ?? []) : [];
                
                return view('profile.edit', compact('profile', 'programStudiList'));
            }

            // Fallback
            $profile = $user;
            $prodiResponse = $this->api->getAllProgramStudi();
            $programStudiList = $prodiResponse->successful() ? ($prodiResponse->json()['data'] ?? []) : [];
            return view('profile.edit', compact('profile', 'programStudiList'));
            
        } catch (\Exception $e) {
            Log::error("Profile edit error: " . $e->getMessage());
            $profile = $user;
            $prodiResponse = $this->api->getAllProgramStudi();
            $programStudiList = $prodiResponse->successful() ? ($prodiResponse->json()['data'] ?? []) : [];
            return view('profile.edit', compact('profile', 'programStudiList'));
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = Session::get('user');
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $request->validate([
            'bio' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'program_studi_id' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048' // 2MB max
        ]);

        try {
            // Map frontend field names to backend field names
            $data = [
                'bio' => $request->input('bio'),
                'phone_number' => $request->input('phone'),
            ];
            
            $prodiId = $request->input('program_studi_id');
            if (!empty($prodiId)) {
                $data['program_studi_id'] = $prodiId;
            }
            
            // Token biasanya dihandle otomatis oleh service jika disimpan di session,
            // tapi pastikan Service KaryaApi mengambilnya.
            
            if ($request->hasFile('profile_picture')) {
                $file = $request->file('profile_picture');
                $response = $this->api->updateProfileWithFile($data, $file);
            } else {
                $response = $this->api->updateProfile($data);
            }

            if ($response->successful()) {
                // Update session with new data
                $updatedProfile = $response->json()['data'] ?? null;
                if ($updatedProfile) {
                    Session::put('user', array_merge($user, $updatedProfile));
                }
                
                return redirect()->route('profile.show', $user['id'])
                    ->with('success', 'Profil berhasil diperbarui!');
            }

            return back()->with('error', $response->json()['message'] ?? 'Gagal memperbarui profil.');
        } catch (\Exception $e) {
            Log::error("Profile update error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memperbarui profil.');
        }
    }

    /**
     * Show bookmarked posts (liked posts)
     */
    public function bookmarks()
    {
        try {
            $currentUser = Session::get('user');
            // AMBIL TOKEN
            $token = Session::get('api_token');
            
            if (!$currentUser) {
                return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
            }

            // Fetch liked posts from backend dengan TOKEN
            // Endpoint Backend: GET /votes/user/liked
            $response = $this->api->getLikedPosts($token);
            
            Log::info("Bookmarks API response", [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            $bookmarks = [];
            if ($response->successful()) {
                $responseBody = $response->json();
                $bookmarks = isset($responseBody['data']) ? $responseBody['data'] : [];
            }

            return view('profile.bookmarks', compact('bookmarks'));
        } catch (\Exception $e) {
            Log::error("Bookmarks error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }
}