<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base" content="{{ rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/') }}">
    <meta name="api-token" content="{{ Session::get('api_token', '') }}">
    <title>Karya Mahasiswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    
    {{-- Global Like Manager for bookmark functionality --}}
    <script>
        window.globalApiBase = @json(rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/'));
        window.globalApiToken = @json(Session::get('api_token', ''));
    </script>
    <script src="{{ asset('js/like-manager.js') }}"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">
    
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center font-bold text-xl text-brand-600">
                        KARYA.UMDP
                    </a>
                </div>
                @php
                    $role = strtolower(Session::get('user')['role'] ?? '');
                    $isAdminLike = in_array($role, ['admin', 'superadmin', 'verifikator', 'kaprodi', 'kemahasiswaan', 'dosen']);
                @endphp

                <div class="flex items-center space-x-4">
                    <form action="{{ route('home') }}" method="GET" class="hidden md:block">
                        <input type="text" name="search" placeholder="Cari karya..." class="border rounded-full px-4 py-1 text-sm bg-gray-100 focus:bg-white focus:ring-2 focus:ring-brand-500 outline-none">
                    </form>

                    @if(Session::has('api_token'))
                        <a href="{{ route('home') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm hidden lg:block">
                            Explore
                        </a>
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-gray-900 font-medium text-sm hidden lg:block">
                            Dashboard
                        </a>
                        <a href="{{ route('posts.create') }}" class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-700 transition">
                            + Upload
                        </a>
                        
                        <div x-data="{ open: false, unreadCount: 0 }" @notification-opened.window="open = true; loadNotifications()" class="relative">
                            <button @click="open = !open; if(open) { fetchUnreadCount(); loadNotifications(); }" class="relative p-2 text-gray-600 hover:text-gray-900 transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <span x-show="unreadCount > 0" class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full" x-text="unreadCount"></span>
                            </button>
                            
                            <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl py-2 ring-1 ring-black/5 z-50 max-h-96 overflow-y-auto" style="display: none;">
                                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                    <h3 class="font-semibold text-gray-900 text-sm">Notifikasi</h3>
                                    <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline font-medium">Lihat Semua</a>
                                </div>
                                <div id="notification-list" class="divide-y divide-gray-100">
                                    <div class="px-4 py-3 text-center text-sm text-gray-500">
                                        Loading notifikasi...
                                    </div>
                                </div>
                                <div class="px-4 py-2 border-t border-gray-100 text-center">
                                    <a href="{{ route('notifications.index') }}" class="text-sm font-medium text-blue-600 hover:underline">
                                        Lihat Semua Notifikasi →
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center space-x-2 hover:opacity-80 transition">
                                @if(Session::has('profile_picture') && Session::get('profile_picture'))
                                    <img src="{{ Session::get('profile_picture') }}" class="h-8 w-8 rounded-full border-2 border-gray-200 object-cover" alt="Profile">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ Session::get('user')['username'] }}&background=random" class="h-8 w-8 rounded-full border-2 border-gray-200" alt="Avatar">
                                @endif
                            </button>
                            <div x-show="open" @click.outside="open = false" class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-xl py-2 ring-1 ring-black/5 z-50" style="display: none;">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="font-semibold text-gray-900 text-sm">{{ Session::get('user')['full_name'] ?? 'User' }}</p>
                                    <p class="text-xs text-gray-500">{{ '@' . (Session::get('user')['username'] ?? 'user') }}</p>
                                </div>
                                @if($isAdminLike)
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        📊 Dashboard Peran
                                    </a>
                                    <a href="{{ route('dashboard.feed') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        📰 Dashboard Biasa
                                    </a>
                                @endif

                                <a href="{{ route('profile.show', Session::get('user')['id']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    👤 Profil Saya
                                </a>
                                <a href="{{ route('posts.my-posts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    📂 Karya Saya
                                </a>
                                <a href="{{ route('bookmarks') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    ❤️ Karya Disukai
                                </a>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    ⚙️ Pengaturan
                                </a>
                                @if($isAdminLike)
                                    <div class="border-t border-gray-100 mt-2 pt-2">
                                        <a href="{{ route('admin.posts.index') }}" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 font-medium">
                                            🛡️ Admin Panel
                                        </a>
                                    </div>
                                @endif
                                <div class="border-t border-gray-100 mt-2 pt-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Logout</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900 font-medium">Masuk</a>
                        <a href="{{ route('register') }}" class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-700 transition">
                            Daftar
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <main class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>

    <script>
        // Fetch and display notifications in dropdown
        async function loadNotifications() {
            const listContainer = document.getElementById('notification-list');
            if (!listContainer) return;

            try {
                // Show loading
                listContainer.innerHTML = '<div class="px-4 py-3 text-center text-sm text-gray-500">Loading notifikasi...</div>';

                // Use meta api-base to avoid double /api when env already includes /api
                const apiBase = (document.querySelector('meta[name="api-base"]')?.content || '').replace(/\/$/, '');
                const apiToken = '{{ Session::get("api_token", "") }}';
                const url = `${apiBase}/notifications`;

                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${apiToken}`
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const result = await response.json();
                const notifications = result.data || [];

                if (notifications.length === 0) {
                    listContainer.innerHTML = '<div class="px-4 py-3 text-center text-sm text-gray-500">Tidak ada notifikasi</div>';
                    return;
                }

                // Display top 5 notifications
                const topNotifications = notifications.slice(0, 5);
                listContainer.innerHTML = topNotifications.map(notif => {
                    const isUnread = !notif.read_at;
                    const type = notif.type || 'info';
                    
                    // Get icon based on type
                    const icons = {
                        'user_registration': '👤',
                        'post_needs_review': '📝',
                        'post_pending': '⏳',
                        'post_published': '✅',
                        'post_rejected': '❌',
                        'post_revision': '↺',
                        'post_liked': '❤️',
                        'post_milestone_10': '🎉',
                        'post_milestone_50': '🌟',
                        'post_milestone_100': '🏆',
                        'comment_received': '💬',
                        'user_followed': '👥'
                    };
                    const icon = icons[type] || 'ⓘ';
                    
                    const bgClass = isUnread ? 'bg-blue-50/50' : 'bg-white';
                    const title = notif.title || 'Notifikasi';
                    const message = (notif.message || '').substring(0, 60) + (notif.message?.length > 60 ? '...' : '');
                    const timeAgo = notif.created_at ? new Date(notif.created_at).toLocaleDateString('id-ID') : '';
                    
                    return `
                        <a href="{{ route('notifications.index') }}" class="block px-4 py-3 hover:bg-gray-50 transition ${bgClass}">
                            <div class="flex gap-3">
                                <div class="text-lg">${icon}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">${title}</p>
                                    <p class="text-xs text-gray-600 mt-1">${message}</p>
                                    <p class="text-xs text-gray-400 mt-1">${timeAgo}</p>
                                </div>
                            </div>
                        </a>
                    `;
                }).join('');

            } catch (error) {
                console.error('Error loading notifications:', error);
                listContainer.innerHTML = '<div class="px-4 py-3 text-center text-sm text-red-500">Gagal memuat notifikasi</div>';
            }
        }

        // Fetch unread notification count
        async function fetchUnreadCount() {
            try {
                const response = await fetch('{{ route("notifications.unread-count") }}', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    console.warn('Unread count request failed:', response.status);
                    return;
                }

                let data;
                try {
                    data = await response.json();
                } catch (parseErr) {
                    console.warn('Unread count response was not JSON');
                    return;
                }

                if (!data || !data.success) return;

                const count = data.data ? data.data.unread_count : (data.unread_count || 0);
                
                // Update Alpine state (if available)
                const button = document.querySelector('[x-data*="unreadCount"]');
                if (button && window.Alpine) {
                    try {
                        const alpineData = Alpine.$data(button);
                        if (alpineData) {
                            alpineData.unreadCount = count;
                        }
                    } catch(e) {}
                }
                
                // Also update the badge number directly
                const badge = document.querySelector('[x-text="unreadCount"]');
                if (badge) {
                    badge.textContent = count;
                }
            } catch (error) {
                console.error('Error fetching unread count:', error);
            }
        }

        // Check notifications on page load
        if (document.querySelector('[x-data*="unreadCount"]')) {
            fetchUnreadCount();
            // Refresh every 30 seconds
            setInterval(fetchUnreadCount, 30000);
        }
    </script>

</body>
</html>