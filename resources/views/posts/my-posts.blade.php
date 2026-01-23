@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Karya Saya</h1>
            <p class="text-gray-600">Kelola dan pantau status verifikasi karya Anda</p>
        </div>
        <a href="{{ route('posts.create') }}" class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
            <span class="mr-2 text-xl">+</span> Upload Karya Baru
        </a>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 rounded-lg">
        <p class="text-green-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
        <p class="text-red-700 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    @if(isset($posts) && count($posts) > 0)
        {{-- Tabs untuk filter --}}
        <div class="flex space-x-4 mb-6 border-b border-gray-200">
            <button onclick="filterPosts('all')" id="tab-all" class="px-4 py-2 font-semibold text-blue-600 border-b-2 border-blue-600 transition">
                Semua Karya ({{ count($posts) }})
            </button>
            <button onclick="filterPosts('verified')" id="tab-verified" class="px-4 py-2 font-semibold text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300 transition">
                Terverifikasi ({{ count(array_filter($posts, fn($p) => $p['is_published'] ?? false)) }})
            </button>
            <button onclick="filterPosts('pending')" id="tab-pending" class="px-4 py-2 font-semibold text-gray-600 hover:text-gray-900 border-b-2 border-transparent hover:border-gray-300 transition">
                Menunggu ({{ count(array_filter($posts, fn($p) => !($p['is_published'] ?? true))) }})
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="posts-grid">
            @foreach($posts as $post)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition post-item" 
                 data-published="{{ $post['is_published'] ? 'true' : 'false' }}">
                {{-- Thumbnail --}}
                <div class="relative h-48 bg-gray-200 overflow-hidden group">
                    @if(!empty($post['attachments']) && is_array($post['attachments']) && count($post['attachments']) > 0)
                        @php
                            $firstFile = $post['attachments'][0];
                        @endphp
                        @if(is_array($firstFile) && str_contains($firstFile['mime'] ?? '', 'image'))
                            @php
                                // Ambil Path dari Database dan bersihkan slash
                                $cleanPath = str_replace('\\', '/', $firstFile['file_url']);
                                
                                // Cek apakah path sudah berupa URL lengkap (http...)?
                                if (str_starts_with($cleanPath, 'http')) {
                                    $imageUrl = $cleanPath;
                                } else {
                                    // Jika path relatif (public/uploads/...), susun URL-nya
                                    $backendUrl = str_replace('/api', '', env('BACKEND_API_URL'));
                                    $backendUrl = rtrim($backendUrl, '/'); // Hapus slash di akhir
                                    $cleanPath = ltrim($cleanPath, '/'); // Hapus slash di awal
                                    $imageUrl = $backendUrl . '/' . $cleanPath;
                                }
                            @endphp
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $post['title'] }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 loading="lazy"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gray-100 text-gray-400 text-sm p-4\'><span>Gagal Muat</span></div>';">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-blue-50 text-blue-400">
                                <span class="text-5xl">📄</span>
                            </div>
                        @endif
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gray-100 text-gray-300">
                            <span class="text-5xl">🖼️</span>
                        </div>
                    @endif
                    {{-- Status Badge --}}
                    <div class="absolute top-3 right-3 flex flex-col gap-2 items-end">
                        @if($post['is_published'] ?? false)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Terverifikasi
                            </span>
                        @elseif(isset($post['rejected_at']) && $post['rejected_at'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                Ditolak
                            </span>
                        @elseif(isset($post['revision_requested_at']) && $post['revision_requested_at'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" clip-rule="evenodd"/></svg>
                                Revisi Diminta
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                Menunggu Verifikasi
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-4">
                    <h3 class="font-bold text-lg text-gray-900 mb-2 line-clamp-2 hover:text-blue-600 transition">
                        <a href="{{ route('posts.show', $post['id']) }}">{{ $post['title'] }}</a>
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                        {{ $post['caption'] ?? 'Tidak ada deskripsi' }}
                    </p>

                    <div class="flex items-center justify-between mb-4 text-xs text-gray-500">
                        <span class="inline-block px-2 py-1 bg-gray-100 text-gray-600 rounded font-medium">
                            {{ $post['category'] ?? 'Uncategorized' }}
                        </span>
                        <span>{{ isset($post['created_at']) ? \Carbon\Carbon::parse($post['created_at'])->format('d M Y') : '-' }}</span>
                    </div>

                    {{-- Revision Notice --}}
                    @if(isset($post['revision_requested_at']) && $post['revision_requested_at'])
                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-xs font-bold text-yellow-800 mb-1">⚠️ Permintaan Revisi</p>
                        <p class="text-xs text-yellow-700">{{ $post['revision_comment'] ?? 'Silakan periksa komentar dari admin' }}</p>
                    </div>
                    @endif

                    {{-- Rejection Notice --}}
                    @if(isset($post['rejected_at']) && $post['rejected_at'])
                    <div class="mb-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-xs font-bold text-red-800 mb-1">❌ Karya Ditolak</p>
                        <p class="text-xs text-red-700">{{ $post['rejection_reason'] ?? 'Silakan hubungi admin untuk informasi lebih lanjut' }}</p>
                    </div>
                    @endif

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                        <a href="{{ route('posts.show', $post['id']) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            Lihat
                        </a>
                        
                        @if(!($post['is_published'] ?? false))
                            {{-- Edit dan Hapus hanya untuk karya yang belum terverifikasi --}}
                            <a href="{{ route('posts.edit', $post['id']) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                Edit
                            </a>
                            <form action="{{ route('posts.destroy', $post['id']) }}" method="POST" class="flex-1" onsubmit="return confirm('Yakin ingin menghapus karya ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">
                                    Hapus
                                </button>
                            </form>
                        @else
                            {{-- Info untuk karya yang sudah terverifikasi --}}
                            <div class="flex-1 text-center px-3 py-2 text-xs font-medium text-green-600 bg-green-50 rounded-lg">
                                <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Sudah Dipublikasikan
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        {{-- Empty State --}}
        <div class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4v2m0 1v1M8.5 5.5h7a2 2 0 012 2v7a2 2 0 01-2 2h-7a2 2 0 01-2-2v-7a2 2 0 012-2zm3-1v1m0 0h1m-1 0h-1"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Karya</h3>
            <p class="text-gray-600 max-w-sm mx-auto mb-6">
                Anda belum mengunggah karya apapun. Mulai bagikan karya kreatif Anda sekarang!
            </p>
            <a href="{{ route('posts.create') }}" class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                <span class="mr-2 text-xl">+</span> Upload Karya Pertama
            </a>
        </div>
    @endif

<script>
function filterPosts(filter) {
    const allPosts = document.querySelectorAll('.post-item');
    const tabs = ['all', 'verified', 'pending'];
    
    // Update tab styling
    tabs.forEach(tab => {
        const tabElement = document.getElementById(`tab-${tab}`);
        if (tab === filter) {
            tabElement.classList.remove('text-gray-600', 'border-transparent');
            tabElement.classList.add('text-blue-600', 'border-blue-600');
        } else {
            tabElement.classList.remove('text-blue-600', 'border-blue-600');
            tabElement.classList.add('text-gray-600', 'border-transparent');
        }
    });
    
    // Filter posts
    allPosts.forEach(post => {
        const isPublished = post.dataset.published === 'true';
        
        if (filter === 'all') {
            post.style.display = 'block';
        } else if (filter === 'verified' && isPublished) {
            post.style.display = 'block';
        } else if (filter === 'pending' && !isPublished) {
            post.style.display = 'block';
        } else {
            post.style.display = 'none';
        }
    });
    
    // Check if no posts visible
    const visiblePosts = Array.from(allPosts).filter(p => p.style.display !== 'none');
    const grid = document.getElementById('posts-grid');
    const existingEmpty = document.getElementById('empty-filter-message');
    
    if (visiblePosts.length === 0 && !existingEmpty) {
        const emptyMessage = document.createElement('div');
        emptyMessage.id = 'empty-filter-message';
        emptyMessage.className = 'col-span-full text-center py-12 bg-gray-50 rounded-xl border border-dashed border-gray-200';
        emptyMessage.innerHTML = `
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                <span class="text-3xl">📭</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Tidak ada karya ${filter === 'verified' ? 'terverifikasi' : 'menunggu verifikasi'}</h3>
            <p class="text-gray-600 max-w-sm mx-auto">
                ${filter === 'verified' ? 'Belum ada karya yang diverifikasi oleh admin.' : 'Semua karya Anda sudah terverifikasi!'}
            </p>
        `;
        grid.appendChild(emptyMessage);
    } else if (existingEmpty && visiblePosts.length > 0) {
        existingEmpty.remove();
    }
}

// Initialize - show all posts on page load
document.addEventListener('DOMContentLoaded', function() {
    filterPosts('all');
});
</script>
</div>
@endsection