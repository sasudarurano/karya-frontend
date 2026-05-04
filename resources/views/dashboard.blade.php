@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-12 px-4 sm:px-6">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- LEFT SIDEBAR (Profile & Menu) --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Profile Card --}}
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden relative group">
                <div class="h-24 bg-gradient-to-r from-blue-600 to-indigo-600"></div>
                <div class="px-6 pb-6 text-center relative">
                    <div class="relative inline-block -mt-12 mb-3">
                        @if(isset($profilePicture) && $profilePicture)
                            <img src="{{ $profilePicture }}" 
                                 class="h-24 w-24 rounded-full border-4 border-white shadow-md object-cover transform group-hover:scale-105 transition duration-300"
                                 alt="Profile">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ Session::get('user')['username'] ?? 'User' }}&background=0D8ABC&color=fff&bold=true" 
                                 class="h-24 w-24 rounded-full border-4 border-white shadow-md transform group-hover:scale-105 transition duration-300">
                        @endif
                        @if(Session::get('user')['role'] === 'admin')
                            <span class="absolute bottom-0 right-0 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full border-2 border-white shadow-sm font-bold">ADMIN</span>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 truncate">{{ Session::get('user')['full_name'] ?? 'Mahasiswa' }}</h3>
                    <p class="text-xs text-gray-500 mb-5 uppercase tracking-wider font-semibold">{{ Session::get('user')['role'] ?? 'User' }}</p>
                    
                    <div class="grid grid-cols-2 gap-2 border-t border-gray-100 pt-4">
                        <a href="{{ route('posts.my-posts') }}" class="group/stat block p-2 rounded-xl hover:bg-gray-50 transition">
                            <span class="block font-extrabold text-xl text-gray-900 group-hover/stat:text-blue-600 transition">{{ session('userStats.postsCount', 0) }}</span>
                            <span class="text-xs text-gray-400 font-medium">Karya</span>
                        </a>
                        <a href="{{ route('users.following', Session::get('user')['id']) }}" class="group/stat block p-2 rounded-xl hover:bg-gray-50 transition">
                            <span class="block font-extrabold text-xl text-gray-900 group-hover/stat:text-blue-600 transition">{{ $followingCount ?? 0 }}</span>
                            <span class="text-xs text-gray-400 font-medium">Mengikuti</span>
                        </a>
                    </div>
                    
                    <a href="{{ route('profile.edit') }}" class="mt-4 block w-full py-2 text-xs font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 hover:text-blue-600 rounded-lg border border-gray-200 transition">
                        Edit Profil
                    </a>
                </div>
            </div>

            {{-- Quick Menu --}}
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-5">
                <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 px-2">Menu Cepat</h4>
                <nav class="space-y-1">
                    <a href="{{ route('posts.create') }}" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition group">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </span>
                        <span class="font-medium text-sm">Upload Karya</span>
                    </a>

                    <a href="{{ route('posts.my-posts') }}" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-xl transition group">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </span>
                        <span class="font-medium text-sm">Arsip Saya</span>
                    </a>

                    <a href="{{ route('bookmarks') }}" class="flex items-center space-x-3 px-3 py-2.5 text-gray-700 hover:bg-pink-50 hover:text-pink-600 rounded-xl transition group">
                        <span class="w-8 h-8 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center group-hover:bg-pink-600 group-hover:text-white transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </span>
                        <span class="font-medium text-sm">Disukai</span>
                    </a>

                    @if(Session::get('user')['role'] === 'admin')
                        <div class="pt-3 mt-2 border-t border-gray-100"></div>
                        <a href="{{ route('admin.posts.index') }}" class="flex items-center space-x-3 px-3 py-2.5 text-red-600 hover:bg-red-50 rounded-xl transition group">
                            <span class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </span>
                            <span class="font-medium text-sm">Moderasi Admin</span>
                        </a>
                    @elseif(in_array(Session::get('user')['role'], ['verifikator', 'dosen pembimbing', 'dosen_pembimbing', 'kaprodi']))
                        <div class="pt-3 mt-2 border-t border-gray-100"></div>
                        <a href="{{ route('admin.posts.index') }}" class="flex items-center space-x-3 px-3 py-2.5 text-amber-600 hover:bg-amber-50 rounded-xl transition group">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            </span>
                            <span class="font-medium text-sm">Panel Moderasi</span>
                        </a>
                    @endif
                </nav>
            </div>
        </div>

        {{-- MAIN FEED --}}
        <div class="lg:col-span-9">
            <div class="flex justify-between items-end mb-8">
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900 leading-tight">Feed Mengikuti</h2>
                    <p class="text-gray-500 text-sm mt-1">Karya terbaru dari kreator yang Anda ikuti.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-50 text-green-700 px-4 py-3 rounded-2xl mb-6 border border-green-200 flex items-center shadow-sm animate-fade-in-down">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(isset($posts) && count($posts) > 0)
                <div class="space-y-6">
                    @foreach($posts as $post)
                        {{-- Post Item --}}
                        <div class="bg-white rounded-3xl shadow-sm hover:shadow-xl border border-gray-100 transition-all duration-300 overflow-hidden group">
                            <div class="flex flex-col md:flex-row">
                                {{-- Thumbnail Section --}}
                                <a href="{{ route('posts.show', $post['id']) }}" class="md:w-5/12 relative overflow-hidden block h-64 md:h-auto">
                                    @php
                                        // Logic Image URL (sama seperti sebelumnya, disederhanakan)
                                        $imageUrl = null;
                                        if(!empty($post['attachments'])) {
                                            foreach($post['attachments'] as $att) {
                                                if(str_contains($att['mime'], 'image')) {
                                                    $cleanPath = str_replace('\\', '/', $att['file_url'] ?? '');
                                                    $backendUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
                                                    $imageUrl = str_starts_with($cleanPath, 'http') ? $cleanPath : $backendUrl . '/' . ltrim($cleanPath, '/');
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp

                                    @if($imageUrl)
                                        <div class="absolute inset-0 bg-cover bg-center blur-sm opacity-20 scale-110" style="background-image: url('{{ $imageUrl }}');"></div>
                                        <img src="{{ $imageUrl }}" 
                                             class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition duration-700 ease-in-out"
                                             alt="{{ $post['title'] }}">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                                    @else
                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">
                                            <div class="text-center">
                                                <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                <span class="text-sm font-medium">No Preview</span>
                                            </div>
                                        </div>
                                    @endif
                                </a>

                                {{-- Content Section --}}
                                <div class="p-6 md:w-7/12 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-4">
                                            <a href="{{ route('profile.show', $post['author']['id']) }}" class="flex items-center space-x-2 group/author">
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($post['author']['full_name']) }}&background=random" 
                                                     class="w-8 h-8 rounded-full shadow-sm group-hover/author:ring-2 ring-blue-500 transition">
                                                <div>
                                                    <p class="text-sm font-bold text-gray-900 group-hover/author:text-blue-600 transition">{{ $post['author']['full_name'] ?? 'Unknown' }}</p>
                                                    <p class="text-[10px] text-gray-400">{{ isset($post['created_at']) ? \Carbon\Carbon::parse($post['created_at'])->diffForHumans() : '-' }}</p>
                                                </div>
                                            </a>
                                            <span class="px-2.5 py-1 bg-gray-50 text-gray-600 text-[10px] uppercase font-bold tracking-wider rounded-md border border-gray-100">
                                                {{ $post['category'] }}
                                            </span>
                                        </div>
                                        
                                        <a href="{{ route('posts.show', $post['id']) }}" class="block group/title">
                                            <h3 class="text-xl font-extrabold text-gray-900 mb-2 leading-snug group-hover/title:text-blue-600 transition">
                                                {{ $post['title'] }}
                                            </h3>
                                            <p class="text-gray-500 text-sm line-clamp-2 leading-relaxed mb-4">
                                                {{ $post['caption'] ?? 'Tidak ada deskripsi.' }}
                                            </p>
                                        </a>
                                    </div>

                                    <div class="flex items-center justify-between border-t border-gray-50 pt-4 mt-2">
                                        <div class="flex space-x-4">
                                            {{-- Like Button --}}
                                            @auth
                                            <button onclick="toggleLikeDashboard({{ $post['id'] }}, this)" 
                                                    data-post-id="{{ $post['id'] }}"
                                                    data-post-like-button="{{ $post['id'] }}"
                                                    data-liked="{{ $post['isLiked'] ?? false ? 'true' : 'false' }}" 
                                                    class="group/btn flex items-center space-x-1.5 transition focus:outline-none">
                                                <div class="p-2 rounded-full group-hover/btn:bg-red-50 transition {{ ($post['isLiked'] ?? false) ? 'text-red-500' : 'text-gray-400 group-hover/btn:text-red-500' }}">
                                                    <svg class="w-5 h-5 transition-transform duration-300 group-active/btn:scale-75" fill="{{ ($post['isLiked'] ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-bold like-count-{{ $post['id'] }} {{ ($post['isLiked'] ?? false) ? 'text-gray-700' : 'text-gray-500' }}">
                                                    {{ $post['likeCount'] ?? 0 }}
                                                </span>
                                            </button>
                                            @endauth

                                            {{-- Comment Button --}}
                                            <a href="{{ route('posts.show', $post['id']) }}#comments" class="group/btn flex items-center space-x-1.5 transition">
                                                <div class="p-2 rounded-full group-hover/btn:bg-blue-50 transition text-gray-400 group-hover/btn:text-blue-500">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                                </div>
                                                <span class="text-sm font-medium text-gray-500 group-hover/btn:text-gray-700">Komentar</span>
                                            </a>
                                        </div>

                                        <a href="{{ route('posts.show', $post['id']) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center group/readmore">
                                            Selengkapnya
                                            <svg class="w-3 h-3 ml-1 transform group-hover/readmore:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Pagination Feed --}}
                    @if(isset($meta) && $meta['last_page'] > 1)
                        <div class="flex justify-between items-center bg-white p-4 rounded-3xl border border-gray-100 shadow-sm mt-8 animate-fade-in-up">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest pl-2">
                                Halaman {{ $meta['page'] }} / {{ $meta['last_page'] }}
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                @if($meta['page'] > 1)
                                    <a href="{{ route('dashboard.feed', ['page' => $meta['page'] - 1]) }}" 
                                       class="px-5 py-2.5 bg-gray-50 text-gray-700 rounded-xl hover:bg-blue-600 hover:text-white transition font-bold text-xs border border-gray-100 flex items-center group">
                                        <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                        Sebelumnya
                                    </a>
                                @endif

                                @if($meta['page'] < $meta['last_page'])
                                    <a href="{{ route('dashboard.feed', ['page' => $meta['page'] + 1]) }}" 
                                       class="px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-bold text-xs shadow-lg shadow-blue-500/30 flex items-center group">
                                        Selanjutnya
                                        <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @else
                {{-- Empty State --}}
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <div class="w-20 h-20 bg-blue-50 text-blue-500 rounded-3xl flex items-center justify-center mx-auto mb-6 transform rotate-3">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Feed Anda Masih Kosong</h3>
                        <p class="text-gray-500 mb-8 leading-relaxed">
                            Sepertinya Anda belum mengikuti siapa pun, atau kreator yang Anda ikuti belum mengunggah karya baru. Yuk, mulai eksplorasi!
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <a href="{{ route('home') }}" class="px-6 py-3 bg-white border-2 border-gray-100 text-gray-700 rounded-xl hover:border-blue-600 hover:text-blue-600 transition font-bold text-sm">
                                Temukan Kreator
                            </a>
                            <a href="{{ route('posts.create') }}" class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-bold text-sm shadow-lg shadow-blue-500/30 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Upload Karya Pertama
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// API configuration (used by like-manager.js)
window.globalApiBase = @json(rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/'));
window.globalApiToken = @json(Session::get('api_token', ''));

// Note: toggleLikeDashboard is now handled by like-manager.js
</script>

<script src="{{ asset('js/dashboard-stats-syncer.js') }}"></script>

<style>
    /* Optional: Custom animation for fade in */
    @keyframes fadeInDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fadeInDown 0.5s ease-out forwards;
    }
</style>
@endsection