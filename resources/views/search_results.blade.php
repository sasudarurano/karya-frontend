@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Header Pencarian --}}
    <div class="mb-10">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900">
            Hasil Pencarian untuk: <span class="text-blue-600">"{{ request('search') ?: request('category') }}"</span>
        </h2>
        <p class="text-gray-500 mt-2">Ditemukan {{ count($searchResults) }} karya yang relevan.</p>
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('home') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-semibold mt-4">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Kembali ke Beranda
        </a>
    </div>

    {{-- Error Message jika API Gagal --}}
    @if(isset($apiError) && !empty($apiError))
    <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error saat mencari karya</h3>
                <p class="mt-1 text-sm text-red-700">{{ $apiError }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Hasil Pencarian Pengguna --}}
    @if(isset($users) && count($users) > 0)
    <div class="mb-10">
        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <svg class="w-6 h-6 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Pengguna yang Relevan
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($users as $user)
            <a href="{{ route('profile.show', $user['id']) }}" class="flex items-center p-4 bg-white rounded-2xl shadow-sm hover:shadow-md border border-gray-100 transition duration-300 transform hover:-translate-y-1">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user['full_name']) }}&background=0D8ABC&color=fff&size=64&bold=true" class="w-12 h-12 rounded-full mr-4 shadow-sm" alt="{{ $user['full_name'] }}">
                <div class="overflow-hidden">
                    <p class="font-bold text-gray-900 truncate">{{ $user['full_name'] }}</p>
                    <p class="text-sm text-blue-500 font-medium truncate">{{ '@' . $user['username'] }}</p>
                </div>
                <div class="ml-auto pl-2">
                    <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(count($searchResults) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($searchResults as $post)
                <div class="bg-white rounded-3xl shadow-md hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden group flex flex-col h-full relative">
                    {{-- Thumbnail Karya --}}
                    <div class="relative h-56 overflow-hidden bg-gray-200">
                        @php
                            // Mengambil gambar pertama dari attachments atau placeholder jika kosong
                            $attachment = !empty($post['attachments']) ? $post['attachments'][0] : null;
                            $imageUrl = 'https://via.placeholder.com/600x400?text=No+Thumbnail';
                            
                            if ($attachment && str_contains($attachment['mime'], 'image')) {
                                $cleanPath = str_replace('\\', '/', $attachment['file_url']);
                                $backendUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
                                
                                // Logic penentuan URL gambar (Lokal vs Remote)
                                if (str_starts_with($cleanPath, 'http')) {
                                    $imageUrl = $cleanPath;
                                } else {
                                    $imageUrl = $backendUrl . '/' . ltrim($cleanPath, '/');
                                }
                            }
                        @endphp
                        <img src="{{ $imageUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="{{ $post['title'] }}">
                        
                        {{-- Badge Kategori --}}
                        <div class="absolute top-4 left-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur text-blue-600 uppercase tracking-wider shadow-sm">
                                {{ $post['category'] }}
                            </span>
                        </div>
                    </div>

                    {{-- Konten Kartu --}}
                    <div class="p-6 flex flex-col flex-grow">
                        <a href="{{ route('posts.show', $post['id']) }}" class="block mb-2 before:absolute before:inset-0 before:z-10">
                            <h3 class="text-lg font-bold text-gray-900 line-clamp-2 hover:text-blue-600 transition h-14 relative z-0">
                                {{ $post['title'] }}
                            </h3>
                        </a>
                        
                        {{-- Kreator (Mahasiswa) --}}
                        <div class="flex items-center space-x-3 mb-6">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($post['author']['full_name'] ?? 'User') }}&background=0D8ABC&color=fff&size=64" class="w-8 h-8 rounded-full border border-gray-100 shadow-sm" alt="Author">
                            <div class="overflow-hidden">
                                <p class="text-xs text-gray-900 font-bold truncate">{{ $post['author']['full_name'] ?? 'Mahasiswa' }}</p>
                                <p class="text-[10px] text-gray-500">{{ '@' . ($post['author']['username'] ?? 'user') }}</p>
                            </div>
                        </div>

                        {{-- Footer Kartu: Info Dosen & Likes --}}
                        <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between">
                            {{-- Info Dosen Pembimbing --}}
                            @if(!empty($post['supervisor']))
                                <div class="flex items-center text-indigo-600" title="Dosen Pembimbing: {{ $post['supervisor']['full_name'] }}">
                                    <div class="p-1 bg-indigo-50 rounded-md mr-2">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-[10px] font-extrabold truncate max-w-[120px]">
                                        {{ $post['supervisor']['full_name'] }}
                                    </span>
                                </div>
                            @else
                                <div class="text-[10px] text-gray-300 italic">Tanpa Pembimbing</div>
                            @endif

                            {{-- Statistik Likes dengan Button --}}
                            @auth
                                <button onclick="toggleLikeSearch(@json($post['id']), this)" 
                                    data-post-id="{{ $post['id'] }}"
                                    data-post-like-button="{{ $post['id'] }}"
                                    data-liked="{{ ($post['isLiked'] ?? false) ? 'true' : 'false' }}" 
                                    class="flex items-center transition relative z-20 cursor-pointer {{ ($post['isLiked'] ?? false) ? 'text-red-500' : 'text-gray-400 hover:text-red-500' }}">
                                <svg class="w-3.5 h-3.5 mr-1 transition" fill="{{ ($post['isLiked'] ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-[10px] font-bold like-count-{{ $post['id'] }}">{{ $post['likeCount'] ?? 0 }}</span>
                            </button>
                            @else
                            <a href="{{ route('login') }}" class="flex items-center text-gray-400 hover:text-red-500 transition relative z-20">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span class="text-[10px] font-bold">{{ $post['likeCount'] ?? 0 }}</span>
                            </a>
                            @endauth
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        {{-- Tampilan jika tidak ada hasil --}}
        <div class="text-center py-24 bg-gray-50 rounded-[2.5rem] border-2 border-dashed border-gray-200">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-3xl shadow-sm mb-6">
                <span class="text-5xl">🔍</span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Karya tidak ditemukan</h3>
            <p class="text-gray-500 max-w-sm mx-auto mb-8">
                Coba gunakan kata kunci yang lebih umum atau periksa kembali filter kategori Anda.
            </p>
            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-bold rounded-2xl hover:bg-blue-700 hover:shadow-lg transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    @endif
</div>

<script>
// API configuration (used by like-manager.js)
window.globalApiBase = @json(rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/'));
window.globalApiToken = @json(Session::get('api_token', ''));

// Note: toggleLikeSearch is now handled by like-manager.js
</script>
@endsection