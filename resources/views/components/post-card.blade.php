@props(['post', 'variant' => 'standard', 'rank' => null])

@php
    // --- GAMBAR UTAMA ---
    $imageUrl = null;
    if (!empty($post['attachments'])) {
        foreach ($post['attachments'] as $file) {
            if (str_contains($file['mime'] ?? '', 'image')) {
                $rawUrl    = $file['file_url'] ?? ('public/uploads/' . ($file['filename'] ?? ''));
                $cleanPath = str_replace('\\', '/', $rawUrl);
                $imageUrl  = str_starts_with($cleanPath, 'http')
                    ? $cleanPath
                    : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($cleanPath, '/');
                break;
            }
        }
    }
    if (!$imageUrl && !empty($post['gdrive_folder_items'])) {
        foreach ($post['gdrive_folder_items'] as $item) {
            if (str_contains($item['mimeType'] ?? '', 'image') && !empty($item['thumbnailLink'])) {
                $imageUrl = str_replace('=s220', '=w600', $item['thumbnailLink']);
                break;
            }
        }
    }
    if (!$imageUrl && !empty($post['gdrive_url'])) {
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $post['gdrive_url'], $m))
            $imageUrl = 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=w600';
        elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $post['gdrive_url'], $m))
            $imageUrl = 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=w600';
    }

    // --- AVATAR AUTHOR ---
    $avatarUrl      = null;
    $backendBaseUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
    $rawAvatar      = $post['author']['profile_picture']['file_url']
        ?? $post['author']['profile_picture']
        ?? $post['author']['pp_id']['file_url']
        ?? null;
    if ($rawAvatar) {
        $p = str_replace('\\', '/', $rawAvatar);
        $avatarUrl = str_starts_with($p, 'http') ? $p : $backendBaseUrl . '/' . ltrim($p, '/');
    }

    // --- META ---
    $authorName    = $post['author']['full_name'] ?? 'Mahasiswa';
    $authorInitial = strtoupper(substr($post['author']['username'] ?? 'U', 0, 1));
    $programStudi  = $post['author']['profile']['program_studi']['nama_program_studi'] ?? null;
    $date          = \Carbon\Carbon::parse($post['created_at'])->diffForHumans();
    $isLiked       = $post['isLiked'] ?? false;

    // --- KATEGORI WARNA ---
    $catMap = [
        'kp/magang'       => ['bg' => 'bg-amber-500',   'label' => '🏢 Magang'],
        'penelitian/pkm'  => ['bg' => 'bg-emerald-500', 'label' => '🔬 Penelitian'],
        'lomba'           => ['bg' => 'bg-violet-500',  'label' => '🏆 Lomba'],
        'project mandiri' => ['bg' => 'bg-sky-500',     'label' => '🚀 Proyek'],
        'skripsi'         => ['bg' => 'bg-rose-500',    'label' => '📄 Skripsi'],
    ];
    $catKey   = strtolower($post['category'] ?? '');
    $catBg    = $catMap[$catKey]['bg']    ?? 'bg-slate-500';
    $catLabel = $catMap[$catKey]['label'] ?? ('📁 ' . ($post['category'] ?? 'Umum'));

    // --- GRADIENT AVATAR WARNA ---
    $gradients = [
        'from-blue-500 to-indigo-600',
        'from-emerald-500 to-teal-600',
        'from-violet-500 to-purple-600',
        'from-rose-500 to-pink-600',
        'from-amber-500 to-orange-600',
    ];
    $gradClass = $gradients[crc32($authorName) % count($gradients)];
@endphp

@if($variant === 'poster')
<article class="group w-[190px] shrink-0 sm:w-[220px]">
    <a href="{{ route('posts.show', $post['id']) }}" class="relative block aspect-[2/3] overflow-hidden rounded-lg bg-slate-100 shadow-sm ring-1 ring-slate-200 transition duration-300 group-hover:-translate-y-1 group-hover:shadow-xl">
        @if($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $post['title'] }}"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                 loading="lazy"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="absolute inset-0 hidden flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                <svg class="mb-2 h-10 w-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs font-black uppercase tracking-wide">No Image</span>
            </div>
        @else
            <div class="flex h-full w-full flex-col items-center justify-center bg-gradient-to-br from-slate-100 via-white to-red-50 px-5 text-center text-slate-500">
                <svg class="mb-3 h-10 w-10 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-xs font-black uppercase tracking-wide">Dokumen Karya</span>
            </div>
        @endif

        <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-black/80 via-black/35 to-transparent"></div>
        <div class="absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-black/40 to-transparent"></div>

        @if($rank)
            <span class="absolute left-2 top-2 text-4xl font-black leading-none text-white drop-shadow-lg">{{ $rank }}</span>
        @endif

        <span class="absolute right-2 top-2 rounded-md bg-black/70 px-2 py-1 text-[11px] font-black uppercase text-white ring-1 ring-white/15">
            {{ $post['category'] ?? 'Karya' }}
        </span>

        <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between gap-2">
            <span class="inline-flex items-center gap-1 rounded-md bg-black/65 px-2 py-1 text-xs font-black text-amber-400">
                <svg class="h-3.5 w-3.5 fill-current" viewBox="0 0 20 20"><path d="M10 1.5l2.47 5.32 5.82.7-4.3 3.98 1.14 5.75L10 14.38l-5.13 2.87 1.14-5.75-4.3-3.98 5.82-.7L10 1.5z"/></svg>
                {{ $post['likeCount'] ?? 0 }}
            </span>
            <span class="inline-flex items-center gap-1 rounded-md bg-black/65 px-2 py-1 text-xs font-black text-white">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.25" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                {{ $post['commentCount'] ?? 0 }}
            </span>
        </div>
    </a>

    <h3 class="mt-3 line-clamp-2 text-sm font-black leading-snug text-slate-950 transition group-hover:text-red-600">
        <a href="{{ route('posts.show', $post['id']) }}">{{ $post['title'] }}</a>
    </h3>
    <p class="mt-1 truncate text-xs font-semibold text-slate-500">{{ $authorName }}</p>
</article>
@else
<article class="group flex flex-col bg-white rounded-2xl overflow-hidden shadow-sm ring-1 ring-slate-200/60 hover:shadow-lg hover:ring-slate-300/60 hover:-translate-y-1 transition-all duration-300 ease-out">

    {{-- THUMBNAIL --}}
    <a href="{{ route('posts.show', $post['id']) }}" class="relative block overflow-hidden bg-slate-100 shrink-0" style="aspect-ratio:16/9">

        @if($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $post['title'] }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                 loading="lazy"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            {{-- Fallback tersembunyi --}}
            <div class="absolute inset-0 hidden flex-col items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 text-slate-400">
                <svg class="w-9 h-9 mb-1 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-[10px] font-semibold uppercase tracking-wider">No Image</span>
            </div>
        @else
            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 text-slate-400">
                <svg class="w-9 h-9 mb-1.5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-50">Dokumen</span>
            </div>
        @endif

        {{-- Gradient overlay bottom --}}
        <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/40 to-transparent pointer-events-none"></div>

        {{-- Kategori badge --}}
        <span class="absolute top-2.5 left-2.5 inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold text-white uppercase tracking-wide {{ $catBg }} shadow-md">
            {{ $catLabel }}
        </span>

        {{-- Hover overlay: tombol lihat --}}
        <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <span class="inline-flex items-center gap-1.5 bg-white text-slate-900 text-xs font-bold px-4 py-2 rounded-full shadow-lg transform scale-90 group-hover:scale-100 transition-transform duration-300">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Lihat Karya
            </span>
        </div>
    </a>

    {{-- KONTEN --}}
    <div class="flex flex-col flex-1 px-4 py-3.5">

        {{-- Judul --}}
        <h3 class="text-[13px] font-bold text-slate-800 leading-snug mb-1.5 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
            <a href="{{ route('posts.show', $post['id']) }}">{{ $post['title'] }}</a>
        </h3>

        {{-- Excerpt --}}
        <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed mb-3 flex-1">
            {{ strip_tags($post['caption'] ?? 'Tidak ada deskripsi untuk karya ini.') }}
        </p>

        {{-- Footer --}}
        <div class="flex items-center justify-between pt-2.5 border-t border-slate-100 gap-2">

            {{-- Author --}}
            <a href="{{ route('profile.show', $post['author']['id']) }}" class="flex items-center gap-2 min-w-0 group/author">
                @if($avatarUrl)
                    <img src="{{ $avatarUrl }}" class="w-6 h-6 rounded-full object-cover ring-1 ring-slate-200 shrink-0" loading="lazy">
                @else
                    <div class="w-6 h-6 rounded-full bg-gradient-to-br {{ $gradClass }} flex items-center justify-center text-white text-[9px] font-extrabold shrink-0">
                        {{ $authorInitial }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold text-slate-700 truncate leading-none group-hover/author:text-blue-600 transition-colors">{{ $authorName }}</p>
                    <p class="text-[10px] text-slate-400 leading-none mt-0.5">{{ $date }}</p>
                </div>
            </a>

            {{-- Stats --}}
            <div class="flex items-center gap-3 shrink-0">
                <button onclick="toggleLikeCard({{ $post['id'] }}, this)"
                        data-post-id="{{ $post['id'] }}"
                        data-liked="{{ $isLiked ? 'true' : 'false' }}"
                        data-post-like-button="{{ $post['id'] }}"
                        data-like-count-selector=".like-count-{{ $post['id'] }}"
                        class="flex items-center gap-1 text-[11px] font-semibold transition-all hover:text-rose-500 hover:scale-110 {{ $isLiked ? 'text-rose-500' : 'text-slate-400' }}"
                        title="Suka">
                    <svg class="w-3.5 h-3.5" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    <span class="like-count-{{ $post['id'] }}">{{ $post['likeCount'] ?? 0 }}</span>
                </button>

                <a href="{{ route('posts.show', $post['id']) }}#comments"
                   class="flex items-center gap-1 text-[11px] font-semibold text-slate-400 hover:text-blue-500 hover:scale-110 transition-all"
                   title="Komentar">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>{{ $post['commentCount'] ?? 0 }}</span>
                </a>
            </div>
        </div>
    </div>
</article>
@endif
