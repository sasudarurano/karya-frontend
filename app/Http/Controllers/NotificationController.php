<?php

namespace App\Http\Controllers;

use App\Services\KaryaApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $api;

    public function __construct(KaryaApi $api)
    {
        $this->api = $api;
    }

    /**
     * Get all notifications for current user
     */
    public function index()
    {
        $notifications = [];
        $error_message = null;

        $response = $this->api->get('/notifications');

        if ($response->successful()) {
            $data = $response->json()['data'] ?? [];
            $notifications = is_array($data) ? $data : [$data];
            
            // Map is_read to read_at for compatibility
            $notifications = array_map(function($notification) {
                if (!isset($notification['is_read'])) {
                    $notification['is_read'] = !empty($notification['read_at']);
                }
                return $notification;
            }, $notifications);
        } else {
            Log::warning('Failed to fetch notifications', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            
            $notifications = [];
        }

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Get unread notification count (for bell icon)
     */
    public function getUnreadCount()
    {
        $response = $this->api->get('/notifications/unread-count');

        if ($response->successful()) {
            $responseData = $response->json();
            $count = $responseData['data']['unread_count'] ?? ($responseData['unread_count'] ?? 0);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'unread_count' => $count
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'data' => [
                'unread_count' => 0
            ],
        ], $response->status());
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        // TODO: Backend must implement PATCH /api/notifications/{id}/mark-as-read endpoint
        $response = $this->api->patch("/notifications/{$notificationId}/mark-as-read");

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi ditandai sebagai sudah dibaca.',
            ]);
        }

        Log::error('Failed to mark notification as read', [
            'notification_id' => $notificationId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menandai notifikasi sebagai sudah dibaca.',
        ], $response->status());
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        // TODO: Backend must implement PATCH /api/notifications/mark-all-as-read endpoint
        $response = $this->api->patch('/notifications/mark-all-as-read');

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi ditandai sebagai sudah dibaca.',
            ]);
        }

        Log::error('Failed to mark all notifications as read', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menandai semua notifikasi.',
        ], $response->status());
    }

    /**
     * Delete a notification
     */
    public function delete($notificationId)
    {
        // TODO: Backend must implement DELETE /api/notifications/{id} endpoint
        $response = $this->api->delete("/notifications/{$notificationId}");

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi dihapus.',
            ]);
        }

        Log::error('Failed to delete notification', [
            'notification_id' => $notificationId,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus notifikasi.',
        ], $response->status());
    }
}
