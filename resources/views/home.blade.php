@extends('layouts.app')

@section('content')

{{-- Hero Section dengan Background Modern --}}
<div class="relative bg-slate-900 rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-200/50 mb-16 isolate">
    {{-- Decorative Background Elements --}}
    <div class="absolute inset-0 bg-gradient-to-br from-indigo-900/40 via-slate-900 to-slate-900 z-0"></div>
    <div class="absolute top-0 right-0 -mt-20 -mr-20 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
    <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
    
    <div class="relative px-6 py-24 md:py-28 text-center z-10 max-w-5xl mx-auto">
        {{-- Badge --}}
        <div class="inline-flex items-center gap-2 py-1.5 px-4 rounded-full bg-slate-800/50 border border-slate-700 backdrop-blur-md text-blue-300 text-xs font-bold uppercase tracking-wider mb-8 shadow-lg">
            <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
            Platform Karya Mahasiswa UMDP
        </div>

        {{-- Headline --}}
        <h1 class="text-5xl md:text-7xl font-black text-white mb-6 tracking-tight leading-tight">
            Tunjukkan Karyamu, <br class="hidden md:block" />
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-cyan-300 to-emerald-300">
                Inspirasi Dunia.
            </span>
        </h1>
        
        <p class="text-slate-300 text-lg md:text-xl max-w-2xl mx-auto mb-12 font-light leading-relaxed">
            Eksplorasi ribuan inovasi, tugas akhir, dan proyek kreatif dari talenta terbaik Universitas Multi Data Palembang dalam satu wadah.
        </p>

        {{-- Search Form --}}
        <form action="{{ route('home') }}" method="GET" class="max-w-3xl mx-auto relative group mb-10">
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
            <div class="relative flex items-center bg-white rounded-2xl shadow-2xl p-2">
                <div class="pl-4 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" placeholder="Cari judul skripsi, nama dosen, atau topik..." 
                       value="{{ request('search') }}"
                       class="w-full px-4 py-3 bg-transparent border-none focus:ring-0 text-slate-800 placeholder-slate-400 text-lg rounded-xl">
                <button type="submit" class="hidden md:block bg-slate-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-blue-600 transition-all duration-300 shadow-lg hover:shadow-blue-500/30">
                    Cari Karya
                </button>
            </div>
        </form>

        {{-- Quick Filters / Tags --}}
        <div class="flex flex-wrap justify-center items-center gap-3">
            <span class="text-slate-400 text-sm font-medium mr-1">Sedang Tren:</span>
            @php
                $categories = [
                    ['label' => '🏢 Magang', 'val' => 'kp/magang'],
                    ['label' => '🔬 Penelitian', 'val' => 'penelitian/pkm'],
                    ['label' => '🏆 Lomba', 'val' => 'lomba'],
                    ['label' => '🚀 Proyek', 'val' => 'project mandiri']
                ];
            @endphp
            @foreach($categories as $cat)
                <a href="{{ route('home', ['category' => $cat['val']]) }}" 
                   class="px-4 py-1.5 rounded-full bg-slate-800/40 text-slate-300 hover:bg-blue-500 hover:text-white border border-slate-700 hover:border-blue-400 transition-all duration-300 text-sm font-medium backdrop-blur-sm">
                    {{ $cat['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>

<div class="space-y-20">
    
    {{-- Section: Paling Populer --}}
    <section>
        <div class="flex items-end justify-between mb-10 px-2">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-orange-100 text-orange-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z" /></svg>
                    </span>
                    Paling Populer
                </h2>
                <p class="text-slate-500 mt-2 ml-14 text-sm font-medium">Karya yang paling banyak dilihat dan disukai bulan ini.</p>
            </div>
            <a href="{{ route('home', ['sort' => 'popular']) }}" class="group flex items-center gap-1 text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
                Lihat Semua
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>

        @if(!empty($popularPosts) && count($popularPosts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($popularPosts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 px-4 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Belum ada yang populer</h3>
                <p class="text-slate-500 max-w-sm mt-1">Jadilah yang pertama membuat karya hebat dan populerkan karyamu!</p>
            </div>
        @endif
    </section>

    {{-- Section: Baru Diupload --}}
    <section>
        <div class="flex items-end justify-between mb-10 px-2">
            <div>
                <h2 class="text-3xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                    <span class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-100 text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                    </span>
                    Terbaru
                </h2>
                <p class="text-slate-500 mt-2 ml-14 text-sm font-medium">Inovasi fresh yang baru saja diunggah mahasiswa.</p>
            </div>
            <a href="{{ route('home', ['sort' => 'newest']) }}" class="group flex items-center gap-1 text-sm font-bold text-blue-600 hover:text-blue-800 transition-colors">
                Lihat Semua
                <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>

        @if(!empty($newestPosts) && count($newestPosts) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($newestPosts as $post)
                    <x-post-card :post="$post" />
                @endforeach
            </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 px-4 bg-slate-50 rounded-3xl border-2 border-dashed border-slate-200 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-slate-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-slate-700">Belum ada karya terbaru</h3>
                <p class="text-slate-500 max-w-sm mt-1">Saat ini belum ada karya baru yang diunggah.</p>
            </div>
        @endif
    </section>

    {{-- CTA / Bottom Section --}}
    @if(!session('user'))
    <section class="relative bg-blue-600 rounded-3xl overflow-hidden py-16 px-8 text-center">
        <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative z-10 max-w-2xl mx-auto">
            <h2 class="text-3xl font-bold text-white mb-4">Punya Karya Hebat yang Ingin Dibagikan?</h2>
            <p class="text-blue-100 mb-8 text-lg">Jangan biarkan karyamu hanya tersimpan di laptop. Publikasikan sekarang dan bangun portofolio digitalmu.</p>
            <a href="{{ route('login') }}" class="inline-block bg-white text-blue-600 font-bold py-3 px-8 rounded-xl shadow-lg hover:bg-blue-50 transition transform hover:-translate-y-1">
                Login untuk Upload
            </a>
        </div>
    </section>
    @endif

</div>

{{-- Custom CSS untuk animasi blobs --}}
<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
</style>

@endsection