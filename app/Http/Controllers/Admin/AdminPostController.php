<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPostController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Halaman Moderasi / Kurasi Karya (Admin Only)
     * Menampilkan semua karya termasuk yang belum dipublikasikan
     */
    public function index(Request $request)
    {
        $posts = [];
        $total = 0;
        $published = 0;
        $pending = 0;
        $meta = [];
        $error_message = null;
        $availableCategories = [];
        $availableProdis = [];

        // Filters
        $searchQuery   = trim($request->query('search', ''));
        $filterCategory = $request->query('category');
        $filterProdi    = $request->query('prodi');

        // Debug: Cek token dan user role
        Log::info('Admin Posts Index - Debug Info:', [
            'has_token' => !empty(session('api_token')),
            'token_preview' => session('api_token') ? substr(session('api_token'), 0, 20) . '...' : 'NO TOKEN',
            'user_role' => session('user.role'),
            'user_id' => session('user.id'),
        ]);

        // Ambil semua karya (published dan unpublished)
        $response = $this->api->getAllPosts();

        // Debug: Log response
        Log::info('Admin Posts Index - API Response:', [
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'has_data' => isset($response->json()['data']),
            'data_count' => isset($response->json()['data']) ? count($response->json()['data']) : 0,
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            
            // Backend returns nested structure: {success, data: {data: [], meta: {}}}
            // Check if response has success flag and nested data
            if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
                $allPosts = $responseData['data']['data'] ?? [];
                $meta = $responseData['data']['meta'] ?? [];
            } else {
                // Fallback: old structure or direct data array
                $allPosts = $responseData['data'] ?? [];
                $meta = [];
            }

            // Enrich dengan daftar prodi (author + contributors)
            $allPosts = collect($allPosts)->map(function ($post) {
                $programStudiList = [];

                $authorProdi = $post['author']['profile']['program_studi']['nama_program_studi'] ?? null;
                if ($authorProdi) {
                    $programStudiList[] = $authorProdi;
                }

                if (!empty($post['contributors']) && is_array($post['contributors'])) {
                    foreach ($post['contributors'] as $contrib) {
                        $contribProdi = $contrib['profile']['program_studi']['nama_program_studi'] ?? null;
                        if ($contribProdi) {
                            $programStudiList[] = $contribProdi;
                        }
                    }
                }

                // Unique & reindex
                $programStudiList = array_values(array_unique($programStudiList));

                $post['program_studi_list'] = $programStudiList;
                $post['program_studi_display'] = !empty($programStudiList)
                    ? implode(', ', $programStudiList)
                    : '-';

                return $post;
            });

            // Kumpulan opsi filter
            $availableCategories = $allPosts->pluck('category')->filter()->unique()->values()->all();
            $availableProdis = $allPosts->pluck('program_studi_list')
                ->flatten()
                ->filter()
                ->unique()
                ->values()
                ->all();

            // Ambil master prodi dari backend agar opsi selalu lengkap
            try {
                $prodiResp = $this->api->getProgramStudis();
                if ($prodiResp->successful()) {
                    $prodiData = $prodiResp->json()['data'] ?? [];
                    $masterProdis = collect($prodiData)
                        ->pluck('nama_program_studi')
                        ->filter()
                        ->values();
                    $availableProdis = collect($availableProdis)
                        ->merge($masterProdis)
                        ->unique()
                        ->values()
                        ->all();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed fetching program studi list', ['error' => $e->getMessage()]);
            }

            // Terapkan filter pencarian/kategori/prodi
            $filteredPosts = $allPosts->filter(function ($post) use ($searchQuery, $filterCategory, $filterProdi) {
                $passesSearch = true;
                if ($searchQuery !== '') {
                    $passesSearch = str_contains(strtolower($post['title'] ?? ''), strtolower($searchQuery))
                        || str_contains(strtolower($post['author']['full_name'] ?? ''), strtolower($searchQuery))
                        || str_contains(strtolower($post['author']['username'] ?? ''), strtolower($searchQuery));
                }

                $passesCategory = $filterCategory ? (($post['category'] ?? null) === $filterCategory) : true;

                $passesProdi = true;
                if ($filterProdi) {
                    $prodiList = $post['program_studi_list'] ?? [];
                    $passesProdi = in_array($filterProdi, $prodiList);
                }

                return $passesSearch && $passesCategory && $passesProdi;
            });
            
            // Log untuk debugging
            Log::info('Admin getAllPosts response:', [
                'response_has_success' => isset($responseData['success']),
                'response_structure' => array_keys($responseData),
                'nested_data_keys' => isset($responseData['data']) ? array_keys($responseData['data']) : 'NO DATA KEY',
                'total_posts' => $allPosts->count(),
                'first_post_keys' => ($allPosts->isNotEmpty() && isset($allPosts[0]) && is_array($allPosts[0])) ? array_keys($allPosts[0]) : [],
                'first_post_sample' => ($allPosts->isNotEmpty() && isset($allPosts[0])) ? json_encode($allPosts[0]) : 'NO DATA',
                'unpublished_count' => $allPosts->where('is_published', false)->count(),
                'meta_from_backend' => $meta,
            ]);
            
            // Hitung statistik dari meta atau dari data
            $total = $meta['total'] ?? count($allPosts);
            $published = $meta['published'] ?? $filteredPosts->where('is_published', true)->count();
            $pending = $meta['unpublished'] ?? ($total - $published);
            
            // Filter untuk menampilkan yang belum dipublikasikan terlebih dahulu
            $posts = $filteredPosts->sortBy(function ($post) {
                return ($post['is_published'] ?? false) ? 1 : 0;
            })->values()->toArray();
        } else {
            Log::error('Failed to fetch all posts for admin', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);
            
            // Provide helpful error message to user
            if ($response->status() === 401) {
                $error_message = 'Unauthorized: Token tidak valid atau sudah expired. Silakan login ulang.';
            } else if ($response->status() === 404) {
                $error_message = 'Backend endpoint /api/posts/admin/all belum tersedia. Hubungi tim backend untuk implementasi.';
            } else {
                $error_message = 'Gagal mengambil data karya. Status: ' . $response->status() . ' - ' . $response->body();
            }
        }

        return view('admin.posts.index', compact(
            'posts',
            'total',
            'published',
            'pending',
            'error_message',
            'meta',
            'availableCategories',
            'availableProdis',
            'searchQuery',
            'filterCategory',
            'filterProdi'
        ));
    }

    /**
     * Show post detail (Modal atau halaman terpisah)
     */
    public function show($id)
    {
        $post = null;
        $error_message = null;

        $response = $this->api->getPostById($id);

        if ($response->successful()) {
            $post = $response->json()['data'] ?? null;

            if (!$post) {
                return back()->with('error', 'Karya tidak ditemukan.');
            }

            // DEBUG: Log all available fields
            Log::info('Admin Post fields available:', [
                'post_id' => $id,
                'all_keys' => array_keys($post),
                'hki_document' => $post['hki_document'] ?? 'NOT_FOUND',
                'post_document' => $post['post_document'] ?? 'NOT_FOUND',
                'document' => $post['document'] ?? 'NOT_FOUND',
                'pdf' => $post['pdf'] ?? 'NOT_FOUND',
            ]);

            return view('admin.posts.show', compact('post'));
        } else {
            Log::error('Failed to fetch post detail for admin', [
                'post_id' => $id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            return back()->with('error', 'Gagal mengambil detail karya.');
        }
    }

    /**
     * Toggle Publish/Unpublish Status (Admin Only)
     */
    public function togglePublish($id)
    {
        $response = $this->api->togglePublish($id);

        if ($response->successful()) {
            return back()->with('success', 'Status publikasi berhasil diubah.');
        }

        Log::error('Failed to toggle publish status', [
            'post_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal mengubah status publikasi.');
    }

    /**
     * Request Revision - Minta user untuk merevisi karya mereka
     */
    public function requestRevision($id, Request $request)
    {
        $validated = $request->validate([
            'revision_comment' => 'required|string|min:5|max:500',
        ]);

        // Panggil API backend untuk menyimpan request revisi
        // TODO: Backend harus implementasi endpoint POST /api/posts/{id}/request-revision
        $response = $this->api->post("/posts/{$id}/request-revision", [
            'revision_comment' => $validated['revision_comment'],
            'moderated_by' => session('user.id'),
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Permintaan revisi berhasil dikirim ke user.');
        }

        Log::error('Failed to request revision', [
            'post_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal mengirim permintaan revisi.');
    }

    /**
     * Reject Post - Tolak karya dengan alasan
     */
    public function reject($id, Request $request)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        // Panggil API backend untuk menolak karya
        // TODO: Backend harus implementasi endpoint POST /api/posts/{id}/reject
        $response = $this->api->post("/posts/{$id}/reject", [
            'rejection_reason' => $validated['rejection_reason'],
            'rejected_by' => session('user.id'),
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Karya berhasil ditolak dan user telah diberitahu.');
        }

        Log::error('Failed to reject post', [
            'post_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal menolak karya.');
    }

    /**
     * Clear Rejection - Batalkan status penolakan
     */
    public function clearRejection($id)
    {
        $response = $this->api->post("/posts/{$id}/clear-rejection", [
            'cleared_by' => session('user.id'),
        ]);

        if ($response->successful()) {
            return back()->with('success', 'Status penolakan berhasil dibatalkan.');
        }

        Log::error('Failed to clear rejection', [
            'post_id' => $id,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return back()->with('error', 'Gagal membatalkan penolakan.');
    }
}
