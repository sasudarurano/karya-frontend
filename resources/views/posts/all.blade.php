@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    
    {{-- Header Section --}}
    <div class="mb-12">
        <div class="flex items-center space-x-4 mb-2">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 font-medium">Beranda</a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 font-semibold">{{ $title }}</span>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">{{ $title }}</h1>
        <p class="text-gray-600 text-lg">
            Jelajahi {{ count($allPosts) }} karya 
            @if($sort === 'popular')
                paling populer dari mahasiswa kami
            @else
                terbaru yang baru saja diupload
            @endif
        </p>
    </div>

    {{-- Filter & Sorting Bar --}}
    <div class="mb-8 flex items-center justify-between">
        <div class="text-sm text-gray-600">
            Menampilkan <span class="font-bold text-gray-900">{{ count($allPosts) }}</span> karya
        </div>
        <div class="flex items-center space-x-3">
            <label class="text-sm font-medium text-gray-700">Urutkan:</label>
            <select onchange="if(this.value) window.location.href = '?sort=' + this.value;" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- Pilih Urutan --</option>
                <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Paling Populer</option>
                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Baru Diupload</option>
            </select>
        </div>
    </div>

    {{-- Posts Grid --}}
    @if(!empty($allPosts) && count($allPosts) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($allPosts as $post)
                <x-post-card :post="$post" />
            @endforeach
        </div>

    {{-- Pagination Controls (Adapted from Home) --}}
    @if(isset($pagination) && $pagination['totalPages'] > 1)
        <div class="mt-16 flex justify-center">
            <nav class="inline-flex items-center gap-2 bg-white rounded-xl shadow-sm border border-gray-200 p-2">
                {{-- Previous Button --}}
                @if($pagination['hasPrevPage'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['currentPage'] - 1]) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Sebelumnya
                    </a>
                @else
                    <span class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                        Sebelumnya
                    </span>
                @endif

                {{-- Page Numbers --}}
                @php
                    $currentPage = $pagination['currentPage'];
                    $totalPages = $pagination['totalPages'];
                    $range = 2;
                    $start = max(1, $currentPage - $range);
                    $end = min($totalPages, $currentPage + $range);
                @endphp

                @if($start > 1)
                    <a href="{{ request()->fullUrlWithQuery(['page' => 1]) }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">1</a>
                    @if($start > 2)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                @endif

                @for($i = $start; $i <= $end; $i++)
                    @if($i == $currentPage)
                        <span class="px-3 py-2 rounded-lg text-sm font-bold bg-blue-600 text-white shadow-md">
                            {{ $i }}
                        </span>
                    @else
                        <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                           class="px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">
                            {{ $i }}
                        </a>
                    @endif
                @endfor

                @if($end < $totalPages)
                    @if($end < $totalPages - 1)
                        <span class="px-2 text-gray-400">...</span>
                    @endif
                    <a href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}" 
                       class="px-3 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors">{{ $totalPages }}</a>
                @endif

                {{-- Next Button --}}
                @if($pagination['hasNextPage'])
                    <a href="{{ request()->fullUrlWithQuery(['page' => $pagination['currentPage'] + 1]) }}" 
                       class="px-4 py-2 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors flex items-center gap-1">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @else
                    <span class="px-4 py-2 rounded-lg text-sm font-medium text-gray-400 cursor-not-allowed flex items-center gap-1">
                        Selanjutnya
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </span>
                @endif
            </nav>
        </div>
        
        <div class="mt-4 text-center text-gray-500 text-sm">
            Menampilkan halaman {{ $pagination['currentPage'] }} dari {{ $pagination['totalPages'] }} (Total {{ $pagination['totalCount'] }} karya)
        </div>
    @else
        <div class="mt-16 text-center text-gray-500 text-sm">
            <p>Menampilkan semua {{ count($allPosts) }} karya</p>
        </div>
    @endif
    @else
        <div class="text-center py-20 bg-white rounded-2xl border border-dashed border-gray-300">
            <svg class="w-20 h-20 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500 font-medium text-lg">Belum ada karya yang tersedia</p>
            <p class="text-gray-400 text-sm mt-2">Coba urutkan ulang atau kembali ke beranda</p>
            <a href="{{ route('home') }}" class="inline-block mt-6 px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                Kembali ke Beranda
            </a>
        </div>
    @endif
</div>

@endsection
