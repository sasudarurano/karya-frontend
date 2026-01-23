<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session; // Tambahkan facade Session
use Carbon\Carbon;

class PostController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Halaman Beranda & Pencarian (Public)
     */
    public function index(Request $request)
    {
        // 1. Logika View All (Sort - Popular/Newest)
        if ($request->has('sort') && in_array($request->input('sort'), ['popular', 'newest'])) {
            $sort = $request->input('sort');
            
            if ($sort === 'popular') {
                $response = $this->api->getPopularPosts(1000); // Get many posts
                $title = 'Karya Paling Populer';
            } else {
                $response = $this->api->getNewestPosts(1000); // Get many posts
                $title = 'Karya Baru Diupload';
            }
            
            $allPosts = $response->successful() ? ($response->json()['data'] ?? []) : [];
            
            Log::info("View all {$sort} posts", [
                'sort' => $sort,
                'count' => count($allPosts),
                'status' => $response->status()
            ]);
            
            return view('posts.all', compact('allPosts', 'sort', 'title'));
        }

        // 2. Logika Pencarian & Filter Kategori
        if ($request->has('search') || $request->has('category')) {
            $filters = array_filter([
                'search' => $request->input('search'),
                'category' => $request->input('category'),
                'limit' => $request->input('limit', 20),
                'page' => $request->input('page', 1),
            ], fn($value) => $value !== null && $value !== '');
            
            Log::info('Search request initiated', [
                'filters' => $filters,
                'request_data' => $request->all()
            ]);
            
            // A. Cari Postingan/Karya
            $response = $this->api->getPosts($filters);
            
            // B. Cari Users (FIX: Menambahkan logika pencarian user)
            $users = [];
            if ($request->has('search') && !empty($request->input('search'))) {
                try {
                    // Menggunakan endpoint general /users dengan parameter search
                    // Pastikan Service KaryaApi memiliki method get() yang fleksibel atau buat method searchUsers
                    $userResponse = $this->api->get('/users', ['search' => $request->input('search'), 'limit' => 5]);
                    
                    if ($userResponse->successful()) {
                        $users = $userResponse->json()['data'] ?? [];
                    }
                } catch (\Exception $e) {
                    Log::warning('User search failed: ' . $e->getMessage());
                }
            }
            
            if (!$response->successful()) {
                Log::error('Search API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'filters_sent' => $filters
                ]);
            }
            
            $searchResults = $response->successful() ? ($response->json()['data'] ?? []) : [];
            $apiError = !$response->successful() ? ($response->json()['message'] ?? $response->body()) : null;
            
            // Mengirim data users juga ke view search_results
            return view('search_results', compact('searchResults', 'users', 'apiError'));
        }

        // 3. Logika Halaman Utama (Popular & Newest)
        // Mengambil data dengan limit 6 karya per bagian
        $newestPage = $request->input('newest_page', 1);
        $newestResp = $this->api->getNewestPosts(6, $newestPage);
        $popularResp = $this->api->getPopularPosts(6);

        // Extract posts and pagination info from newest posts response
        $newestData = $newestResp->successful() ? $newestResp->json()['data'] : [];
        $newestPosts = $newestData['posts'] ?? [];
        $newestPagination = $newestData['pagination'] ?? null;
        
        $popularPosts = ($popularResp->successful() ? ($popularResp->json()['data'] ?? []) : []);

        return view('home', compact('newestPosts', 'newestPagination', 'popularPosts'));
    }

    /**
     * Halaman Detail Karya
     */
    public function show($id)
    {
        // FIX: Ambil token dari session agar backend tahu siapa yang melihat post ini
        // Ini memperbaiki masalah status Like dan Follow yang tidak sinkron saat refresh
        $token = session('api_token');

        // Backend akan melempar 404 jika is_published = false
        // Kita kirim token (param ke-2) ke method getPostById
        $response = $this->api->getPostById($id, $token);
        
        Log::info('Post detail fetch attempt', [
            'post_id' => $id,
            'status' => $response->status(),
            'successful' => $response->successful(),
            'has_token' => !empty($token),
            'token_length' => strlen($token ?? '')
        ]);

        if ($response->successful()) {
            $post = $response->json()['data'];
            
            // DEBUG: Log all available fields in post to see HKI document field
            Log::info('Post fields available:', [
                'post_id' => $id,
                'all_keys' => array_keys($post),
                'hki_document' => $post['hki_document'] ?? 'NOT_FOUND',
                'post_document' => $post['post_document'] ?? 'NOT_FOUND',
                'document' => $post['document'] ?? 'NOT_FOUND',
                'pdf' => $post['pdf'] ?? 'NOT_FOUND',
            ]);
            
            // DEBUG: Log the isLiked and is_followed status from backend
            Log::info('Post data received from backend', [
                'post_id' => $id,
                'isLiked' => $post['isLiked'] ?? 'NOT_SET',
                'author_is_followed' => $post['author']['is_followed'] ?? 'NOT_SET',
                'likeCount' => $post['likeCount'] ?? 0,
                'has_isLiked_field' => isset($post['isLiked']),
                'has_is_followed_field' => isset($post['author']['is_followed'])
            ]);
            
            // Get comments for this post
            // Kirim token juga untuk validasi kepemilikan komentar (tombol delete/edit)
            $commentsResponse = $this->api->getComments($id, $token);
            $comments = $commentsResponse->successful() ? ($commentsResponse->json()['data'] ?? []) : [];
            
            /** * Catatan: Data statistik kreator kini tersedia di:
             * $post['author']['stats']['posts']
             * $post['author']['stats']['followers']
             * Status like tersedia di: $post['isLiked']
             * Status follow tersedia di: $post['author']['is_followed']
             */
            
            return view('posts.show', compact('post', 'comments'));
        }

        Log::error('Post detail fetch failed', [
            'post_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        // Mengarahkan ke halaman 404 jika karya tidak ditemukan atau belum dipublikasikan
        abort(404, 'Karya tidak ditemukan atau belum dipublikasikan.');
    }

    /**
     * Dashboard Personal (Feed Karya dari orang yang diikuti)
     */
    public function dashboard()
    {
        // FIX: Pastikan memanggil endpoint feed dengan token
        $token = session('api_token');
        
        if (!$token) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $response = $this->api->getFollowingPosts($token);
        
        if ($response->status() === 401) {
            return redirect()->route('login')->with('error', 'Sesi habis, silakan login kembali.');
        }

        $posts = $response->successful() ? ($response->json()['data'] ?? []) : [];
        
        // Get current user's profile for profile picture
        $user = session('user');
        $profilePicture = null;
        
        if ($user) {
            try {
                // Gunakan token juga saat fetch profile sendiri untuk akurasi data
                $profileResponse = $this->api->getCurrentUserProfile($token);
                if ($profileResponse->successful()) {
                    $profileData = $profileResponse->json()['data'] ?? null;
                    
                    // Extract profile picture if available
                    if (isset($profileData['profile_picture']['file_url'])) {
                        $cleanPath = str_replace('\\', '/', $profileData['profile_picture']['file_url']);
                        $profilePicture = str_starts_with($cleanPath, 'http') 
                            ? $cleanPath 
                            : rtrim(env('BACKEND_API_URL'), '/') . '/' . ltrim($cleanPath, '/');
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Failed to get profile picture for dashboard: " . $e->getMessage());
            }
        }
        
        return view('dashboard', compact('posts', 'profilePicture'));
    }

    /**
     * Form Upload Karya Baru
     */
    public function create()
    {
        // Mengambil daftar user untuk pilihan kontributor/tim
        // Gunakan endpoint /v1/users/list yang bisa diakses oleh semua user terautentikasi
        $token = session('api_token');
        $response = $this->api->getUsersList($token);
        $users = $response->successful() ? ($response->json()['data'] ?? []) : [];

        return view('posts.create', compact('users'));
    }

    /**
     * Proses Simpan & Publikasi Karya
     */
    public function store(Request $request)
    {
        Log::info('Upload request received', [
            'all_fields' => array_keys($request->all()),
            'has_post_images' => $request->hasFile('post_images'),
            'has_post_document' => $request->hasFile('post_document'),
            'post_images_count' => count($request->file('post_images') ?? []),
            'contributor_ids' => $request->contributor_ids ?? []
        ]);

        // 1. Validasi Input (sesuai backend CreatePostDTO)
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:lomba,tugas kelas,ta/skripsi,kp/magang,penelitian/pkm,project mandiri',
            'caption' => 'nullable|string',
            'is_grouped' => 'required|in:true,false',
            'supervisor_id' => 'nullable|string',
            'contributor_ids' => 'nullable|array',
            'contributor_ids.*' => 'nullable|string',
            'post_images' => 'required|array|min:1|max:5',
            'post_images.*' => 'file|mimes:jpg,jpeg,png,webp|max:10240',
            'post_document' => 'nullable|file|mimes:pdf|max:10240',
            'url_youtube' => 'nullable|url',
            'url_karya' => 'nullable|url',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        Log::info('Validation passed', ['validated_keys' => array_keys($validated)]);

        // 2. Transformasi Data untuk Backend (hanya field yang diterima CreatePostDTO)
        $supervisor = $request->supervisor_id ? [$request->supervisor_id] : [];
        $contributors = array_values(array_filter($request->contributor_ids ?? []));
        $isGrouped = filter_var($request->is_grouped, FILTER_VALIDATE_BOOLEAN);

        // Jika tidak ada contributor, gunakan current user
        if (empty($contributors)) {
            $contributors = [auth()->id()]; 
            // Note: auth()->id() mungkin null jika pakai Session manual, fallback:
            if (!$contributors[0]) $contributors = [session('user')['id'] ?? null];
        }

        $data = [
            'title'           => $request->title,
            'category'        => $request->category,
            'caption'         => $request->caption,
            'is_grouped'      => $isGrouped,
            // Kirim supervisor_id langsung agar tersimpan di kolom posts.supervisor_id
            // Filter empty string menjadi null untuk menghindari validation error di backend
            'supervisor_id'   => !empty($request->supervisor_id) ? $request->supervisor_id : null,
            // Opsional: tetap kirim lecturer_ids untuk kompatibilitas (pivot jika ada)
            'lecturer_ids'    => $supervisor,
            'contributor_ids' => $contributors,
            'url_youtube'     => !empty($request->url_youtube) ? $request->url_youtube : null,
            'url_karya'       => !empty($request->url_karya) ? $request->url_karya : null,
            'start_date'      => !empty($request->start_date) ? $request->start_date : null,
            'end_date'        => !empty($request->end_date) ? $request->end_date : null,
            'is_published'    => false, // Default false untuk moderasi admin
        ];

        // 3. Pengolahan File (Multipart - max 5 images + 1 PDF)
        $allFiles = [];
        if ($request->hasFile('post_images')) {
            foreach($request->file('post_images') as $file) {
                $allFiles[] = $file;
            }
        }
        if ($request->hasFile('post_document')) {
            $allFiles[] = $request->file('post_document');
        }

        Log::info('Creating post', [
            'title' => $data['title'],
            'category' => $data['category'],
            'is_grouped' => $data['is_grouped'],
            'file_count' => count($allFiles),
        ]);

        // 4. Kirim ke API Node.js dengan Token
        $token = session('api_token');
        $response = $this->api->createPost($data, $allFiles, $token);

        if ($response->successful()) {
            return redirect()->route('posts.my-posts')->with('success', 'Karya berhasil diupload! Menunggu verifikasi admin.');
        }

        // 5. Penanganan Error API
        $errorJson = null;
        try { $errorJson = $response->json(); } catch (\Throwable $t) {}
        $errorBody = $response->body();
        Log::error('Upload failed', [
            'status' => $response->status(),
            'json' => $errorJson,
            'body' => $errorBody
        ]);
        
        $errorMessage = is_array($errorJson) && isset($errorJson['message']) ? $errorJson['message'] : ($errorBody ?: 'Terjadi kesalahan server saat menyimpan karya.');
        return back()->withInput()->with('error', 'Gagal upload: ' . $errorMessage);
    }

    /**
     * Proxy Image: Mengunduh gambar dari backend ke storage lokal Laravel
     * untuk menghindari masalah CORS dan menghemat bandwith API.
     */
    public function getPostImage(Request $request)
    {
        $path = $request->query('path');

        if (!$path) {
            return redirect('https://via.placeholder.com/600x400?text=No+Image');
        }

        $filename = basename(str_replace('\\', '/', $path));
        $localPath = 'posts-images/' . $filename;

        if (!Storage::disk('public')->exists($localPath)) {
            $cleanPath = str_replace('\\', '/', $path);
            $nodeBaseUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
            $targetUrl = $nodeBaseUrl . '/' . ltrim($cleanPath, '/');

            try {
                $response = Http::timeout(10)->get($targetUrl);
                if ($response->successful()) {
                    Storage::disk('public')->put($localPath, $response->body());
                } else {
                    return redirect('https://via.placeholder.com/600x400?text=Image+Not+Found');
                }
            } catch (\Exception $e) {
                return redirect('https://via.placeholder.com/600x400?text=Connection+Error');
            }
        }

        return response()->file(storage_path('app/public/' . $localPath));
    }

    public function myPosts()
    {
        // Memanggil API Service untuk mengambil karya milik user yang sedang login
        $token = session('api_token');
        
        if (!$token) {
            return redirect()->route('login');
        }

        $response = $this->api->getMyPosts($token); 
        
        Log::info('My posts fetch attempt', [
            'status' => $response->status(),
            'successful' => $response->successful(),
        ]);

        if ($response->successful()) {
            $posts = $response->json()['data'] ?? [];
            Log::info('My posts fetched successfully', ['count' => count($posts)]);
            return view('posts.my-posts', compact('posts'));
        }
        
        Log::error('Failed to fetch my posts', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal memuat karya Anda. Error: ' . $response->status());
    }
}