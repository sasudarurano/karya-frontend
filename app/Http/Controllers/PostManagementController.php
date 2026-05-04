<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PostManagementController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Show edit form for a post
     */
    public function edit($postId)
    {
        try {
            $response = $this->api->getPostById($postId);
            
            if (!$response->successful()) {
                return back()->with('error', 'Karya tidak ditemukan.');
            }

            $post = $response->json()['data'] ?? null;

            // Check if user owns this post
            $currentUser = Session::get('user');
            if (!$currentUser || $post['user_id'] !== $currentUser['id']) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengedit karya ini.');
            }

            // Mengambil daftar user untuk pilihan kontributor/tim/supervisor
            $token = session('api_token');
            $usersResponse = $this->api->getUsersList($token);
            $users = $usersResponse->successful() ? ($usersResponse->json()['data'] ?? []) : [];

            return view('posts.edit', compact('post', 'users'));
        } catch (\Exception $e) {
            Log::error("Post edit view error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan.');
        }
    }

    /**
     * Update a post
     */
    public function update(Request $request, $postId)
    {
        // Validasi Input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:lomba,tugas kelas,ta/skripsi,kp/magang,penelitian/pkm,project mandiri',
            'caption' => 'nullable|string',
            'is_grouped' => 'required|in:true,false',
            'supervisor_id' => 'nullable|string',
            'contributor_ids' => 'nullable|array',
            'contributor_ids.*' => 'nullable|string',
            'gdrive_url' => 'nullable|url',
            'post_document' => 'nullable|file|mimes:pdf|max:10240',
            'url_youtube' => 'nullable|url',
            'url_karya' => 'nullable|url',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            // Transform data for backend
            // Backend UpdatePostDTO expects 'supervisor_id' as a single UUID string
            $supervisorId = !empty($request->supervisor_id) ? $request->supervisor_id : null;
            // 'contributor_ids' expects an array of UUID strings; set null if empty
            $contributorsArr = array_values(array_filter($request->contributor_ids ?? []));
            $contributors = count($contributorsArr) > 0 ? $contributorsArr : null;
            $isGrouped = filter_var($request->is_grouped, FILTER_VALIDATE_BOOLEAN);

            $data = [
                'title' => $validated['title'],
                'category' => $validated['category'],
                'caption' => $validated['caption'] ?? null,
                'is_grouped' => $isGrouped,
                'supervisor_id' => $supervisorId,
                'contributor_ids' => $contributors,
                'url_youtube' => !empty($validated['url_youtube']) ? $validated['url_youtube'] : null,
                'url_karya' => !empty($validated['url_karya']) ? $validated['url_karya'] : null,
                'start_date' => !empty($validated['start_date']) ? $validated['start_date'] : null,
                'end_date' => !empty($validated['end_date']) ? $validated['end_date'] : null,
                'gdrive_url' => !empty($validated['gdrive_url']) ? $validated['gdrive_url'] : null,
            ];

            // Include updated existing file ids (as JSON string) so backend replaces correctly
            $rawContentIds = $request->input('content_file_ids');
            if ($rawContentIds !== null && $rawContentIds !== '') {
                $data['content_file_ids'] = $rawContentIds; // backend parses string → array
            } else {
                // If user removed all existing files, send explicit null to clear when no new files
                $data['content_file_ids'] = null;
            }

            // Collect files only if provided
            $allFiles = [];
            if ($request->hasFile('post_document') && $request->file('post_document')->isValid()) {
                $allFiles[] = $request->file('post_document');
            }

            Log::info('Update post request', [
                'post_id' => $postId,
                'data_keys' => array_keys($data),
                'file_count' => count($allFiles),
                'has_images' => $request->hasFile('post_images'),
                'has_document' => $request->hasFile('post_document'),
                'content_file_ids_raw' => $rawContentIds,
            ]);

            // Send to API
            $token = session('api_token');
            $response = $this->api->updatePost($postId, $data, $allFiles, $token);

            if ($response->successful()) {
                return redirect()->route('posts.show', $postId)
                    ->with('success', 'Karya berhasil diperbarui!');
            }

            // Handle API error
            $errorJson = null;
            try { $errorJson = $response->json(); } catch (\Throwable $t) {}
            $errorBody = $response->body();
            
            Log::error('Update API error', [
                'post_id' => $postId,
                'status' => $response->status(),
                'json' => $errorJson,
                'body' => $errorBody
            ]);
            
            $errorMessage = is_array($errorJson) && isset($errorJson['message']) ? $errorJson['message'] : ($errorBody ?: 'Terjadi kesalahan server saat memperbarui karya.');
            return back()->withInput()->with('error', 'Gagal update: ' . $errorMessage);

        } catch (\Exception $e) {
            Log::error("Post update error", [
                'post_id' => $postId,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui karya: ' . $e->getMessage());
        }
    }

    /**
     * Delete a post
     */
    public function destroy($postId)
    {
        try {
            // Get post to check ownership
            $postResponse = $this->api->getPostById($postId);
            
            if (!$postResponse->successful()) {
                return back()->with('error', 'Karya tidak ditemukan.');
            }

            $post = $postResponse->json()['data'] ?? null;
            $currentUser = Session::get('user');

            // Check if user owns this post or is admin
            if (!$currentUser || 
                ($post['user_id'] !== $currentUser['id'] && $currentUser['role'] !== 'admin')) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menghapus karya ini.');
            }

            $response = $this->api->deletePost($postId);

            if ($response->successful()) {
                return redirect()->route('posts.my-posts')
                    ->with('success', 'Karya berhasil dihapus!');
            }

            return back()->with('error', $response->json()['message'] ?? 'Gagal menghapus karya.');
        } catch (\Exception $e) {
            Log::error("Post delete error: " . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menghapus karya.');
        }
    }
}
