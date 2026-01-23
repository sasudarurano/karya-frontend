@props(['post'])

@php
    // --- 1. LOGIKA GAMBAR UTAMA ---
    $imageUrl = null;
    if (!empty($post['attachments']) && count($post['attachments']) > 0) {
        $firstFile = $post['attachments'][0];
        if (str_contains($firstFile['mime'] ?? '', 'image')) {
            $rawUrl = $firstFile['file_url'] ?? ('public/uploads/' . ($firstFile['filename'] ?? ''));
            $cleanPath = str_replace('\\', '/', $rawUrl);
            
            if (str_starts_with($cleanPath, 'http')) {
                $imageUrl = $cleanPath;
            } else {
                $backendUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
                $imageUrl = $backendUrl . '/' . ltrim($cleanPath, '/');
            }
        }
    }

    // --- 2. LOGIKA FOTO PROFIL AUTHOR ---
    $avatarUrl = null;
    $backendBaseUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
    
    $rawAvatar = $post['author']['profile_picture']['file_url'] 
        ?? $post['author']['profile_picture'] 
        ?? $post['author']['pp_id']['file_url'] 
        ?? null;

    if ($rawAvatar) {
        $path = str_replace('\\', '/', $rawAvatar);
        $avatarUrl = str_starts_with($path, 'http') ? $path : $backendBaseUrl . '/' . ltrim($path, '/');
    }

    // --- 3. HELPER LAINNYA ---
    $authorName = $post['author']['full_name'] ?? 'Mahasiswa';
    $authorInitial = strtoupper(substr($post['author']['username'] ?? 'U', 0, 1));
    $programStudi = $post['author']['profile']['program_studi']['nama_program_studi'] ?? null;
    $date = \Carbon\Carbon::parse($post['created_at'])->diffForHumans();
    $isLiked = $post['isLiked'] ?? false;
@endphp

<div class="group relative flex flex-col h-full bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 hover:-translate-y-1 transition-all duration-300 overflow-hidden">
    
    {{-- BAGIAN GAMBAR --}}
    <div class="relative h-52 overflow-hidden bg-slate-100">
        {{-- Kategori Badge (Glassmorphism) --}}
        <div class="absolute top-4 left-4 z-10">
            <span class="px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-white/90 backdrop-blur-md text-slate-800 shadow-sm border border-white/50">
                {{ $post['category'] ?? 'Umum' }}
            </span>
        </div>

        <a href="{{ route('posts.show', $post['id']) }}" class="block h-full w-full">
            @if($imageUrl)
                <img src="{{ $imageUrl }}" 
                     alt="{{ $post['title'] }}" 
                     class="w-full h-full object-cover transform group-hover:scale-110 transition duration-700 ease-in-out"
                     loading="lazy"
                     onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50\'><svg class=\'w-10 h-10 mb-2\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg><span class=\'text-xs font-medium uppercase\'>No Image</span></div>';">
            @else
                {{-- Fallback jika tidak ada gambar --}}
                <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-50 to-slate-100 text-slate-300">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="text-xs font-bold uppercase tracking-widest opacity-60">Dokumen</span>
                </div>
            @endif
        </a>
    </div>

    {{-- BAGIAN KONTEN --}}
    <div class="flex flex-col flex-1 p-5">
        {{-- Metadata Atas (Tanggal) --}}
        <div class="flex items-center gap-2 mb-3">
            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
            <span class="text-xs text-slate-400 font-medium">{{ $date }}</span>
        </div>

        {{-- Judul --}}
        <h3 class="text-lg font-bold text-slate-800 leading-snug mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors">
            <a href="{{ route('posts.show', $post['id']) }}">
                {{ $post['title'] }}
            </a>
        </h3>

        {{-- Caption / Excerpt --}}
        <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed mb-6 flex-1">
            {{ $post['caption'] ?? 'Tidak ada deskripsi singkat untuk karya ini.' }}
        </p>

        {{-- Footer: Author & Stats --}}
        <div class="pt-4 mt-auto border-t border-slate-100 flex items-center justify-between">
            
            {{-- Author Info --}}
            <a href="{{ route('profile.show', $post['author']['id']) }}" class="flex items-center gap-2.5 group/author max-w-[60%]">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shadow-sm" loading="lazy">
                @else
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                        {{ $authorInitial }}
                    </div>
                @endif
                <div class="flex flex-col min-w-0">
                    <span class="text-xs font-bold text-slate-700 truncate group-hover/author:text-blue-600 transition-colors">
                        {{ $authorName }}
                    </span>
                    @if($programStudi)
                        <span class="text-[10px] text-slate-400 truncate">{{ $programStudi }}</span>
                    @endif
                </div>
            </a>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-3 text-slate-400">
                {{-- Like Button --}}
                <button onclick="toggleLikeCard({{ $post['id'] }}, this)" 
                        data-post-id="{{ $post['id'] }}" 
                        data-liked="{{ $isLiked ? 'true' : 'false' }}"
                        data-post-like-button="{{ $post['id'] }}"
                        data-like-count-selector=".like-count-{{ $post['id'] }}"
                        class="flex items-center gap-1 text-xs font-medium transition-colors hover:text-rose-500 {{ $isLiked ? 'text-rose-500' : '' }}"
                        title="Suka">
                    <svg class="w-4 h-4" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                    <span class="like-count-{{ $post['id'] }}">{{ $post['likeCount'] ?? 0 }}</span>
                </button>

                {{-- Comment Link --}}
                <a href="{{ route('posts.show', $post['id']) }}#comments" class="flex items-center gap-1 text-xs font-medium hover:text-blue-600 transition-colors" title="Komentar">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <span>{{ $post['commentCount'] ?? 0 }}</span>
                </a>
            </div>
        </div>
    </div>
</div>