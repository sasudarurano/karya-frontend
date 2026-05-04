@extends('layouts.app')

@section('content')

{{-- 1. PRE-PROCESSING DATA --}}
@php
    // Helper URL
    $backendBaseUrl = rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/');
    
    function getSafeUrl($path, $baseUrl) {
        if (!$path) return null;
        $path = str_replace('\\', '/', $path);
        // For file URLs, remove /api from base URL
        $fileBaseUrl = str_replace('/api', '', $baseUrl);
        return str_starts_with($path, 'http') ? $path : $fileBaseUrl . '/' . ltrim($path, '/');
    }

    // Data Penulis
    $author = $post['author'];
    $authorImg = getSafeUrl($author['profile_picture']['file_url'] ?? null, $backendBaseUrl);
    
    // Data User & State
    $currentUserId = Session::get('user')['id'] ?? null;
    $isLoggedIn = Session::has('api_token');
    
    // Status Interactions
    $isLiked = $post['isLiked'] ?? false;
    $likeCount = $post['likeCount'] ?? 0;
    $isFollowing = $author['is_followed'] ?? false;

    // Formatting
    \Carbon\Carbon::setLocale('id');
    $dateFormatted = \Carbon\Carbon::parse($post['created_at'])->translatedFormat('d F Y');
    $startDateFormatted = !empty($post['start_date']) ? \Carbon\Carbon::parse($post['start_date'])->translatedFormat('d M Y') : null;
    $endDateFormatted = !empty($post['end_date']) ? \Carbon\Carbon::parse($post['end_date'])->translatedFormat('d M Y') : null;
@endphp

<div class="min-h-screen bg-[#F8FAFC] pb-24 font-sans selection:bg-blue-100 selection:text-blue-900">
    
    {{-- Decorative Background --}}
    <div class="fixed top-0 left-0 right-0 h-[500px] bg-gradient-to-b from-blue-50/80 to-transparent -z-10 pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-6 sm:pt-10">
        
        {{-- Breadcrumb Modern --}}
        <nav class="flex items-center text-sm font-medium text-slate-500 mb-6 sm:mb-10 overflow-x-auto whitespace-nowrap scrollbar-hide py-2">
            <a href="{{ route('home') }}" class="hover:text-blue-600 transition-colors flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Beranda
            </a>
            <svg class="w-4 h-4 mx-2 text-slate-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="text-slate-800 font-semibold truncate max-w-[200px] sm:max-w-md">{{ $post['title'] }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14">
            
            {{-- === LEFT COLUMN: MAIN CONTENT (8/12) === --}}
            <div class="lg:col-span-8 space-y-8 sm:space-y-12">
                
                {{-- 1. Header Section --}}
                <header class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-blue-600 text-white uppercase tracking-wider shadow-sm shadow-blue-200">
                            {{ $post['category'] }}
                        </span>
                        <span class="text-slate-300">|</span>
                        <span class="text-slate-500 text-sm font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $dateFormatted }}
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-black text-slate-900 leading-[1.15] tracking-tight">
                        {{ $post['title'] }}
                    </h1>

                    {{-- Timeline --}}
                    @if($startDateFormatted || $endDateFormatted)
                        <div class="inline-flex items-center gap-3 px-4 py-2 bg-white rounded-xl border border-slate-200 text-sm text-slate-600 shadow-sm">
                            <div class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <span class="font-medium">
                                @if($startDateFormatted && $endDateFormatted)
                                    {{ $startDateFormatted }} — {{ $endDateFormatted }}
                                @elseif($startDateFormatted)
                                    Dimulai {{ $startDateFormatted }}
                                @else
                                    Selesai {{ $endDateFormatted }}
                                @endif
                            </span>
                        </div>
                    @endif

                    {{-- Verified By Badge --}}
                    @if(isset($post['verifier']) && !empty($post['verifier']))
                        <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-200 text-sm">
                            <div class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Diverifikasi oleh</span>
                                <span class="font-bold text-emerald-700">{{ $post['verifier']['full_name'] ?? 'Admin' }}</span>
                            </div>
                        </div>
                    @endif
                </header>

                {{-- 2. Media Showcase --}}
                <div class="space-y-4 mb-4">
                    <h3 class="text-xl font-bold text-slate-900 flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-blue-600 to-indigo-600 rounded-full"></span>
                        Screenshoot / Media Karya
                    </h3>
                    <p class="text-xs text-slate-500 bg-blue-50/50 border border-blue-100 p-3 rounded-xl flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pastikan akses link Google Drive diubah ke "Anyone with the link" agar media dapat tampil di sini.
                    </p>
                </div>

                <div class="space-y-8">
                    @php
                        $mediaItems = [];
                        if (!empty($post['gdrive_folder_items']) && count($post['gdrive_folder_items']) > 0) {
                            foreach($post['gdrive_folder_items'] as $item) {
                                if (str_contains($item['mimeType'] ?? '', 'image') || str_contains($item['mimeType'] ?? '', 'video')) {
                                    $mediaItems[] = [
                                        'type' => str_contains($item['mimeType'], 'image') ? 'image' : 'video',
                                        'url' => str_contains($item['mimeType'], 'image') ? str_replace('=s220', '=w1500', $item['thumbnailLink'] ?? '') : ($item['webContentLink'] ?? ''),
                                        'thumbnail' => $item['thumbnailLink'] ?? null
                                    ];
                                }
                            }
                        } elseif (!empty($post['attachments']) && count($post['attachments']) > 0) {
                            foreach($post['attachments'] as $item) {
                                if (str_contains($item['mime'] ?? '', 'image')) {
                                    $imgUrl = getSafeUrl($item['file_url'], $backendBaseUrl);
                                    $mediaItems[] = [
                                        'type' => 'image',
                                        'url' => $imgUrl,
                                        'thumbnail' => $imgUrl
                                    ];
                                } elseif (str_contains($item['mime'] ?? '', 'video')) {
                                    $vidUrl = getSafeUrl($item['file_url'], $backendBaseUrl);
                                    $mediaItems[] = [
                                        'type' => 'video',
                                        'url' => $vidUrl,
                                        'thumbnail' => null
                                    ];
                                }
                            }
                        }
                    @endphp

                    @if(count($mediaItems) > 0)
                        <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl shadow-slate-200 bg-slate-900 aspect-video group select-none" id="imageSlider">
                            {{-- Slides Container --}}
                            @foreach($mediaItems as $index => $item)
                                @if($item['type'] === 'image')
                                    <div class="slide-item absolute inset-0 w-full h-full transition-opacity duration-500 ease-out {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" data-index="{{ $index }}">
                                        {{-- Artistic Blur Backdrop --}}
                                        <div class="absolute inset-0 bg-cover bg-center opacity-30 blur-[50px] scale-125 transform translate-y-10" style="background-image: url('{{ $item['url'] }}');"></div>
                                        {{-- Main Image --}}
                                        <div class="absolute inset-0 flex items-center justify-center p-2 sm:p-4">
                                            <img src="{{ $item['url'] }}" class="max-w-full max-h-full object-contain rounded-lg shadow-lg relative z-10" alt="{{ $post['title'] }}">
                                        </div>
                                    </div>
                                @elseif($item['type'] === 'video')
                                    <div class="slide-item absolute inset-0 w-full h-full transition-opacity duration-500 ease-out {{ $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }} bg-black" data-index="{{ $index }}">
                                        <video src="{{ $item['url'] }}" controls controlsList="nodownload" class="w-full h-full object-contain outline-none pb-8"></video>
                                    </div>
                                @endif
                            @endforeach

                            {{-- Navigation & Dots --}}
                            @if(count($mediaItems) > 1)
                                <div class="absolute inset-0 z-20 flex items-center justify-between p-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                                    <button onclick="changeSlide(-1)" class="pointer-events-auto p-3 rounded-full bg-black/20 hover:bg-black/50 text-white backdrop-blur-md transition-all hover:scale-105 border border-white/10">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                    </button>
                                    <button onclick="changeSlide(1)" class="pointer-events-auto p-3 rounded-full bg-black/20 hover:bg-black/50 text-white backdrop-blur-md transition-all hover:scale-105 border border-white/10">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </button>
                                </div>
                                <div class="absolute bottom-6 left-0 right-0 flex justify-center gap-2 z-20">
                                    @foreach($mediaItems as $index => $item)
                                        <button onclick="goToSlide({{ $index }})" class="dot-indicator h-1.5 rounded-full transition-all duration-300 shadow-sm {{ $index === 0 ? 'bg-white w-8' : 'bg-white/40 hover:bg-white w-2' }}" data-index="{{ $index }}"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @elseif(!empty($post['gdrive_url']))
                        @php
                            $gdriveUrl = $post['gdrive_url'];
                            $fileId = null;
                            $folderId = null;

                            if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
                                $fileId = $matches[1];
                            } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
                                $fileId = $matches[1];
                            } elseif (preg_match('/drive\.google\.com\/drive\/folders\/([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
                                $folderId = $matches[1];
                            }
                        @endphp
                        
                        @if($folderId)
                            <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl shadow-slate-200 border border-slate-200 min-h-[400px] sm:min-h-[500px]">
                                <iframe src="https://drive.google.com/embeddedfolderview?id={{ $folderId }}#list" class="w-full h-full absolute inset-0" frameborder="0"></iframe>
                            </div>
                        @elseif($fileId)
                            <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl shadow-slate-200 bg-slate-900 aspect-video group select-none flex items-center justify-center">
                                <div class="absolute inset-0 bg-cover bg-center opacity-30 blur-[50px] scale-125 transform translate-y-10" style="background-image: url('https://drive.google.com/thumbnail?id={{ $fileId }}&sz=w1000');"></div>
                                <img src="https://drive.google.com/thumbnail?id={{ $fileId }}&sz=w1000" class="max-w-full max-h-full object-contain rounded-lg shadow-lg relative z-10" alt="{{ $post['title'] }}" onerror="this.onerror=null; this.parentElement.innerHTML='<a href=\'{{ $gdriveUrl }}\' target=\'_blank\' class=\'flex flex-col items-center gap-3 text-white hover:text-blue-300 transition z-20\'><svg class=\'w-16 h-16\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14\'></path></svg><span class=\'text-xl font-bold\'>Buka File Eksternal</span></a><p class=\'absolute bottom-4 text-xs text-slate-400 z-20\'>Thumbnail tidak bisa dimuat secara langsung, silakan buka link.</p>';">
                            </div>
                        @else
                            <div class="relative w-full rounded-3xl overflow-hidden shadow-2xl shadow-slate-200 bg-blue-50 aspect-video group select-none flex items-center justify-center border border-blue-200">
                                <a href="{{ $gdriveUrl }}" target="_blank" class="flex flex-col items-center gap-3 text-blue-600 hover:text-blue-800 transition">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    <span class="text-xl font-bold">Buka Link Dokumen/Google Drive Karya</span>
                                </a>
                            </div>
                        @endif
                    @endif

                </div>

                {{-- 5. Content / Description --}}
                <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-blue-600 to-indigo-600 rounded-full"></span>
                        Deskripsi Karya
                    </h3>
                    <div class="prose prose-lg prose-slate prose-blue max-w-none text-slate-600 leading-relaxed prose-img:rounded-xl">
                        <div class="whitespace-pre-line">{!! $post['caption'] ?? 'Penulis belum menambahkan deskripsi detail untuk karya ini.' !!}</div>
                    </div>
                </div>

                {{-- YouTube Video --}}
                @if(!empty($post['url_youtube']))
                    @php
                        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|shorts\/)|youtu\.be\/)([\w-]{11})/i', $post['url_youtube'], $matches);
                        $videoId = $matches[1] ?? null;
                    @endphp
                    @if($videoId)
                        <div class="rounded-3xl overflow-hidden shadow-2xl shadow-slate-200 border-4 border-white bg-black aspect-video relative z-0 mb-8">
                            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $videoId }}" frameborder="0" allowfullscreen></iframe>
                        </div>
                    @endif
                @endif

                {{-- 3. Action Bar (Sticky Mobile) --}}
                <div class="sticky top-20 z-30 lg:static bg-white/80 backdrop-blur-lg lg:bg-transparent lg:backdrop-blur-none border-y border-slate-200 lg:border-0 py-3 lg:py-0 px-4 -mx-4 lg:mx-0 lg:px-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 sm:gap-6">
                            {{-- Like Button --}}
                            <button id="likeButton" 
                                    onclick="toggleLike()" 
                                    data-post-id="{{ $post['id'] }}" 
                                    data-liked="{{ $isLiked ? 'true' : 'false' }}" 
                                    class="group flex items-center gap-3 transition-all focus:outline-none">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-rose-200 rounded-full blur opacity-0 group-hover:opacity-40 transition-opacity duration-300"></div>
                                    <div class="relative p-2.5 rounded-full border {{ $isLiked ? 'bg-rose-50 border-rose-200 text-rose-500' : 'bg-white border-slate-200 text-slate-400 group-hover:border-rose-200 group-hover:text-rose-500' }} transition-all duration-300">
                                        <svg id="likeIcon" class="w-6 h-6 transform group-active:scale-90 transition-transform" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex flex-col items-start">
                                    <span id="likeCount" class="text-xl font-black {{ $isLiked ? 'text-rose-600' : 'text-slate-700' }}">{{ $likeCount }}</span>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Apresiasi</span>
                                </div>
                            </button>

                            {{-- Comment Scroll --}}
                            <a href="#comments" class="group flex items-center gap-3 transition-all">
                                <div class="relative p-2.5 rounded-full bg-white border border-slate-200 text-slate-400 group-hover:border-blue-200 group-hover:text-blue-500 transition-all duration-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                </div>
                                <div class="flex flex-col items-start">
                                    <span class="text-xl font-black text-slate-700">{{ count($comments ?? []) }}</span>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Komentar</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 4. HKI Document Section --}}
                @php
                    // Cari dokumen PDF di dalam attachments dengan mime type application/pdf
                    $hkiDoc = null;
                    if (isset($post['attachments']) && is_array($post['attachments'])) {
                        foreach ($post['attachments'] as $attachment) {
                            if (isset($attachment['mime']) && str_contains($attachment['mime'], 'pdf')) {
                                $hkiDoc = $attachment;
                                break;
                            }
                        }
                    }
                    
                    // Fallback: cek field alternatif jika ada
                    if (!$hkiDoc) {
                        $hkiDocPath = $post['hki_document'] ?? $post['post_document'] ?? $post['document'] ?? $post['pdf'] ?? null;
                        if ($hkiDocPath) {
                            $hkiDoc = ['file_url' => $hkiDocPath];
                        }
                    }
                @endphp
                @if(isset($hkiDoc) && !empty($hkiDoc))
                <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-100">
                    <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                        <span class="w-1.5 h-6 bg-gradient-to-b from-amber-600 to-orange-600 rounded-full"></span>
                        Dokumen HKI / Laporan (PDF)
                    </h3>
                    <div class="space-y-4">
                        @php
                            $hkiUrl = getSafeUrl($hkiDoc['file_url'], $backendBaseUrl);
                            $hkiFileName = $hkiDoc['filename'] ?? basename($hkiDoc['file_url']);
                        @endphp
                        <a href="{{ $hkiUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-4 p-5 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200 hover:border-amber-300 hover:shadow-lg transition-all group">
                            <div class="flex-shrink-0 p-3 bg-amber-100 text-amber-600 rounded-xl group-hover:bg-amber-600 group-hover:text-white transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-amber-900 group-hover:text-amber-700 transition">{{ $hkiFileName }}</p>
                                <p class="text-xs text-amber-700">Klik untuk membuka PDF</p>
                            </div>
                            <div class="flex-shrink-0 text-amber-400 group-hover:text-amber-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </div>
                        </a>
                        <p class="text-sm text-slate-500 text-center">Format PDF - Dokumentasi lengkap HKI/Laporan karya</p>
                    </div>
                </div>
                @endif


                {{-- 5. Comments Section --}}
                <div id="comments" class="pt-8">
                    <h3 class="text-xl font-bold text-slate-900 mb-6">Diskusi & Masukan</h3>
                    <x-comments-section :postId="$post['id']" :comments="$comments" />
                </div>
            </div>


            {{-- === RIGHT COLUMN: SIDEBAR (4/12) === --}}
            <div class="lg:col-span-4">
                
                {{-- 
                    PERUBAHAN PENTING:
                    Kita bungkus SEMUA elemen sidebar dalam satu div dengan class 'sticky'.
                    Dengan begini, Author, Dosen, dan Kontributor akan menjadi satu kesatuan unit
                    yang menempel saat di-scroll.
                --}}
                <div class="sticky top-24 space-y-6">

                    {{-- A. AUTHOR CARD --}}
                    <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 border border-slate-100 overflow-hidden">
                        {{-- Card Banner --}}
                        <div class="h-24 bg-gradient-to-r from-blue-600 to-indigo-600 relative">
                            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 16px 16px;"></div>
                        </div>

                        <div class="px-6 pb-6 -mt-12 text-center relative">
                            {{-- Avatar --}}
                            <div class="relative inline-block mb-3">
                                <div class="p-1.5 bg-white rounded-2xl shadow-sm">
                                    <a href="{{ route('profile.show', $author['id']) }}">
                                        @if($authorImg)
                                            <img src="{{ $authorImg }}" class="w-24 h-24 rounded-xl object-cover" alt="{{ $author['full_name'] }}" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($author['full_name']) }}&background=0D8ABC&color=fff';">
                                        @else
                                            <div class="w-24 h-24 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center text-3xl font-bold">
                                                {{ strtoupper(substr($author['full_name'] ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                    </a>
                                </div>
                            </div>

                            {{-- Name & Details --}}
                            <a href="{{ route('profile.show', $author['id']) }}" class="block group">
                                <h2 class="text-xl font-black text-slate-900 group-hover:text-blue-600 transition">{{ $author['full_name'] }}</h2>
                                <p class="text-slate-500 font-medium text-sm">{{ '@' . ($author['username'] ?? '-') }}</p>
                            </a>
                            
                            @if(isset($author['profile']['program_studi']['nama_program_studi']))
                                <div class="mt-3 inline-block px-3 py-1 bg-slate-50 text-slate-600 text-xs font-bold rounded-full border border-slate-200">
                                    {{ $author['profile']['program_studi']['nama_program_studi'] }}
                                </div>
                            @endif

                            {{-- Stats Row --}}
                            <div class="grid grid-cols-2 gap-4 mt-6 py-4 border-t border-slate-100">
                                <div>
                                    <span class="block text-xl font-black text-slate-800">{{ $author['stats']['posts'] ?? 0 }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Karya</span>
                                </div>
                                <div>
                                    <span class="block text-xl font-black text-slate-800">{{ $author['stats']['followers'] ?? 0 }}</span>
                                    <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Pengikut</span>
                                </div>
                            </div>

                            {{-- Follow Button --}}
                            <button id="followButton" 
                                    onclick="toggleFollow()" 
                                    data-author-id="{{ $author['id'] }}"
                                    data-is-following="{{ $isFollowing ? 'true' : 'false' }}"
                                    class="w-full mt-2 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 flex items-center justify-center gap-2 shadow-sm
                                    {{ $isFollowing 
                                        ? 'bg-slate-100 text-slate-600 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 border border-transparent' 
                                        : 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow-lg hover:shadow-blue-200' }}">
                                <span class="follow-text">{{ $isFollowing ? 'Mengikuti' : 'Ikuti Penulis' }}</span>
                            </button>
                        </div>
                    </div>

                    {{-- B. CONTRIBUTORS & SUPERVISORS --}}
                    @php 
                        $lecturers = [];
                        if (!empty($post['supervisor'])) $lecturers[] = $post['supervisor'];
                        if (!empty($post['lecturers']) && is_array($post['lecturers'])) $lecturers = array_merge($lecturers, $post['lecturers']);
                        
                        $contributors = $post['contributors'] ?? [];
                    @endphp

                    {{-- Hapus duplikasi card Dosen Pembimbing di sini; gunakan satu card di section C di bawah --}}

                    @if(count($contributors) > 0)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Tim Kontributor
                            </h3>
                            <div class="space-y-3">
                                @foreach($contributors as $contributor)
                                    @php 
                                        $contImg = getSafeUrl($contributor['profile_picture']['file_url'] ?? null, $backendBaseUrl);
                                        $programStudi = $contributor['profile']['program_studi']['nama_program_studi'] ?? null;
                                    @endphp
                                    <a href="{{ route('profile.show', $contributor['id']) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-emerald-50 border border-transparent hover:border-emerald-200 transition group">
                                        @if($contImg)
                                            <img src="{{ $contImg }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-emerald-50" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($contributor['full_name']) }}&background=10B981&color=fff';">
                                        @else
                                            <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-bold ring-2 ring-emerald-50">
                                                {{ strtoupper(substr($contributor['full_name'] ?? 'U', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-800 truncate group-hover:text-emerald-700 transition">{{ $contributor['full_name'] }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ '@' . ($contributor['username'] ?? 'user') }}</p>
                                            @if($programStudi)
                                                <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ $programStudi }}</p>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- C. DOSEN PEMBIMBING (USERNAME) --}}
                    @if(count($lecturers) > 0)
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
                            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                Dosen Pembimbing
                            </h3>
                            <div class="space-y-3">
                                @foreach($lecturers as $lecturer)
                                    @php 
                                        $lecImg = getSafeUrl($lecturer['profile_picture']['file_url'] ?? null, $backendBaseUrl);
                                    @endphp
                                    <div class="flex items-center gap-3 p-2 rounded-xl bg-purple-50/50 border border-purple-100">
                                        @if($lecImg)
                                            <img src="{{ $lecImg }}" class="w-10 h-10 rounded-full object-cover ring-2 ring-purple-50" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($lecturer['full_name']) }}&background=A855F7&color=fff';">
                                        @else
                                            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-xs font-bold ring-2 ring-purple-50">
                                                {{ strtoupper(substr($lecturer['full_name'] ?? 'D', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-bold text-slate-800 truncate">{{ $lecturer['full_name'] }}</p>
                                            <p class="text-xs text-purple-600 font-medium truncate">{{ '@' . ($lecturer['username'] ?? 'dosen') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div> {{-- End of Sticky Wrapper --}}
                
            </div>
        </div>
    </div>
</div>

{{-- CONFIGURATION SCRIPTS --}}
<script type="text/javascript">
    const postId = @json($post['id']);
    const authorId = @json($post['author']['id'] ?? null);
    const currentUserId = @json($currentUserId);
    const isLoggedIn = @json($isLoggedIn);
    const loginUrl = @json(route('login'));
    const apiToken = @json(Session::get('api_token'));
    const apiBase = @json($backendBaseUrl);
</script>

<script src="{{ asset('js/post-show-reactive.js') }}"></script>

@endsection