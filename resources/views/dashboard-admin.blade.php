@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#f8fafc] py-10">
    <div class="container mx-auto px-4 lg:px-12">
        
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-blue-600 font-bold text-sm tracking-widest uppercase">
                    <span class="h-1 w-8 bg-blue-600 rounded-full"></span>
                    Central Administration
                </div>
                <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">
                    Admin <span class="text-slate-400 font-light">/</span> Verifikator
                </h1>
                <p class="text-slate-500 text-lg">Panel kurasi konten dan manajemen operasional platform.</p>
            </div>
            
            <div class="flex flex-col gap-3">
                @if(in_array(strtolower(session('user.role', '')), ['verifikator', 'dosen pembimbing', 'dosen_pembimbing', 'kaprodi', 'kemahasiswaan']))
                <a href="{{ route('dashboard.feed') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    Ke Dashboard Pengguna
                </a>
                @endif
                <div class="bg-white p-1 rounded-2xl shadow-sm border border-slate-200">
                    <div class="px-4 py-2 text-right">
                        <p class="text-xs text-slate-400 font-medium uppercase">Petugas Sesi Ini</p>
                        <p class="text-sm font-bold text-slate-800">{{ session('user.full_name', 'Admin Staff') }}</p>
                    </div>
                    <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center m-auto mr-2">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/60 shadow-sm overflow-hidden relative group">
                <div class="absolute -right-4 -bottom-4 text-blue-50 opacity-10 group-hover:scale-110 transition-transform">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>
                </div>
                <p class="text-sm font-semibold text-slate-400 uppercase mb-1">Otoritas</p>
                <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ strtoupper(session('user.role', 'Admin')) }}</h3>
                <div class="mt-4 flex items-center gap-2 text-xs font-medium text-blue-600 bg-blue-50 w-fit px-3 py-1 rounded-full">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    Verified Session
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-200/60 shadow-sm">
                <p class="text-sm font-semibold text-slate-400 uppercase mb-1">Modul Akses</p>
                @if(strtolower(session('user.role', '')) === 'verifikator')
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">01 <span class="text-lg font-normal text-slate-400">Main Module</span></h3>
                    <div class="mt-4 flex -space-x-2">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center text-indigo-600 text-xs font-bold">M</div>
                    </div>
                @else
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">02 <span class="text-lg font-normal text-slate-400">Main Modules</span></h3>
                    <div class="mt-4 flex -space-x-2">
                        <div class="h-8 w-8 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center text-indigo-600 text-xs font-bold">M</div>
                        <div class="h-8 w-8 rounded-full bg-emerald-100 border-2 border-white flex items-center justify-center text-emerald-600 text-xs font-bold">P</div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-200/60 shadow-sm group">
                <p class="text-sm font-semibold text-slate-400 uppercase mb-1">Status Server</p>
                <div class="flex items-center gap-3">
                    <h3 class="text-2xl font-black text-emerald-500 tracking-tight italic">Operational</h3>
                </div>
                <p class="mt-4 text-xs text-slate-400 font-medium">Sistem dipantau secara real-time</p>
            </div>
        </div>

        <div class="mb-12">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mb-6 px-1">Aksi Cepat Manajemen</h2>
            <div class="grid grid-cols-1 @if(strtolower(session('user.role', '')) === 'verifikator') grid-cols-1 @else md:grid-cols-2 @endif gap-6">
                <a href="{{ route('admin.posts.index') }}" class="group relative bg-white border border-slate-200 rounded-[2rem] p-8 transition-all hover:shadow-2xl hover:shadow-blue-100 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="space-y-4">
                            <div class="h-14 w-14 rounded-2xl bg-blue-600 flex items-center justify-center shadow-lg shadow-blue-200 text-white group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-900">Moderasi Karya</h3>
                                <p class="text-slate-500 mt-1 leading-relaxed">Verifikasi kualitas dan kelayakan karya mahasiswa sebelum dipublikasi.</p>
                            </div>
                        </div>
                        <div class="text-slate-300 group-hover:text-blue-600 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </div>
                </a>

                {{-- Program Studi Menu - hanya untuk admin/kaprodi/kemahasiswaan, sembunyikan untuk verifikator --}}
                @if(strtolower(session('user.role', '')) !== 'verifikator')
                <a href="{{ route('admin.program-studi.index') }}" class="group relative bg-white border border-slate-200 rounded-[2rem] p-8 transition-all hover:shadow-2xl hover:shadow-indigo-100 hover:-translate-y-1">
                    <div class="flex items-start justify-between">
                        <div class="space-y-4">
                            <div class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-200 text-white group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-slate-900">Program Studi</h3>
                                <p class="text-slate-500 mt-1 leading-relaxed">Kelola struktur data fakultas dan departemen di lingkungan kampus.</p>
                            </div>
                        </div>
                        <div class="text-slate-300 group-hover:text-indigo-600 transition-colors">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                        </div>
                    </div>
                </a>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-6">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    Lingkup Pekerjaan Anda
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 flex gap-4 items-start">
                        <div class="p-2 bg-orange-50 rounded-lg text-orange-600 italic font-bold">01</div>
                        <div>
                            <h4 class="font-bold text-slate-800">Quality Control</h4>
                            <p class="text-sm text-slate-500 mt-1">Menjamin setiap karya memenuhi standar publikasi institusi.</p>
                        </div>
                    </div>
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 flex gap-4 items-start">
                        <div class="p-2 bg-purple-50 rounded-lg text-purple-600 italic font-bold">02</div>
                        <div>
                            <h4 class="font-bold text-slate-800">Audit Trail</h4>
                            <p class="text-sm text-slate-500 mt-1">Setiap persetujuan Anda akan tercatat dalam sejarah sistem.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <h2 class="text-lg font-bold text-slate-800">Navigasi User</h2>
                <div class="bg-slate-900 rounded-[2rem] p-8 text-white relative overflow-hidden group">
                    <div class="relative z-10">
                        <p class="text-slate-400 text-sm mb-4">Ingin melihat tampilan publik?</p>
                        <a href="{{ route('dashboard.feed') }}" class="flex items-center justify-between group-hover:text-blue-400 transition-colors font-bold">
                            Buka Feed Utama
                            <svg class="w-5 h-5 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                        </a>
                    </div>
                    <div class="absolute -right-8 -bottom-8 text-white/5 group-hover:rotate-12 transition-transform">
                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM15 13a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2h-2zM15 3a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5a2 2 0 012-2h2z"></path></svg>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection