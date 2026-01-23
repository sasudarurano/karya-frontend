<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Store a new comment on a post
     */
    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        try {
            $response = $this->api->createComment($postId, $request->content);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Komentar berhasil ditambahkan!',
                    'data' => $data['data'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal menambahkan komentar.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Comment store error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menambahkan komentar.'
            ], 500);
        }
    }

    /**
     * Update an existing comment
     */
    public function update(Request $request, $postId, $commentId)
    {
        $request->validate([
            'content' => 'required|string|max:1000'
        ]);

        try {
            $response = $this->api->updateComment($postId, $commentId, $request->content);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Komentar berhasil diperbarui!',
                    'data' => $data['data'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal memperbarui komentar.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Comment update error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui komentar.'
            ], 500);
        }
    }

    /**
     * Delete a comment
     */
    public function destroy($postId, $commentId)
    {
        try {
            $response = $this->api->deleteComment($postId, $commentId);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Komentar berhasil dihapus!',
                    'data' => $data['data'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal menghapus komentar.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Comment delete error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus komentar.'
            ], 500);
        }
    }
}
