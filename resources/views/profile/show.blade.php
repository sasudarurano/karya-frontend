@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    {{-- ================= HEADER SECTION (TETAP) ================= --}}
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="px-8 py-12">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                {{-- Profile Picture --}}
                <div class="flex-shrink-0">
                    @php
                        $profilePicture = null;
                        
                        // Logic Profile Picture tetap dipertahankan
                        if (isset($profile['profile_picture']['file_url'])) {
                            $cleanPath = str_replace('\\', '/', $profile['profile_picture']['file_url']);
                            $profilePicture = str_starts_with($cleanPath, 'http') 
                                ? $cleanPath 
                                : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($cleanPath, '/');
                        } elseif (isset($profile['profile_picture']) && is_string($profile['profile_picture'])) {
                            $cleanPath = str_replace('\\', '/', $profile['profile_picture']);
                            $profilePicture = str_starts_with($cleanPath, 'http') 
                                ? $cleanPath 
                                : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($cleanPath, '/');
                        } elseif (isset($profile['pp_id']['file_url'])) {
                            $cleanPath = str_replace('\\', '/', $profile['pp_id']['file_url']);
                            $profilePicture = str_starts_with($cleanPath, 'http') 
                                ? $cleanPath 
                                : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($cleanPath, '/');
                        }
                    @endphp
                    @if($profilePicture)
                        <img src="{{ $profilePicture }}" 
                             alt="Profile Picture" 
                             class="w-32 h-32 rounded-full object-cover shadow-lg border-4 border-white"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center text-6xl font-bold text-blue-600 shadow-lg" style="display:none;">
                            {{ strtoupper(substr($profile['username'] ?? $profile['full_name'] ?? 'U', 0, 1)) }}
                        </div>
                    @else
                        <div class="w-32 h-32 bg-white rounded-full flex items-center justify-center text-6xl font-bold text-blue-600 shadow-lg">
                            {{ strtoupper(substr($profile['username'] ?? $profile['full_name'] ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>

                {{-- Profile Info --}}
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold text-white mb-2">
                        {{ $profile['full_name'] ?? $profile['username'] ?? 'User' }}
                    </h1>
                    <p class="text-blue-100 text-lg mb-4">@<span>{{ $profile['username'] ?? 'user' }}</span></p>
                    
                    @if(!empty($profile['bio']))
                    <p class="text-white/90 max-w-2xl mb-4">{{ $profile['bio'] }}</p>
                    @endif

                    {{-- Stats --}}
                    <div class="flex items-center justify-center md:justify-start gap-8 text-white">
                        <div>
                            <span class="text-2xl font-bold">{{ count($posts) }}</span>
                            <span class="text-blue-100 text-sm ml-1">Karya</span>
                        </div>
                        <div>
                            <a href="{{ route('users.followers', $userId) }}" class="hover:underline">
                                <span class="text-2xl font-bold">{{ $followersCount }}</span>
                                <span class="text-blue-100 text-sm ml-1">Followers</span>
                            </a>
                        </div>
                        <div>
                            <a href="{{ route('users.following', $userId) }}" class="hover:underline">
                                <span class="text-2xl font-bold">{{ $followingCount }}</span>
                                <span class="text-blue-100 text-sm ml-1">Following</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3">
                    @if($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" 
                           class="px-6 py-2 bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition font-semibold shadow-sm">
                            ✏️ Edit Profil
                        </a>
                    @else
                        @php $following = ($profile['is_followed'] ?? false) ? true : false; @endphp
                        <button id="followButton" 
                                onclick="window.profileFollowHelper?.toggle()" 
                                data-user-id="{{ $userId }}"
                                data-is-following="{{ $following ? 'true' : 'false' }}"
                                class="px-6 py-2 rounded-lg transition font-semibold shadow-sm"
                                style="background-color: {{ $following ? '#dcfce7' : '#e8f0ff' }}; color: {{ $following ? '#15803d' : '#0066cc' }};">
                            {{ $following ? '✓ Mengikuti' : '+ Ikuti' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ================= MAIN LAYOUT GRID ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        {{-- ================= KIRI: DETAIL PROFIL ================= --}}
        <div class="lg:col-span-3 order-1 sticky top-24">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 text-lg mb-6 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </span>
                    Detail Profil
                </h3>
                
                <div class="space-y-6">
                    @if(!empty($profile['email']))
                    <div class="group">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Email</p>
                        <p class="text-gray-900 font-medium break-all">{{ $profile['email'] }}</p>
                    </div>
                    @endif

                    @if(!empty($profile['nid']))
                    <div class="group">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">NIM/NIP</p>
                        <p class="text-gray-900 font-medium">{{ $profile['nid'] }}</p>
                    </div>
                    @endif

                    @if(!empty($profile['phone']) || !empty($profile['phone_number']))
                    <div class="group">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Telepon</p>
                        <p class="text-gray-900 font-medium">{{ $profile['phone'] ?? $profile['phone_number'] }}</p>
                    </div>
                    @endif

                    @if(!empty($profile['program_studi']) || !empty($profile['program_studi_id']))
                    <div class="group">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Program Studi</p>
                        <p class="text-gray-900 font-medium">
                            @php
                                $prodiName = 'N/A';
                                if (isset($profile['program_studi']) && is_array($profile['program_studi'])) {
                                    $prodiName = $profile['program_studi']['nama_program_studi'] 
                                            ?? $profile['program_studi']['name'] 
                                            ?? $profile['program_studi']['program_name']
                                            ?? 'N/A';
                                }
                            @endphp
                            {{ $prodiName }}
                        </p>
                    </div>
                    @endif

                    <div class="group">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Role</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                            {{ $profile['role'] ?? 'Mahasiswa' }}
                        </span>
                    </div>

                    <div class="group">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Bergabung Sejak</p>
                        <p class="text-gray-900 font-medium">
                            {{ isset($profile['created_at']) ? \Carbon\Carbon::parse($profile['created_at'])->format('d F Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TENGAH: KARYA DIPUBLIKASIKAN ================= --}}
        {{-- Jika OwnProfile: col-span-6, Jika Visitor: col-span-9 --}}
        <div class="{{ $isOwnProfile ? 'lg:col-span-6' : 'lg:col-span-9' }} order-2">
            <div class="flex items-center justify-between mb-6">
                <h3 class="font-bold text-gray-900 text-xl flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-600 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </span>
                    Karya Dipublikasikan 
                    <span class="ml-2 text-sm font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">{{ count($posts) }}</span>
                </h3>
            </div>
            
            @if(count($posts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($posts as $post)
                <a href="{{ route('posts.show', $post['id']) }}" class="group h-full">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition duration-300 h-full flex flex-col">
                        {{-- Thumbnail --}}
                        <div class="relative h-48 bg-gray-200 overflow-hidden">
                            @if(!empty($post['attachments']) && count($post['attachments']) > 0)
                                @php
                                    $imageFile = collect($post['attachments'])->first(fn($a) => str_contains($a['mime'] ?? '', 'image'));
                                    if ($imageFile) {
                                        $cleanPath = str_replace('\\', '/', $imageFile['file_url']);
                                        $imageUrl = str_starts_with($cleanPath, 'http') ? $cleanPath : rtrim(env('BACKEND_API_URL'), '/') . '/' . ltrim($cleanPath, '/');
                                    }
                                @endphp
                                @if(isset($imageUrl))
                                    <img src="{{ $imageUrl }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" alt="{{ $post['title'] }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                        </div>
                        
                        {{-- Content --}}
                        <div class="p-4 flex flex-col flex-1">
                            <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition">
                                {{ $post['title'] }}
                            </h4>
                            <div class="mt-auto">
                                <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-md mb-2">
                                    {{ $post['category'] ?? 'Umum' }}
                                </span>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($post['created_at'])->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <h3 class="text-gray-900 font-medium mb-1">Belum ada karya</h3>
                <p class="text-gray-500 text-sm">Pengguna ini belum mempublikasikan karya apapun.</p>
            </div>
            @endif
        </div>

        {{-- ================= KANAN: GANTI PASSWORD (Hanya Owner) ================= --}}
        @if($isOwnProfile)
        <div class="lg:col-span-3 order-3 sticky top-24">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 text-lg mb-6 flex items-center gap-2">
                    <span class="bg-red-100 text-red-600 p-1.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </span>
                    Keamanan
                </h3>
                
                <form id="changePasswordForm" class="space-y-4">
                    <div id="passwordMessages"></div>

                    {{-- Old Password --}}
                    <div>
                        <label for="old_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Password Lama</label>
                        <input type="password" id="old_password" name="old_password" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="••••••••">
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="new_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Password Baru</label>
                        <input type="password" id="new_password" name="new_password" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Min. 8 karakter">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Konfirmasi</label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                               placeholder="Ulangi password">
                    </div>

                    {{-- Button --}}
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 active:scale-95 transition font-semibold text-sm shadow-md">
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="mt-6 bg-blue-50 rounded-xl p-4 border border-blue-100">
                <h4 class="font-semibold text-blue-800 text-sm mb-2">💡 Tips Keamanan</h4>
                <p class="text-xs text-blue-700 leading-relaxed">
                    Gunakan password yang kuat dengan kombinasi huruf besar, huruf kecil, angka, dan simbol untuk melindungi akun Anda.
                </p>
            </div>
        </div>
        @endif

    </div>
</div>

{{-- Modal Component --}}
<div id="notificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeModal()">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
        <div id="modalHeader" class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900"></h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div id="modalBody" class="px-6 py-6">
            <div id="modalIcon" class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full"></div>
            <p id="modalMessage" class="text-gray-700 text-center mb-6"></p>
            <div id="modalCopySection" class="hidden">
                <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Password Baru:</p>
                            <p id="modalPasswordText" class="text-2xl font-mono font-bold text-gray-900"></p>
                        </div>
                        <button onclick="copyPasswordToClipboard()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                            📋 Copy
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 text-center">Berikan password ini kepada user untuk login</p>
            </div>
        </div>
        <div id="modalFooter" class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="closeModal()" id="modalCloseBtn" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                Tutup
            </button>
        </div>
    </div>
</div>

@if(!$isOwnProfile)
<script>
const userId = @json($userId);
const profileApiBase = @json(rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/'));
const profileApiToken = @json(Session::get('api_token', ''));
const profileLoginUrl = @json(route('login'));
const currentUserId = @json(Session::get('user')['id'] ?? null);

// Initialize global follow helpers if not exists
if (!window.karyaFollowHelpers) {
    window.karyaFollowHelpers = {
        storageKey: 'karya-follow-state',

        // Use object map { userId: true } to match post page
        getAllFollows() {
            try {
                const raw = localStorage.getItem(this.storageKey);
                return raw ? JSON.parse(raw) : {};
            } catch (e) {
                console.warn('[FOLLOW] Failed to read local follows', e);
                return {};
            }
        },

        persistFollows(map) {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(map));
            } catch (e) {
                console.warn('[FOLLOW] Failed to persist follows', e);
            }
        },

        updateFollowState(userId, isFollowing) {
            const saved = this.getAllFollows();
            if (isFollowing) {
                saved[String(userId)] = true;
            } else {
                delete saved[String(userId)];
            }
            this.persistFollows(saved);
        },

        isUserFollowed(userId) {
            const saved = this.getAllFollows();
            return !!saved[String(userId)];
        },

        syncFollowButton(authorId, isFollowing) {
            this.updateFollowState(authorId, isFollowing);

            const btn = document.getElementById('followButton');
            if (btn /* optional identity check removed to keep it simple */) {
                btn.dataset.isFollowing = isFollowing ? 'true' : 'false';

                if (isFollowing) {
                    btn.textContent = '✓ Mengikuti';
                    btn.style.backgroundColor = '#dcfce7';
                    btn.style.color = '#15803d';
                } else {
                    btn.textContent = '+ Ikuti';
                    btn.style.backgroundColor = '#e8f0ff';
                    btn.style.color = '#0066cc';
                }
            }
        }
    };
}

// Profile page follow manager
window.profileFollowHelper = {
    userId: userId,
    apiBase: profileApiBase,
    apiToken: profileApiToken,
    loginUrl: profileLoginUrl,
    isLoggedIn: !!profileApiToken,
    
    init() {
        // Authoritative state from DB (backend)
        this.refreshFollowStateFromServer().then(() => {
            this.syncButtonState(); // keep dataset/UI aligned after refresh
            this.setupStorageListener();
        });
    },
    
    syncButtonState() {
        const btn = document.getElementById('followButton');
        if (!btn) return;
        
        // Get follow state from server attribute (most authoritative for initial load)
        const serverFollowState = btn.dataset.isFollowing === 'true';
        
        // Use server-rendered dataset until the backend refresh arrives
        window.karyaFollowHelpers.syncFollowButton(this.userId, serverFollowState);
    },
    
    setupStorageListener() {
        window.addEventListener('storage', (e) => {
            if (e.key === window.karyaFollowHelpers.storageKey) {
                this.syncButtonState();
            }
        });
    },
    
    async toggle() {
        if (!this.isLoggedIn) {
            window.location.href = this.loginUrl;
            return;
        }
        
        const btn = document.getElementById('followButton');
        if (!btn) return;
        
        const isCurrentlyFollowing = btn.dataset.isFollowing === 'true';
        const method = isCurrentlyFollowing ? 'DELETE' : 'POST';
        
        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = '...';
        
        try {
            const res = await fetch(`${this.apiBase}/users/${this.userId}/follow`, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${this.apiToken}`
                },
                credentials: 'include'
            });
            
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            
            if (data.success) {
                const newFollowState = !isCurrentlyFollowing;
                window.karyaFollowHelpers.syncFollowButton(this.userId, newFollowState);
            } else {
                showModal('error', 'Gagal Follow', data.message || 'Gagal memproses follow');
                btn.textContent = originalText;
            }
        } catch (err) {
            console.error('[PROFILE FOLLOW] Error:', err);
            showModal('error', 'Error Koneksi', 'Terjadi kesalahan koneksi atau server.');
            btn.textContent = originalText;
        } finally {
            btn.disabled = false;
        }
    }
    ,
    async refreshFollowStateFromServer() {
        if (!this.isLoggedIn) return; // Without login, rely on server-render + localStorage
        try {
            const res = await fetch(`${this.apiBase}/users/${this.userId}/profile`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${this.apiToken}`
                },
                credentials: 'include'
            });
            if (!res.ok) return; // keep current state if error
            const body = await res.json();
            const data = body?.data ?? body;
            const userData = data?.user ?? data;
            const isFollowingServer = !!(userData?.is_followed);
            window.karyaFollowHelpers.syncFollowButton(this.userId, isFollowingServer);
            // Fallback check using followers list if server didn't provide is_followed
            if (!isFollowingServer && currentUserId) {
                await this.refreshFromFollowersList();
            }
        } catch (e) {
            console.warn('[PROFILE FOLLOW] Failed to refresh follow state from server', e);
            // Try fallback via followers list
            if (currentUserId) {
                await this.refreshFromFollowersList();
            }
        }
    },
    async refreshFromFollowersList() {
        try {
            const res = await fetch(`${this.apiBase}/users/${this.userId}/followers`, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const body = await res.json();
            const followers = body?.data ?? [];
            const isFollower = followers.some(f => String(f.owner_id) === String(currentUserId));
            window.karyaFollowHelpers.syncFollowButton(this.userId, isFollower);
        } catch (e) {
            console.warn('[PROFILE FOLLOW] Fallback followers list failed', e);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    if (window.profileFollowHelper) {
        window.profileFollowHelper.init();
    }
});
</script>
@endif

@if($isOwnProfile)
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Change Password Form Handler
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const oldPassword = document.getElementById('old_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const messagesDiv = document.getElementById('passwordMessages');
            
            // Clear previous messages
            messagesDiv.innerHTML = '';
            
            // Validation
            if (newPassword !== confirmPassword) {
                showModal('error', 'Validasi Gagal', 'Password baru dan konfirmasi password tidak cocok.');
                return;
            }
            
            if (newPassword.length < 8) {
                showModal('error', 'Validasi Gagal', 'Password minimal 8 karakter.');
                return;
            }
            
            if (oldPassword === newPassword) {
                showModal('error', 'Validasi Gagal', 'Password baru harus berbeda dari password lama.');
                return;
            }
            
            try {
                messagesDiv.innerHTML = '<div class="p-3 bg-blue-50 border-l-4 border-blue-600 rounded-lg mb-4"><p class="text-blue-700 text-xs font-medium">⏳ Memproses...</p></div>';
                
                // Get token dari Laravel Session
                const token = @json(Session::get('api_token'));
                const baseUrl = @json(rtrim(env('BACKEND_API_URL'), '/'));
                
                console.log('Attempting password change', {
                    endpoint: baseUrl + '/v1/users/change-password',
                    hasToken: !!token,
                    tokenLength: token ? token.length : 0
                });
                
                // Direct fetch call ke backend
                const response = await fetch(baseUrl + '/v1/users/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        old_password: oldPassword,
                        new_password: newPassword,
                        confirm_password: confirmPassword
                    })
                });
                
                const responseData = await response.json();
                
                console.log('Password change response:', {
                    status: response.status,
                    ok: response.ok,
                    body: responseData
                });
                
                if (response.ok) {
                    messagesDiv.innerHTML = '';
                    changePasswordForm.reset();
                    showModal('success', 'Berhasil!', 'Password berhasil diubah. Anda akan logout dalam 2 detik...');
                    
                    // Logout after 2 seconds menggunakan POST
                    setTimeout(() => {
                        // Create form untuk POST logout
                        const logoutForm = document.createElement('form');
                        logoutForm.method = 'POST';
                        logoutForm.action = '/logout';
                        
                        // Add CSRF token
                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = '{{ csrf_token() }}';
                        logoutForm.appendChild(csrfInput);
                        
                        // Submit form
                        document.body.appendChild(logoutForm);
                        logoutForm.submit();
                    }, 2000);
                } else {
                    const errorMsg = responseData?.message || responseData?.error || responseData?.data?.message || 'Gagal mengubah password';
                    console.error('Password change error:', responseData);
                    messagesDiv.innerHTML = '';
                    showModal('error', 'Gagal Ubah Password', errorMsg);
                }
            } catch (error) {
                console.error('Error changing password:', {
                    message: error.message,
                    stack: error.stack,
                    error: error
                });
                messagesDiv.innerHTML = '';
                showModal('error', 'Error Koneksi', error.message || 'Gagal terhubung ke server.');
            }
        });
    }
});

// Modal Helper Functions
function showModal(type, title, message, password = null) {
    const modal = document.getElementById('notificationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalCopySection = document.getElementById('modalCopySection');
    const modalPasswordText = document.getElementById('modalPasswordText');
    
    // Set icon based on type
    if (type === 'success') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    } else if (type === 'error') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
    } else if (type === 'info') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Show password copy section if provided
    if (password) {
        modalPasswordText.textContent = password;
        modalCopySection.classList.remove('hidden');
        window.currentPassword = password;
    } else {
        modalCopySection.classList.add('hidden');
        window.currentPassword = null;
    }
    
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
    window.currentPassword = null;
}

function copyPasswordToClipboard() {
    if (window.currentPassword && navigator.clipboard) {
        navigator.clipboard.writeText(window.currentPassword).then(() => {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Tersalin!';
            btn.classList.add('bg-green-600');
            btn.classList.remove('bg-blue-600');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-blue-600');
            }, 2000);
        });
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endif

@endsection