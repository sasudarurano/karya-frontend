<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VoteController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Vote (like/dislike) a post
     */
    public function vote(Request $request, $postId)
    {
        $request->validate([
            'vote_type' => 'nullable|boolean' // true = like, false = dislike
        ]);

        try {
            $voteType = $request->input('vote_type', true); // default true (like)
            $response = $this->api->votePost($postId, $voteType);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => $voteType ? 'Berhasil menyukai karya!' : 'Berhasil tidak menyukai karya!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal melakukan vote.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Vote error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan vote.'
            ], 500);
        }
    }

    /**
     * Remove vote from a post
     */
    public function unvote($postId)
    {
        try {
            $response = $this->api->unvotePost($postId);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vote berhasil dihapus!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal menghapus vote.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Unvote error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus vote.'
            ], 500);
        }
    }

    /**
     * Toggle vote (PATCH) - Sesuai dengan backend API
     * Backend akan automatically handle: cast, remove, atau update vote
     */
    public function toggleVote(Request $request, $postId)
    {
        $request->validate([
            'vote_type' => 'required|boolean' // true = like, false = dislike
        ]);

        try {
            $voteType = $request->input('vote_type');
            $response = $this->api->toggleVote($postId, $voteType);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'message' => $data['message'] ?? 'Vote berhasil diproses',
                    'data' => $data['data'] ?? null
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal melakukan vote.'
            ], $response->status());
        } catch (\Exception $e) {
            Log::error("Toggle vote error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat melakukan vote.'
            ], 500);
        }
    }
}
