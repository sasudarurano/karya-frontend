@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-10 font-sans">
    <div class="container mx-auto px-4 lg:px-6 max-w-7xl">
        
        {{-- Header Section --}}
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-200"></div>
                    <div class="relative h-16 w-16 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shadow-sm text-blue-600">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-wider border border-blue-100">
                            Administrator Panel
                        </span>
                    </div>
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Moderasi Karya</h1>
                    <p class="text-slate-500 font-medium text-sm mt-1">
                        Kelola, tinjau, dan publikasikan karya mahasiswa.
                    </p>
                </div>
            </div>
            
            {{-- Action Buttons (Optional placeholder for future features like Export) --}}
            <div class="hidden md:block">
                 <button onclick="window.location.reload()" class="p-2 text-slate-400 hover:text-blue-600 transition-colors" title="Refresh Data">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                 </button>
            </div>
        </div>

        {{-- Alerts --}}
        @if(session('success'))
            <div class="mb-8 flex items-center p-4 bg-emerald-50/80 backdrop-blur border border-emerald-100 rounded-2xl text-emerald-800 shadow-sm animate-fade-in-down">
                <div class="h-8 w-8 bg-emerald-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                </div>
                <span class="font-semibold text-sm">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error') || (isset($error_message) && $error_message))
            <div class="mb-8 flex items-center p-4 bg-rose-50/80 backdrop-blur border border-rose-100 rounded-2xl text-rose-800 shadow-sm animate-fade-in-down">
                <div class="h-8 w-8 bg-rose-100 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-4 h-4 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </div>
                <span class="font-semibold text-sm">{{ session('error') ?? $error_message }}</span>
            </div>
        @endif

        {{-- Stats Overview --}}
        @if(!empty($posts))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="relative bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(203,213,225,0.3)] overflow-hidden group hover:border-blue-200 transition-all duration-300">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-blue-600 transform translate-x-4 -translate-y-4" fill="currentColor" viewBox="0 0 20 20"><path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"></path></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Antrean</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800">{{ $total ?? 0 }}</h3>
                        <span class="text-xs font-semibold text-slate-400">karya</span>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-1">
                    <div class="bg-blue-500 h-1 rounded-full" style="width: 100%"></div>
                </div>
            </div>

            <div class="relative bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(203,213,225,0.3)] overflow-hidden group hover:border-emerald-200 transition-all duration-300">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-emerald-600 transform translate-x-4 -translate-y-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"></path></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Live Publikasi</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-emerald-600">{{ $published ?? 0 }}</h3>
                        <span class="text-xs font-semibold text-emerald-600/60">aktif</span>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-1">
                    @php $pubPercent = ($total > 0) ? ($published / $total) * 100 : 0; @endphp
                    <div class="bg-emerald-500 h-1 rounded-full" style="width: {{ $pubPercent }}%"></div>
                </div>
            </div>

            <div class="relative bg-white p-6 rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(203,213,225,0.3)] overflow-hidden group hover:border-amber-200 transition-all duration-300">
                <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                    <svg class="w-24 h-24 text-amber-500 transform translate-x-4 -translate-y-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 9a1 1 0 112 0 1 1 0 01-2 0zm0 4a1 1 0 112 0 1 1 0 01-2 0z"></path></svg>
                </div>
                <div class="relative z-10">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Menunggu Review</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-amber-500">{{ $pending ?? 0 }}</h3>
                        <span class="text-xs font-semibold text-amber-500/60">pending</span>
                    </div>
                </div>
                <div class="mt-4 w-full bg-slate-100 rounded-full h-1">
                    @php $pendPercent = ($total > 0) ? ($pending / $total) * 100 : 0; @endphp
                    <div class="bg-amber-400 h-1 rounded-full" style="width: {{ $pendPercent }}%"></div>
                </div>
            </div>
        </div>
        @endif

        {{-- Main Content Card --}}
        <div class="bg-white rounded-[1.5rem] border border-slate-200 shadow-xl shadow-slate-200/50 overflow-hidden mb-12">
            
            {{-- Filter Toolbar --}}
            <div class="p-5 border-b border-slate-100 bg-white">
                <form method="GET" class="flex flex-col lg:flex-row gap-4 items-end lg:items-center justify-between">
                    <div class="flex-1 w-full grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                            <input type="text" name="search" value="{{ $searchQuery }}" 
                                class="pl-10 block w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500" 
                                placeholder="Cari judul atau penulis...">
                            @if($searchQuery)
                            <a href="{{ route('admin.posts.index', array_merge(request()->query(), ['search' => null])) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </a>
                            @endif
                        </div>

                        <div>
                            <select name="category" class="block w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-600">
                                <option value="">Semua Kategori</option>
                                @foreach($availableCategories as $cat)
                                    <option value="{{ $cat }}" {{ $filterCategory === $cat ? 'selected' : '' }}>{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <select name="prodi" class="block w-full rounded-xl border-slate-200 bg-slate-50 text-sm focus:border-blue-500 focus:ring-blue-500 text-slate-600">
                                <option value="">Semua Program Studi</option>
                                @foreach($availableProdis as $prodi)
                                    <option value="{{ $prodi }}" {{ $filterProdi === $prodi ? 'selected' : '' }}>{{ $prodi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full lg:w-auto justify-end">
                        <a href="{{ route('admin.posts.index') }}" class="px-4 py-2.5 text-sm font-bold text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition">Reset</a>
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold shadow-lg shadow-slate-200 transition-all transform active:scale-95">
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </form>
            </div>

            @if(empty($posts))
                {{-- Empty State --}}
                <div class="p-20 text-center">
                    <div class="w-24 h-24 bg-gradient-to-br from-slate-50 to-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300 shadow-inner">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">
                        @if($searchQuery || $filterCategory || $filterProdi)
                            Hasil tidak ditemukan
                        @else
                            Antrean Bersih!
                        @endif
                    </h3>
                    <p class="text-slate-500 mt-2 max-w-md mx-auto">
                        @if($searchQuery || $filterCategory || $filterProdi)
                            Tidak ada karya yang cocok dengan kriteria pencarian Anda.
                        @else
                            Tidak ada karya yang perlu dimoderasi saat ini. Kerja bagus!
                        @endif
                    </p>
                    @if($searchQuery || $filterCategory || $filterProdi)
                        <div class="mt-8">
                            <a href="{{ route('admin.posts.index') }}" class="text-blue-600 font-bold hover:underline">Lihat semua karya →</a>
                        </div>
                    @endif
                </div>
            @else

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="px-6 py-4">Karya & Judul</th>
                                <th class="px-6 py-4">Penulis</th>
                                <th class="px-6 py-4">Prodi & Kategori</th>
                                <th class="px-6 py-4">Verified By</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($posts as $post)
                            <tr class="group hover:bg-blue-50/30 transition-colors duration-200">
                                {{-- Kolom 1: Gambar & Judul --}}
                                <td class="px-6 py-5 align-top">
                                    <div class="flex gap-4">
                                        <div class="relative h-20 w-28 flex-shrink-0 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 shadow-sm">
                                            @php
                                                $imageUrl = null;

                                                if (!empty($post['attachments']) && is_array($post['attachments'])) {
                                                    foreach ($post['attachments'] as $attachment) {
                                                        if (str_contains($attachment['mime'] ?? '', 'image') && !empty($attachment['file_url'])) {
                                                            $cleanPath = str_replace('\\', '/', $attachment['file_url']);
                                                            if (str_starts_with($cleanPath, 'http')) {
                                                                $imageUrl = $cleanPath;
                                                            } else {
                                                                $backendUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
                                                                $imageUrl = $backendUrl . '/' . ltrim($cleanPath, '/');
                                                            }
                                                            break;
                                                        }
                                                    }
                                                }

                                                if (!$imageUrl && !empty($post['gdrive_folder_items']) && is_array($post['gdrive_folder_items'])) {
                                                    foreach ($post['gdrive_folder_items'] as $item) {
                                                        if (str_contains($item['mimeType'] ?? '', 'image') && !empty($item['thumbnailLink'])) {
                                                            $imageUrl = str_replace('=s220', '=w800', $item['thumbnailLink']);
                                                            break;
                                                        }
                                                    }
                                                }

                                                if (!$imageUrl && !empty($post['gdrive_url'])) {
                                                    $gdriveUrl = $post['gdrive_url'];
                                                    if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
                                                        $imageUrl = 'https://drive.google.com/thumbnail?id=' . $matches[1] . '&sz=w800';
                                                    } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
                                                        $imageUrl = 'https://drive.google.com/thumbnail?id=' . $matches[1] . '&sz=w800';
                                                    }
                                                }
                                            @endphp

                                            @if($imageUrl)
                                                <img src="{{ $imageUrl }}" class="h-full w-full object-cover transition duration-700 group-hover:scale-110" loading="lazy" 
                                                    onerror="this.parentElement.innerHTML='<div class=\'h-full w-full flex flex-col items-center justify-center text-slate-400 bg-slate-50\'><svg class=\'w-6 h-6 mb-1\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z\'></path></svg><span class=\'text-[9px] font-bold uppercase\'>Error</span></div>'">
                                            @else
                                                <div class="h-full w-full flex flex-col items-center justify-center text-slate-400 bg-slate-50">
                                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                    <span class="text-[9px] font-bold uppercase">No Image</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0 py-1">
                                            <h4 class="text-sm font-bold text-slate-800 leading-snug group-hover:text-blue-700 transition-colors line-clamp-2" title="{{ $post['title'] }}">
                                                {{ $post['title'] ?? 'Judul Tidak Tersedia' }}
                                            </h4>
                                            <div class="mt-2 flex items-center gap-2">
                                                 <span class="text-[10px] text-slate-400 font-mono bg-slate-100 px-1.5 py-0.5 rounded">ID: {{ $post['id'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 2: Penulis --}}
                                <td class="px-6 py-5 align-top">
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 text-xs font-bold border border-slate-200">
                                            {{ substr($post['author']['full_name'] ?? 'A', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-700">{{ $post['author']['full_name'] ?? 'Anonim' }}</p>
                                            <p class="text-xs text-slate-500 font-medium">{{ $post['author']['email'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kolom 3: Prodi & Kategori --}}
                                <td class="px-6 py-5 align-top">
                                    <div class="flex flex-col gap-2">
                                        @php $prodiList = $post['program_studi_list'] ?? []; @endphp
                                        @if(!empty($prodiList))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($prodiList as $prodi)
                                                    <span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-bold uppercase tracking-wide">{{ $prodi }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-slate-400 text-xs italic">Prodi tidak diset</span>
                                        @endif
                                        <span class="self-start inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 text-[10px] font-bold border border-blue-100">
                                            {{ ucfirst($post['category'] ?? 'Umum') }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Kolom 4: Verified By --}}
                                <td class="px-6 py-5 align-top">
                                    @if(isset($post['verifier']) && !empty($post['verifier']))
                                        <div class="flex items-center gap-2">
                                            <div class="h-7 w-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs font-bold border border-emerald-200">
                                                {{ substr($post['verifier']['full_name'] ?? 'A', 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-700">{{ $post['verifier']['full_name'] ?? 'Admin' }}</p>
                                                <p class="text-[10px] text-slate-500">{{ $post['verifier']['username'] ?? '-' }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Belum diverifikasi</span>
                                    @endif
                                </td>

                                {{-- Kolom 5: Status --}}
                                <td class="px-6 py-5 align-middle text-center">
                                    @php
                                        $isPublished = $post['is_published'] ?? false;
                                        $isRejected = isset($post['rejected_at']) && $post['rejected_at'];
                                        $isRevision = isset($post['revision_requested_at']) && $post['revision_requested_at'];
                                    @endphp
                                    @if($isPublished)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 text-emerald-700 font-bold text-[10px] uppercase bg-emerald-100/50 px-3 py-1 rounded-full border border-emerald-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Published
                                            </span>
                                        </div>
                                    @elseif($isRejected)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 text-rose-700 font-bold text-[10px] uppercase bg-rose-100/50 px-3 py-1 rounded-full border border-rose-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> Rejected
                                            </span>
                                        </div>
                                    @elseif($isRevision)
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 text-amber-700 font-bold text-[10px] uppercase bg-amber-100/50 px-3 py-1 rounded-full border border-amber-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Menunggu Revisi
                                            </span>
                                        </div>
                                    @else
                                        <div class="inline-flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 text-amber-700 font-bold text-[10px] uppercase bg-amber-100/50 px-3 py-1 rounded-full border border-amber-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pending
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Kolom 5: Aksi --}}
                                <td class="px-6 py-5 align-middle text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @php
                                            $isPublished = $post['is_published'] ?? false;
                                            $isRejected = isset($post['rejected_at']) && $post['rejected_at'];
                                        @endphp

                                        @if($isRejected && !$isPublished)
                                            {{-- Batalkan Penolakan (menggantikan Publish) --}}
                                            <form action="{{ route('admin.posts.clear-rejection', $post['id'] ?? 0) }}" method="POST" onsubmit="return confirm('Batalkan penolakan untuk karya ini? Karya akan kembali ke status Pending.');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-md shadow-blue-200" title="Cancel Reject">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4V6m0 0l-5 5m5-5l5 5m4 3v4m0 0h4m-4 0h-4"></path></svg>
                                                    Cancel Reject
                                                </button>
                                            </form>
                                        @else
                                            {{-- Toggle Publish --}}
                                            <form action="{{ route('admin.posts.toggle-publish', $post['id'] ?? 0) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status publikasi untuk karya ini?');">
                                                @csrf
                                                @method('PATCH')
                                                @if($isPublished)
                                                    <button type="submit" class="group/btn relative inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all" title="Tarik dari Publikasi">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    </button>
                                                @else
                                                    <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition shadow-md shadow-blue-200" title="Terbitkan Sekarang">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Publish
                                                    </button>
                                                @endif
                                            </form>
                                        @endif

                                        {{-- Detail Link --}}
                                        <a href="{{ route('admin.posts.show', $post['id'] ?? 0) }}" 
                                           class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all" 
                                           title="Lihat Detail Lengkap">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-400 font-medium">
                        Menampilkan <span class="font-bold text-slate-600">{{ count($posts) }}</span> dari <span class="font-bold text-slate-600">{{ $meta['total'] ?? count($posts) }}</span> karya.
                    </p>
                    
                    @if(isset($meta) && isset($meta['last_page']) && $meta['last_page'] > 1)
                    <div class="flex items-center gap-2">
                        @if($meta['page'] > 1)
                        <a href="{{ route('admin.posts.index', array_merge(request()->query(), ['page' => $meta['page'] - 1])) }}" 
                           class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                            Sebelumnya
                        </a>
                        @endif

                        <span class="text-xs font-bold text-slate-500 px-2">
                            {{ $meta['page'] }} / {{ $meta['last_page'] }}
                        </span>

                        @if($meta['page'] < $meta['last_page'])
                        <a href="{{ route('admin.posts.index', array_merge(request()->query(), ['page' => $meta['page'] + 1])) }}" 
                           class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                            Selanjutnya
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Debug Panel (Collapsed by default style) --}}
        @if(config('app.debug'))
            <div class="mt-12 group">
                <div class="cursor-pointer text-xs font-mono text-slate-400 text-center uppercase hover:text-slate-600 transition">Dev Debug Info (Hover to Show)</div>
                <div class="hidden group-hover:block mt-2 p-4 bg-slate-900 text-slate-300 rounded-xl text-xs font-mono overflow-x-auto shadow-2xl">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div><span class="text-slate-500">Role:</span> {{ session('user.role') ?? 'NULL' }}</div>
                        <div><span class="text-slate-500">Token:</span> {{ session('api_token') ? 'YES' : 'NO' }}</div>
                        <div><span class="text-slate-500">Items:</span> {{ is_array($posts) ? count($posts) : 'Not Array' }}</div>
                        <div class="col-span-4 border-t border-slate-800 pt-2 text-slate-500 break-all">
                           First Item Keys: {{ !empty($posts[0]) ? implode(', ', array_keys($posts[0])) : 'Empty' }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<style>
    @keyframes fade-in-down {
        0% { opacity: 0; transform: translateY(-10px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-down {
        animation: fade-in-down 0.5s ease-out forwards;
    }
</style>
@endsection
