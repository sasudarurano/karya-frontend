<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class KaryaApi
{
    protected $baseUrl;

    public function __construct()
    {
        // Pastikan di .env file Laravel sudah ada:
        // BACKEND_API_URL=http://localhost:3000/api
        $this->baseUrl = env('BACKEND_API_URL');
    }

    // --- HELPERS ---

    /**
     * Helper untuk request dengan Token JWT (Butuh Login)
     * Menggunakan session 'api_token' agar konsisten dengan proses login.
     */
    private function getClient()
    {
        $token = Session::get('api_token');
        return Http::withToken($token)->acceptJson();
    }

    /**
     * Helper untuk request dengan Token Optional
     * Jika token provided, gunakan itu. Jika tidak, gunakan public client.
     */
    private function getClientWithToken($token = null)
    {
        if ($token) {
            return Http::withToken($token)->acceptJson();
        }
        
        $sessionToken = Session::get('api_token');
        if ($sessionToken) {
            return Http::withToken($sessionToken)->acceptJson();
        }
        
        return Http::acceptJson();
    }

    /**
     * Helper untuk request Public (Tanpa Login)
     */
    private function getPublicClient()
    {
        return Http::acceptJson();
    }

    // --- AUTHENTICATION ---

    public function login($identifier, $password)
    {
        return $this->getPublicClient()->post("{$this->baseUrl}/auth/login", [
            'identifier' => $identifier,
            'password' => $password
        ]   );
    }

    public function register($data)
    {
        return $this->getPublicClient()->post("{$this->baseUrl}/auth/register", $data);
    }

    /**
     * Refresh Access Token menggunakan Refresh Token
     * Dipanggil otomatis saat access token akan atau sudah expired
     */
    public function refreshAccessToken($refreshToken = null)
    {
        $refreshToken = $refreshToken ?? Session::get('refresh_token');
        
        if (!$refreshToken) {
            return null;
        }

        $response = $this->getPublicClient()->post("{$this->baseUrl}/auth/refresh", [
            'refreshToken' => $refreshToken
        ]);

        if ($response->successful()) {
            $data = $response->json()['data'];
            
            // Update tokens di session
            Session::put('api_token', $data['accessToken']);
            if (isset($data['refreshToken'])) {
                Session::put('refresh_token', $data['refreshToken']);
            }
            
            return true;
        }

        // Jika refresh gagal (biasanya karena refresh token juga expired)
        // Maka user harus login ulang
        Session::flush();
        return false;
    }

    /**
     * Mengambil daftar postingan publik (Pencarian / Filter)
     * Secara otomatis hanya mengambil is_published: true di sisi backend.
     * @param array $filters - Filter parameters (user_id, limit, etc)
     * @param string|null $token - Optional JWT token untuk sync like status
     */
    public function getPosts($filters = [], $token = null)
    {
        $filters = array_filter($filters, fn($value) => $value !== null && $value !== '');
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/posts", $filters);
    }

    /**
     * Mengambil karya milik user yang sedang login (Hanya visible untuk user sendiri)
     * Endpoint: GET /api/posts/my
     * Menampilkan semua post user terlepas dari status publikasi (untuk mahasiswa)
     */
    public function getMyPosts($token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/posts/my");
    }

    /**
     * Mengambil feed khusus user yang di-follow (Dashboard)
     */
    public function getFollowingPosts($params = [])
    {
        return $this->getClient()->get("{$this->baseUrl}/posts/following", $params);
    }

    /**
     * Mengambil daftar karya yang di-like oleh user yang sedang login
     */
    public function getLikedPosts($token = null)
    {
        $headers = [];
        
        // Jika token dikirim, gunakan; jika tidak, gunakan dari session
        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }
        
        return Http::withHeaders($headers)
                   ->get("{$this->baseUrl}/votes/user/liked");
    }

    /**
     * Mengambil Karya Terbaru (Home Page) dengan pagination
     */
    public function getNewestPosts($limit = 6, $page = 1)
    {
        return $this->getPublicClient()->get("{$this->baseUrl}/posts/newest", [
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * Mengambil Karya Populer (Home Page)
     */
    public function getPopularPosts($limit = 6, $page = 1)
    {
        return $this->getPublicClient()->get("{$this->baseUrl}/posts/popular", [
            'limit' => $limit,
            'page' => $page
        ]);
    }

    /**
     * Mengambil detail satu postingan berdasarkan ID
     * @param string $id - Post ID
     * @param string|null $token - Optional JWT token untuk mendapatkan like/follow status
     *                             Jika tidak diberikan, akan gunakan token dari session
     */
    public function getPostById($id, $token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/posts/{$id}");
    }

    /**
     * Mengubah status publikasi (Hanya Admin)
     */
    public function togglePublish($postId)
    {
        return $this->getClient()->patch("{$this->baseUrl}/posts/{$postId}/toggle-publish");
    }

    /**
     * Upload Karya Baru
     * Mendukung Multipart Form Data untuk file upload
     */
    public function createPost($data, $files = [], $token = null)
    {
        $jwtToken = $token ?? Session::get('api_token');

        // Konversi boolean ke string "true"/"false" untuk multipart form-data
        // Backend Node.js akan parse string ini menjadi boolean
        if (isset($data['is_grouped'])) {
            $boolValue = filter_var($data['is_grouped'], FILTER_VALIDATE_BOOLEAN);
            $data['is_grouped'] = $boolValue ? 'true' : 'false';
        }
        if (isset($data['is_published'])) {
            $boolValue = filter_var($data['is_published'], FILTER_VALIDATE_BOOLEAN);
            $data['is_published'] = $boolValue ? 'true' : 'false';
        }

        // Build multipart attachments array
        $attachments = [];

        // Add all regular data fields as multipart parts
        foreach ($data as $key => $value) {
            // Backend currently stores a single supervisor_id. Sending lecturer_ids
            // with external lecturer identifiers makes the Node DTO reject uploads.
            if ($key === 'lecturer_ids') {
                continue;
            }

            if (is_array($value)) {
                // Array fields (e.g. contributor_ids) -> send as multiple parts with same name
                foreach ($value as $item) {
                    if ($item !== null && $item !== '') {
                        $attachments[] = [
                            'name'     => $key,
                            'contents' => (string) $item,
                        ];
                    }
                }
            } elseif ($value !== null) {
                $attachments[] = [
                    'name'     => $key,
                    'contents' => (string) $value,
                ];
            }
        }

        // Add file attachments
        foreach ($files as $file) {
            $attachments[] = [
                'name'     => 'attachments',
                'contents' => file_get_contents($file->getRealPath()),
                'filename' => $file->getClientOriginalName(),
                'headers'  => ['Content-Type' => $file->getMimeType()],
            ];
        }

        return Http::withToken($jwtToken)
            ->asMultipart()
            ->post("{$this->baseUrl}/posts", $attachments);
    }

    /**
     * Mengambil SEMUA postingan untuk Admin Moderation
     * Termasuk yang belum dipublikasikan
     * 
     * Endpoint: GET /api/posts/admin/all
     * Authentication: Required (Admin/Superadmin/Verifikator)
     */
    public function getAllPosts($params = [])
    {
        $token = Session::get('api_token');
        
        if (!$token) {
            Log::error('getAllPosts: Token tidak ditemukan di session');
            throw new \Exception('Token tidak ditemukan. Silakan login ulang.');
        }
        
        Log::info('getAllPosts: Sending request', [
            'endpoint' => "{$this->baseUrl}/posts/admin/all",
            'has_token' => !empty($token),
            'params' => $params
        ]);
        
        return $this->getClient()->get("{$this->baseUrl}/posts/admin/all", $params);
    }

    // --- USERS ---

    public function getAllUsers($params = [], $token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/v1/users", $params);
    }

    public function searchUsers($params = [])
    {
        return $this->getPublicClient()->get("{$this->baseUrl}/v1/users/search", $params);
    }

    /**
     * Get users list for dropdown (accessible by all authenticated users)
     */
    public function getUsersList($token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/v1/users/list");
    }

    /**
     * Get all Program Studi (public endpoint)
     */
    public function getProgramStudis()
    {
        return $this->getClient()->get("{$this->baseUrl}/program-studi");
    }

    public function getUserById($userId, $token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/v1/users/{$userId}");
    }

    /**
     * Verify/Validate user account (for mahasiswa validation)
     */
    public function verifyUser($userId, $token = null)
    {
        return $this->getClientWithToken($token)->post("{$this->baseUrl}/v1/users/{$userId}/verify");
    }

    public function deactivateUser($userId, $token = null)
    {
        return $this->getClientWithToken($token)->patch("{$this->baseUrl}/v1/users/{$userId}/deactivate");
    }

    // --- COMMENTS ---

    /**
     * Mengambil komentar untuk satu postingan
     * @param string $postId - Post ID
     * @param string|null $token - Optional JWT token untuk validasi kepemilikan (edit/delete button)
     *                             Jika tidak diberikan, akan gunakan token dari session
     */
    public function getComments($postId, $token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/posts/{$postId}/comments");
    }

    public function createComment($postId, $content, $parentCommentId = null)
    {
        $data = [
            'content' => $content
        ];
        
        if ($parentCommentId) {
            $data['parent_comment_id'] = $parentCommentId;
        }
        
        return $this->getClient()->post("{$this->baseUrl}/posts/{$postId}/comments", $data);
    }

    public function updateComment($postId, $commentId, $content)
    {
        return $this->getClient()->put("{$this->baseUrl}/posts/{$postId}/comments/{$commentId}", [
            'content' => $content
        ]);
    }

    public function deleteComment($postId, $commentId)
    {
        return $this->getClient()->delete("{$this->baseUrl}/posts/{$postId}/comments/{$commentId}");
    }

    // --- VOTES (LIKE/DISLIKE) ---

    public function votePost($postId, $voteType = true)
    {
        return $this->getClient()->post("{$this->baseUrl}/votes", [
            'post_id' => $postId,
            'vote_type' => $voteType // true = like, false = dislike
        ]);
    }

    public function unvotePost($postId)
    {
        return $this->getClient()->delete("{$this->baseUrl}/votes/{$postId}");
    }

    /**
     * Toggle vote (PATCH method) - Sesuai dokumentasi backend
     * Backend akan automatically handle: cast, remove, atau update vote
     */
    public function toggleVote($postId, $voteType = true)
    {
        return $this->getClient()->patch("{$this->baseUrl}/posts/{$postId}/vote", [
            'vote_type' => $voteType
        ]);
    }

    // --- FOLLOW SYSTEM ---

    public function followUser($userId)
    {
        return $this->getClient()->post("{$this->baseUrl}/users/{$userId}/follow");
    }

    public function unfollowUser($userId)
    {
        return $this->getClient()->delete("{$this->baseUrl}/users/{$userId}/follow");
    }

    public function getFollowers($userId)
    {
        // Backend exposes: GET /api/users/:id/followers
        return $this->getPublicClient()->get("{$this->baseUrl}/users/{$userId}/followers");
    }

    public function getFollowing($userId)
    {
        // Backend exposes: GET /api/users/:id/following
        return $this->getPublicClient()->get("{$this->baseUrl}/users/{$userId}/following");
    }

    // --- PROFILE ---

    public function getCurrentUserProfile()
    {
        return $this->getClient()->get("{$this->baseUrl}/users/me/profile");
    }

    public function getUserProfile($userId, $token = null)
    {
        return $this->getClientWithToken($token)->get("{$this->baseUrl}/users/{$userId}/profile");
    }

    public function updateProfile($data)
    {
        return $this->getClient()->put("{$this->baseUrl}/users/me/profile", $data);
    }

    public function updateProfileWithFile($data, $file)
    {
        return $this->getClient()
            ->attach('profile_picture', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->put("{$this->baseUrl}/users/me/profile", $data);
    }

    /**
     * Get public profile picture for a user (no authentication required)
     */
    public function getProfilePicture($userId)
    {
        return $this->getPublicClient()->get("{$this->baseUrl}/users/{$userId}/profile-picture");
    }

    // --- PROGRAM STUDI ---

    public function getAllProgramStudi($params = [])
    {
        return $this->getPublicClient()->get("{$this->baseUrl}/program-studi", $params);
    }

    public function getProgramStudiById($id)
    {
        return $this->getPublicClient()->get("{$this->baseUrl}/program-studi/{$id}");
    }

    public function createProgramStudi($data)
    {
        return $this->getClient()->post("{$this->baseUrl}/program-studi", $data);
    }

    public function updateProgramStudi($id, $data)
    {
        return $this->getClient()->put("{$this->baseUrl}/program-studi/{$id}", $data);
    }

    public function deleteProgramStudi($id)
    {
        return $this->getClient()->delete("{$this->baseUrl}/program-studi/{$id}");
    }

    // --- POST MANAGEMENT (EDIT/DELETE) ---

    public function updatePost($postId, $data, $files = [], $token = null)
    {
        $request = $this->getClientWithToken($token);

        // Handle Multipart (Upload File) jika ada file baru
        if (!empty($files)) {
            foreach ($files as $file) {
                $request->attach(
                    'attachments',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                );
            }
        }

        // Konversi boolean ke string "true"/"false" untuk multipart form-data
        if (isset($data['is_grouped'])) {
            $boolValue = filter_var($data['is_grouped'], FILTER_VALIDATE_BOOLEAN);
            $data['is_grouped'] = $boolValue ? 'true' : 'false';
        }

        // PUT request dengan form data dan file
        return $request->put("{$this->baseUrl}/posts/{$postId}", $data);
    }

    public function deletePost($postId)
    {
        return $this->getClient()->delete("{$this->baseUrl}/posts/{$postId}");
    }

    // --- USER MANAGEMENT (Admin) ---

    /**
     * Register user baru (untuk admin yang membuat user)
     * Menggunakan endpoint /api/auth/register
     */
    public function createUser($userData)
    {
        return $this->getPublicClient()->post("{$this->baseUrl}/auth/register", $userData);
    }

    /**
     * Update user (change role, verification status, etc)
     * Endpoint: PUT /api/v1/users/:id
     */
    /**
     * Update user information
     * @param string $userId - User ID
     * @param array $updateData - Data to update (full_name, email, role, password)
     * @param string|null $token - Optional JWT token untuk authenticated request
     */
    public function updateUser($userId, $updateData, $token = null)
    {
        Log::info('KaryaApi:updateUser', [
            'userId' => $userId,
            'updateData' => $updateData,
            'token_exists' => !empty($token),
            'url' => "{$this->baseUrl}/v1/users/{$userId}",
        ]);
        
        $response = $this->getClientWithToken($token)->put("{$this->baseUrl}/v1/users/{$userId}", $updateData);
        
        Log::info('KaryaApi:updateUser:response', [
            'userId' => $userId,
            'status' => $response->status(),
            'successful' => $response->successful(),
        ]);
        
        return $response;
    }

    /**
     * Change user role to verifikator
     * Menggunakan updateUser() dengan role: 'verifikator'
     */
    public function changeUserRoleToVerifikator($userId)
    {
        return $this->updateUser($userId, ['role' => 'verifikator']);
    }

    /**
     * Change user's own password
     * Endpoint: POST /api/v1/users/change-password
     * @param array $data - Array dengan keys: old_password, new_password, confirm_password
     * @param string|null $token - Optional JWT token
     */
    public function changePassword($data, $token = null)
    {
        Log::info('KaryaApi:changePassword', [
            'endpoint' => "{$this->baseUrl}/v1/users/change-password",
            'has_token' => !empty($token),
            'token_length' => $token ? strlen($token) : 0,
            'data_keys' => array_keys($data)
        ]);
        
        $response = $this->getClientWithToken($token)->post("{$this->baseUrl}/v1/users/change-password", $data);
        
        Log::info('KaryaApi:changePassword:response', [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->json()
        ]);
        
        return $response;
    }

    /**
     * Reset user password (Admin Only)
     * Endpoint: POST /api/v1/users/:id/reset-password
     * @param string $userId - User ID to reset password for
     * @param string|null $token - Optional JWT token (must be admin)
     */
    public function resetPassword($userId, $token = null)
    {
        return $this->getClientWithToken($token)->post("{$this->baseUrl}/v1/users/{$userId}/reset-password");
    }

    // --- GENERIC HTTP METHODS (untuk endpoint yang tidak memiliki method khusus) ---

    /**
     * Generic GET request (authenticated)
     */
    public function get($endpoint, $params = [])
    {
        return $this->getClient()->get("{$this->baseUrl}{$endpoint}", $params);
    }

    /**
     * Generic GET request (public/unauthenticated)
     */
    public function getPublic($endpoint, $params = [])
    {
        return $this->getPublicClient()->get("{$this->baseUrl}{$endpoint}", $params);
    }

    /**
     * Generic POST request (authenticated)
     */
    public function post($endpoint, $data = [])
    {
        return $this->getClient()->post("{$this->baseUrl}{$endpoint}", $data);
    }

    /**
     * Generic PUT request (authenticated)
     */
    public function put($endpoint, $data = [])
    {
        return $this->getClient()->put("{$this->baseUrl}{$endpoint}", $data);
    }

    /**
     * Generic PATCH request (authenticated)
     */
    public function patch($endpoint, $data = [])
    {
        return $this->getClient()->patch("{$this->baseUrl}{$endpoint}", $data);
    }

    /**
     * Generic DELETE request (authenticated)
     */
    public function delete($endpoint, $data = [])
    {
        return $this->getClient()->delete("{$this->baseUrl}{$endpoint}", $data);
    }
}
