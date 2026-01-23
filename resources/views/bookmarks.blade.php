@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Breadcrumb Navigation --}}
    <nav class="flex text-sm font-medium text-gray-500 mb-8 overflow-x-auto whitespace-nowrap">
        <a href="{{ route('home') }}" class="hover:text-blue-600 transition">Beranda</a>
        <span class="mx-3 text-gray-300">/</span>
        <span class="text-gray-900 truncate">Karya Favorit Saya</span>
    </nav>

    {{-- Header --}}
    <div class="mb-12">
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
            ❤️ Karya Favorit Saya
        </h1>
        <p class="text-xl text-gray-600">
            Daftar karya yang telah Anda sukai
        </p>
        
        {{-- Info --}}
        <p class="text-sm text-gray-500 mt-2">Bookmark kini langsung dibaca dari server (tanpa localStorage).</p>
    </div>

    {{-- Content --}}
    <div id="bookmarkContainer" class="space-y-8">
        {{-- Loading State --}}
        <div id="loadingState" class="text-center py-12">
            <div class="inline-block">
                <div class="animate-spin h-12 w-12 text-blue-600 border-4 border-gray-200 border-t-blue-600 rounded-full"></div>
            </div>
            <p class="mt-4 text-gray-600">Memuat karya favorit Anda...</p>
        </div>

        {{-- Empty State (hidden by default) --}}
        <div id="emptyState" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">Belum ada karya favorit</h3>
            <p class="text-gray-600 mt-2">Mulai dengan menyukai karya favorit Anda di halaman Beranda.</p>
            <a href="{{ route('home') }}" class="inline-block mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Jelajahi Karya
            </a>
        </div>

        {{-- Posts Grid --}}
        <div id="postsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 hidden">
            {{-- Posts will be injected here by JavaScript --}}
        </div>

        {{-- Error State --}}
        <div id="errorState" class="hidden text-center py-12">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900">Terjadi kesalahan</h3>
            <p id="errorMessage" class="text-gray-600 mt-2">Gagal memuat karya favorit Anda.</p>
            <button onclick="location.reload()" class="inline-block mt-6 px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                Coba Lagi
            </button>
        </div>
    </div>
</div>

<script>
const apiBaseEnv = @json(rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/'));
const apiToken = @json(Session::get('api_token', ''));
const apiBaseCandidates = Array.from(new Set([
    apiBaseEnv,
    typeof window !== 'undefined' ? window.globalApiBase : null,
    `${window.location.origin}/api`
].filter(Boolean)));

console.log('[BOOKMARKS] API Bases candidates:', apiBaseCandidates);
console.log('[BOOKMARKS] Token:', apiToken ? 'Present' : 'Missing');

// Try multiple API bases until one succeeds
async function fetchWithFallback(path, options = {}) {
    let lastError = null;
    for (const base of apiBaseCandidates) {
        const url = `${base}${path}`;
        console.log('[BOOKMARKS] Trying:', url);
        try {
            const res = await fetch(url, options);
            return { res, base };
        } catch (err) {
            console.warn('[BOOKMARKS] Failed on base', base, err);
            lastError = err;
        }
    }
    throw lastError || new Error('Tidak bisa menghubungi server');
}

// Load and display bookmarked posts
async function loadBookmarks() {
    try {
        const { res, base } = await fetchWithFallback('/posts/user/liked', {
            headers: {
                'Accept': 'application/json',
                ...(apiToken ? { 'Authorization': `Bearer ${apiToken}` } : {})
            }
        });

        console.log('[BOOKMARKS] Response status:', res.status, res.ok, 'base:', base);

        let data = null;
        try {
            data = await res.json();
        } catch (e) {
            console.warn('[BOOKMARKS] Failed to parse JSON payload');
        }

        if (!res.ok) {
            const msg = data?.message || `Gagal memuat: ${res.status}`;
            throw new Error(msg);
        }

        console.log('[BOOKMARKS] Response data:', data);

        const posts = Array.isArray(data?.data) ? data.data : [];
        console.log('[BOOKMARKS] Loaded posts:', posts.length);

        document.getElementById('loadingState').classList.add('hidden');

        if (posts.length === 0) {
            document.getElementById('emptyState').classList.remove('hidden');
            return;
        }

        // Display posts
        const grid = document.getElementById('postsGrid');
        const backendBaseUrl = base.replace(/\/api$/, '');
        
        grid.innerHTML = posts.map(post => {
            // Process image URL
            let imageUrl = 'https://via.placeholder.com/600x400?text=No+Thumbnail';
            if (post.attachments && post.attachments[0]) {
                const attachment = post.attachments[0];
                if (attachment.file_url) {
                    imageUrl = attachment.file_url.startsWith('http') 
                        ? attachment.file_url 
                        : `${backendBaseUrl}/${attachment.file_url.replace(/\\/g, '/')}`;
                }
            }
            
            return `
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition hover:scale-105 transform">
                <div class="relative h-48 bg-gray-100 overflow-hidden group">
                    <img src="${imageUrl}" 
                         alt="${post.title}" 
                         class="w-full h-full object-cover group-hover:scale-110 transition"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/600x400?text=No+Image';">
                    
                    <div class="absolute top-3 right-3">
                        <span class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
                            ${post.category || 'Karya'}
                        </span>
                    </div>
                </div>

                <div class="p-5">
                    <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2">
                        ${post.title}
                    </h3>

                    <div class="flex items-center gap-3 mb-4">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(post.author?.full_name || 'User')}&background=0D8ABC&color=fff&size=64" 
                             alt="${post.author?.full_name || 'User'}" 
                             class="w-8 h-8 rounded-full object-cover">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">
                                ${post.author?.full_name || 'User'}
                            </p>
                            <p class="text-xs text-gray-500">
                                ${new Date(post.created_at).toLocaleDateString('id-ID')}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 pt-4 border-t border-gray-100 text-sm text-gray-600">
                        <span class="flex items-center gap-1">
                            ❤️ <strong>${post.likeCount || 0}</strong> Sukai
                        </span>
                        <span class="flex items-center gap-1">
                            💬 <strong>${post.commentCount || 0}</strong> Komentar
                        </span>
                    </div>

                    <div class="mt-4 flex gap-2">
                        <a href="/karya/${post.id}" class="flex-1 text-center px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            Lihat Detail
                        </a>
                        <button onclick="removeBookmark('${post.id}', '${base}')" 
                                class="px-3 py-2 text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition"
                                title="Hapus dari favorit">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
        `;
        }).join('');

        document.getElementById('postsGrid').classList.remove('hidden');
    } catch (err) {
        console.error('[BOOKMARKS] Error:', err);
        document.getElementById('loadingState').classList.add('hidden');
        document.getElementById('errorState').classList.remove('hidden');
        document.getElementById('errorMessage').textContent = err.message || 'Gagal memuat karya favorit Anda.';
    }
}

// Remove post from bookmarks (toggle like via API)
async function removeBookmark(postId, baseOverride) {
    if (!confirm('Hapus dari favorit?')) return;

    const base = baseOverride || apiBaseCandidates[0];

    try {
        const url = `${base}/posts/${postId}/vote`;
        console.log('[BOOKMARKS] Removing bookmark via API:', url);

        const response = await fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(apiToken ? { 'Authorization': `Bearer ${apiToken}` } : {})
            },
            body: JSON.stringify({ vote_type: true })
        });

        if (!response.ok) {
            throw new Error(`Gagal menghapus: ${response.status}`);
        }

        const data = await response.json();
        console.log('[BOOKMARKS] Remove response:', data);

        location.reload();
    } catch (err) {
        console.error('[BOOKMARKS] Error removing bookmark', err);
        alert('Gagal menghapus dari favorit: ' + err.message);
    }
}

// Load bookmarks on page load
document.addEventListener('DOMContentLoaded', loadBookmarks);
</script>
@endsection
