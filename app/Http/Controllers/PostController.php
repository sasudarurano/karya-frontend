<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session; // Tambahkan facade Session
use Illuminate\Support\Facades\Cache; // Tambahkan facade Cache
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
            $page = $request->input('page', 1);
            $limit = 20; // Reduce limit from 1000 to 20 for performance

            $cacheKey = "view_all_{$sort}_page_{$page}";

            // Cache for 5 minutes (300 seconds)
            $data = Cache::remember($cacheKey, 300, function () use ($sort, $limit, $page) {
                if ($sort === 'popular') {
                    $response = $this->api->getPopularPosts($limit, $page);
                } else {
                    $response = $this->api->getNewestPosts($limit, $page);
                }
                return $response->successful() ? ($response->json()['data'] ?? null) : null;
            });

            $title = $sort === 'popular' ? 'Karya Paling Populer' : 'Karya Baru Diupload';
            
            // Handle both structure types (just in case)
            $allPosts = $data['posts'] ?? ($data ?? []); // If backend returns object with posts key or just array
            $pagination = $data['pagination'] ?? null;
            
            // If data is just an array (fallback), manually slice for "fake" pagination (if backend update failed/delayed)
            if (is_array($data) && !isset($data['posts']) && count($data) > $limit) {
                // This fallback shouldn't be reached if backend is updated correctly
                $allPosts = array_slice($data, 0, $limit); 
            }

            return view('posts.all', compact('allPosts', 'sort', 'title', 'pagination'));
        }

        // 2. Logika Pencarian & Filter Kategori
        if ($request->has('search') || $request->has('category')) {
            $filters = array_filter([
                'search' => $request->input('search'),
                'category' => $request->input('category'),
                'limit' => $request->input('limit', 20),
                'page' => $request->input('page', 1),
            ], fn($value) => $value !== null && $value !== '');
            
            // Cache search results for 1 minute only
            $cacheKey = 'search_' . md5(json_encode($filters));
            
            $searchData = Cache::remember($cacheKey, 60, function () use ($filters, $request) {
                $response = $this->api->getPosts($filters);
                
                $users = [];
                // Only search users if search query present
                if (isset($filters['search'])) {
                    try {
                        $userResponse = $this->api->searchUsers(['search' => $filters['search'], 'limit' => 5]);
                        if ($userResponse->successful()) {
                            $users = $userResponse->json()['data'] ?? [];
                        }
                    } catch (\Exception $e) {}
                }

                return [
                    'searchResults' => $response->successful() ? ($response->json()['data'] ?? []) : [],
                    'apiError' => !$response->successful() ? ($response->json()['message'] ?? $response->body()) : null,
                    'users' => $users
                ];
            });
            
            $searchResults = $searchData['searchResults'];
            $users = $searchData['users'];
            $apiError = $searchData['apiError'];
            
            return view('search_results', compact('searchResults', 'users', 'apiError'));
        }

        // 3. Logika Halaman Utama (Popular & Terbaru)
        $cacheKey = "home_data_v3";
        
        $homeData = Cache::remember($cacheKey, 300, function () {
            $newestResp = $this->api->getNewestPosts(12, 1);
            $popularResp = $this->api->getPopularPosts(12, 1);

            $newestDataRaw = $newestResp->successful() ? $newestResp->json()['data'] : [];
            $popularDataRaw = $popularResp->successful() ? $popularResp->json()['data'] : [];

            return [
                'newestPosts' => $newestDataRaw['posts'] ?? [],
                'popularPosts' => $popularDataRaw['posts'] ?? ($popularDataRaw ?? [])
            ];
        });

        $newestPosts = $homeData['newestPosts'];
        $popularPosts = $homeData['popularPosts'];

        return view('home', compact('newestPosts', 'popularPosts'));
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
        $externalData = $this->getExternalLecturersAndStudents();
        $lecturers = $externalData['lecturers'];
        $students = $externalData['students'];

        return view('posts.create', compact('lecturers', 'students'));
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
            'gdrive_url' => 'nullable|url',
            'post_image' => 'nullable|image|mimes:png,jpeg,jpg|max:2560',
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
            'gdrive_url'      => !empty($request->gdrive_url) ? $request->gdrive_url : null,
            'is_published'    => false, // Default false untuk moderasi admin
        ];

        // 3. Pengolahan File (Multipart - PDF dan Foto)
        $allFiles = [];
        if ($request->hasFile('post_image')) {
            $allFiles[] = $request->file('post_image');
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

        if (is_array($errorJson) && !empty($errorJson['details']) && is_array($errorJson['details'])) {
            $detailMessages = collect($errorJson['details'])
                ->map(function ($detail) {
                    $field = $detail['field'] ?? null;
                    $message = $detail['message'] ?? null;

                    return $field && $message ? "{$field}: {$message}" : $message;
                })
                ->filter()
                ->implode('; ');

            if ($detailMessages !== '') {
                $errorMessage .= " ({$detailMessages})";
            }
        }

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

    }

    /**
     * Get lecturers and students from external API with caching
     */
    private function getExternalLecturersAndStudents()
    {
        $lecturers = Cache::get('external_lecturers');
        if (!$lecturers) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(15)->get('https://apps2.mdp.ac.id/sipenamas/lppm/lookup');
                if ($response->successful()) {
                    $data = $response->json();
                    $list = [];
                    foreach (($data['message'] ?? []) as $item) {
                        $list[] = [
                            'id' => $item['id'],
                            'full_name' => preg_replace('/\s*\([^)]*\)$/', '', $item['value']),
                            'username' => '',
                        ];
                    }
                    if (!empty($list)) {
                        Cache::put('external_lecturers', $list, 86400);
                        $lecturers = $list;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch external lecturers: " . $e->getMessage());
            }
        }

        $students = Cache::get('external_students');
        if (!$students) {
            try {
                $response = \Illuminate\Support\Facades\Http::withoutVerifying()->timeout(15)->get('https://apps2.mdp.ac.id/sipenamas/lppm/lookupmhs');
                if ($response->successful()) {
                    $data = $response->json();
                    $list = [];
                    foreach (($data['message'] ?? []) as $item) {
                        $list[] = [
                            'id' => $item['id'],
                            'full_name' => $item['value'],
                            'username' => $item['id'],
                        ];
                    }
                    if (!empty($list)) {
                        Cache::put('external_students', $list, 86400);
                        $students = $list;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to fetch external students: " . $e->getMessage());
            }
        }

        return [
            'lecturers' => $lecturers ?? [],
            'students' => $students ?? [],
        ];
    }
}
