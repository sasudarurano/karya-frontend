<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
        $searchQuery    = trim($request->query('search', ''));
        $filterCategory = $request->query('category');
        $filterProdi    = $request->query('prodi');
        $page           = $request->query('page', 1);
        $limit          = 10;

        // Ambil data dari backend dengan filter dan pagination
        $params = [
            'search'   => $searchQuery,
            'category' => $filterCategory,
            'prodi'    => $filterProdi,
            'page'     => $page,
            'limit'    => $limit
        ];

        $response = $this->api->getAllPosts($params);

        if ($response->successful()) {
            $responseData = $response->json();
            
            // Backend returns nested structure: {success, data: {data: [], meta: {}}}
            if (isset($responseData['success']) && $responseData['success'] && isset($responseData['data'])) {
                $posts = $responseData['data']['data'] ?? [];
                $meta = $responseData['data']['meta'] ?? [];
            } else {
                // Fallback
                $posts = $responseData['data'] ?? [];
                $meta = [];
            }

            // Enrich dengan daftar prodi display (sekadar untuk tampilan tabel jika dibutuhkan)
            $posts = collect($posts)->map(function ($post) {
                $prodi = $post['author']['profile']['program_studi']['nama_program_studi'] ?? '-';
                $post['program_studi_display'] = $prodi;
                return $post;
            })->toArray();

            // Hitung statistik dari meta (backend mengirim global stats dalam meta)
            $total     = $meta['global_total'] ?? ($meta['total'] ?? count($posts));
            $published = $meta['published'] ?? 0;
            $pending   = $meta['unpublished'] ?? 0;
            
            // Opsi filter (untuk dropdown)
            // Kategori: bisa kita hardcode atau ambil dari spesifik API categories jika ada.
            // Di sini kita tetap butuh list Prodi lengkap untuk dropdown.
            try {
                $prodiResp = $this->api->getProgramStudis();
                if ($prodiResp->successful()) {
                    $prodiData = $prodiResp->json()['data'] ?? [];
                    $availableProdis = collect($prodiData)
                        ->pluck('nama_program_studi')
                        ->filter()
                        ->unique()
                        ->values()
                        ->all();
                }
            } catch (\Throwable $e) {
                Log::warning('Failed fetching program studi list', ['error' => $e->getMessage()]);
            }

            // List kategori (bisa ambil dari konstanta atau API lain, di sini kita ambil dari data yang ada di DB secara global jika mungkin, 
            // tapi sementara kita gunakan list statis atau biarkan kosong jika API categories belum ada)
            $availableCategories = ['PKM', 'Karya Tulis', 'Project Mandiri', 'Lainnya']; 
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
            Cache::forget('home_data_v2');
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
            Cache::forget('home_data_v2');
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
            Cache::forget('home_data_v2');
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
