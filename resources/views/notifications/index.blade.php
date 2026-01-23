@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Notifikasi</h1>
        @if(isset($notifications) && count($notifications) > 0)
        <button type="button" onclick="markAllAsRead()" id="markAllBtn" class="px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition cursor-pointer">
            Tandai semua sebagai sudah dibaca
        </button>
        @endif
    </div>

    @if(isset($notifications) && count($notifications) > 0)
        <div class="space-y-3">
            @foreach($notifications as $notification)
            <div class="notification-item bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition {{ !($notification['read_at'] ?? true) ? 'bg-blue-50/30 border-blue-200' : '' }}" data-notification-id="{{ $notification['id'] }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        {{-- Notification Type Icon --}}
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 mt-1">
                                @php
                                    $type = $notification['type'] ?? 'info';
                                    
                                    // Define icon colors based on notification type
                                    $iconMap = [
                                        'user_registration' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => '👤'],
                                        'post_needs_review' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => '📝'],
                                        'post_pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'icon' => '⏳'],
                                        'post_published' => ['bg' => 'bg-green-100', 'text' => 'text-green-600', 'icon' => '✓'],
                                        'post_rejected' => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => '✕'],
                                        'post_revision' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => '↺'],
                                        'post_liked' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-600', 'icon' => '❤️'],
                                        'post_milestone_10' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => '🎉'],
                                        'post_milestone_50' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-600', 'icon' => '🌟'],
                                        'post_milestone_100' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => '🏆'],
                                        'comment_received' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => '💬'],
                                        'user_followed' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => '👥'],
                                        'default' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'ⓘ'],
                                    ];
                                    
                                    $iconConfig = $iconMap[$type] ?? $iconMap['default'];
                                    $iconColor = $iconConfig['bg'] . ' ' . $iconConfig['text'];
                                    $icon = $iconConfig['icon'];
                                    
                                    // Determine link based on notification type
                                    $relatedUrl = null;
                                    $data = $notification['data'] ?? [];
                                    
                                    switch($type) {
                                        case 'user_registration':
                                            // Only build URL if route exists and parameter is available
                                            if (!empty($data['user_id']) && \Illuminate\Support\Facades\Route::has('admin.users.edit')) {
                                                $relatedUrl = route('admin.users.edit', ['id' => $data['user_id']]);
                                            }
                                            break;
                                        case 'post_needs_review':
                                            if (!empty($data['post_id']) && \Illuminate\Support\Facades\Route::has('admin.posts.moderate')) {
                                                $relatedUrl = route('admin.posts.moderate', ['id' => $data['post_id']]);
                                            }
                                            break;
                                        case 'post_pending':
                                        case 'post_published':
                                        case 'post_rejected':
                                        case 'post_revision':
                                        case 'post_liked':
                                        case 'post_milestone_10':
                                        case 'post_milestone_50':
                                        case 'post_milestone_100':
                                        case 'comment_received':
                                            if (!empty($data['post_id']) && \Illuminate\Support\Facades\Route::has('posts.show')) {
                                                // Route expects parameter name 'id' (see routes/web.php)
                                                $relatedUrl = route('posts.show', ['id' => $data['post_id']]);
                                            }
                                            break;
                                        case 'user_followed':
                                            if (!empty($data['follower_id']) && \Illuminate\Support\Facades\Route::has('profile.show')) {
                                                // Route expects parameter name 'userId'
                                                $relatedUrl = route('profile.show', ['userId' => $data['follower_id']]);
                                            }
                                            break;
                                    }
                                @endphp
                                <div class="w-10 h-10 rounded-full {{ $iconColor }} flex items-center justify-center text-lg">
                                    {{ $icon }}
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900">
                                    {{ $notification['title'] ?? 'Notifikasi' }}
                                </p>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    {{ $notification['message'] ?? 'Tidak ada pesan' }}
                                </p>
                                @if(isset($notification['data']['revision_notes']) && $notification['data']['revision_notes'])
                                <div class="mt-2 p-2 bg-amber-50 rounded text-xs text-amber-900">
                                    <strong>Pesan Revisi:</strong> {{ $notification['data']['revision_notes'] }}
                                </div>
                                @endif
                                @if(isset($notification['data']['rejection_reason']) && $notification['data']['rejection_reason'])
                                <div class="mt-2 p-2 bg-red-50 rounded text-xs text-red-900">
                                    <strong>Alasan Ditolak:</strong> {{ $notification['data']['rejection_reason'] }}
                                </div>
                                @endif
                                <p class="text-xs text-gray-400 mt-2">
                                    {{ isset($notification['created_at']) ? \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() : '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if(!($notification['read_at'] ?? false))
                        <button type="button" onclick="markAsRead('{{ $notification['id'] }}')" class="mark-read-btn p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition cursor-pointer" title="Tandai sebagai sudah dibaca" aria-label="Tandai sudah dibaca">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                        @endif
                        <button type="button" onclick="deleteNotification('{{ $notification['id'] }}')" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Hapus notifikasi" aria-label="Hapus notifikasi">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>

                {{-- Link to related content (if available) --}}
                @if($relatedUrl)
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <a href="{{ $relatedUrl }}" class="text-sm font-medium text-blue-600 hover:underline">
                        @switch($type)
                            @case('user_registration')
                                Lihat Profil User →
                            @break
                            @case('post_needs_review')
                                Moderasi Karya →
                            @break
                            @case('post_revision')
                                Lihat Revisi & Edit →
                            @break
                            @default
                                Lihat Detail →
                        @endswitch
                    </a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Tidak Ada Notifikasi</h3>
            <p class="text-gray-600 mb-6">Anda sudah melihat semua notifikasi terbaru.</p>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Kembali ke Dashboard
            </a>
        </div>
    @endif
</div>

<script>
function markAsRead(notificationId) {
    const btn = event?.currentTarget;
    if (btn) btn.disabled = true;
    fetch(`/api/notifications/${notificationId}/mark-as-read`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (!data?.success) throw new Error(data?.message || 'Gagal menandai notifikasi.');
        const el = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (el) {
            el.classList.remove('bg-blue-50/30','border-blue-200');
            el.style.opacity = '1';
            const actionBtn = el.querySelector('.mark-read-btn');
            if (actionBtn) actionBtn.remove();
        }
        if (typeof fetchUnreadCount === 'function') fetchUnreadCount();
    })
    .catch(err => {
        console.error('Mark as read failed:', err);
        alert(err.message);
    })
    .finally(() => { if (btn) btn.disabled = false; });
}

function markAllAsRead() {
    const markAllBtn = document.getElementById('markAllBtn');
    if (markAllBtn) markAllBtn.disabled = true;
    fetch('/api/notifications/mark-all-as-read', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (!data?.success) throw new Error(data?.message || 'Gagal menandai semua notifikasi.');
        document.querySelectorAll('.notification-item').forEach(item => {
            item.classList.remove('bg-blue-50/30','border-blue-200');
            item.style.opacity = '1';
            const btn = item.querySelector('.mark-read-btn');
            if (btn) btn.remove();
        });
        if (markAllBtn) markAllBtn.classList.add('hidden');
        if (typeof fetchUnreadCount === 'function') fetchUnreadCount();
    })
    .catch(err => {
        console.error('Mark all as read failed:', err);
        alert(err.message);
    })
    .finally(() => { if (markAllBtn) markAllBtn.disabled = false; });
}

function deleteNotification(notificationId) {
    if (confirm('Yakin ingin menghapus notifikasi ini?')) {
        fetch(`/api/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const element = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (element) {
                    element.remove();
                }
                
                // Check if no notifications left
                if (document.querySelectorAll('.notification-item').length === 0) {
                    location.reload();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
}
</script>
@endsection
