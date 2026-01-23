@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-50 via-blue-50 to-indigo-100 py-10">
    <div class="container mx-auto px-4 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-12">
            <div class="flex items-center gap-5">
                <div class="relative">
                    <div class="h-16 w-16 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500 border-2 border-white"></span>
                    </span>
                </div>
                <div>
                    <nav class="flex mb-1" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-xs font-semibold uppercase tracking-widest text-indigo-600">
                            <li>System</li>
                            <li><svg class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></li>
                            <li class="text-gray-500">Root Access</li>
                        </ol>
                    </nav>
                    <h1 class="text-4xl font-extrabold text-slate-900 tracking-tight">Superadmin Dashboard</h1>
                    <p class="text-slate-500 font-medium">Selamat datang kembali, <span class="text-indigo-600">{{ session('user.full_name', 'Administrator') }}</span></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard.feed') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition-all shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    View Feed
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @php
                $stats = [
                    ['label' => 'Current Role', 'val' => strtoupper(session('user.role', 'Super')), 'sub' => 'Full Authority', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'color' => 'indigo'],
                    ['label' => 'Active Modules', 'val' => str_pad($activeModules['count'], 2, '0', STR_PAD_LEFT), 'sub' => $activeModules['status'], 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z', 'color' => 'blue'],
                    ['label' => 'System Status', 'val' => $systemStatus['status'], 'sub' => $systemStatus['message'], 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'color' => $systemStatus['color']],
                    ['label' => 'Security Log', 'val' => $securityInfo['status'], 'sub' => $securityInfo['message'], 'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'color' => 'amber'],
                ];
            @endphp

            @foreach($stats as $stat)
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white p-6 shadow-sm hover:shadow-md transition-all">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-2xl bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $stat['label'] }}</span>
                </div>
                <h3 class="text-2xl font-bold text-slate-800">{{ $stat['val'] }}</h3>
                <p class="text-sm text-slate-500">{{ $stat['sub'] }}</p>
            </div>
            @endforeach
        </div>

        <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-3">
            <span class="h-8 w-1.5 rounded-full bg-indigo-600"></span>
            Management Hub
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <a href="{{ route('admin.posts.index') }}" class="group relative bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:border-blue-200 transition-all overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-blue-200 group-hover:rotate-6 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Moderasi Karya</h3>
                    <p class="text-slate-500 leading-relaxed mb-6">Tinjau, setujui, atau moderasi konten karya mahasiswa yang masuk ke sistem.</p>
                    <span class="flex items-center text-blue-600 font-bold group-hover:gap-3 transition-all">
                        Kelola Sekarang <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.program-studi.index') }}" class="group relative bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:border-indigo-200 transition-all overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-indigo-200 group-hover:rotate-6 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Program Studi</h3>
                    <p class="text-slate-500 leading-relaxed mb-6">Konfigurasi data departemen dan program studi akademik yang terdaftar.</p>
                    <span class="flex items-center text-indigo-600 font-bold group-hover:gap-3 transition-all">
                        Konfigurasi <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </span>
                </div>
            </a>

            <a href="{{ route('admin.users.index') }}" class="group relative bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 hover:border-purple-200 transition-all overflow-hidden">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-50 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-14 h-14 bg-purple-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-purple-200 group-hover:rotate-6 transition-transform">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <h3 class="text-2xl font-bold text-slate-900 mb-2">Manajemen User</h3>
                    <p class="text-slate-500 leading-relaxed mb-6">Kelola hak akses pengguna, ganti role, dan monitoring aktivitas user.</p>
                    <span class="flex items-center text-purple-600 font-bold group-hover:gap-3 transition-all">
                        Atur Pengguna <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </span>
                </div>
            </a>
        </div>

        <div class="bg-slate-900 rounded-[3rem] p-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 p-10 opacity-10">
                <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
            </div>
            <div class="relative z-10 grid md:grid-cols-2 gap-10 items-center">
                <div>
                    <h3 class="text-2xl font-bold mb-4 italic text-indigo-400">#SuperadminPrivilege</h3>
                    <p class="text-slate-400 leading-relaxed mb-6 text-lg">
                        Anda berada dalam mode kontrol penuh. Setiap perubahan pada basis data dan konfigurasi sistem akan dicatat dalam audit log untuk keamanan bersama.
                    </p>
                    <div class="flex gap-4">
                        <div class="px-4 py-2 bg-white/10 rounded-full text-xs font-mono">Server: Laravel v12.x</div>
                        <div class="px-4 py-2 bg-white/10 rounded-full text-xs font-mono">Environment: Testing</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-6 bg-white/5 rounded-3xl border border-white/10">
                        <p class="text-slate-400 text-sm mb-1">Database Status</p>
                        <p class="font-bold {{ $dbStatus['isConnected'] ? 'text-emerald-400' : 'text-rose-400' }}">
                            {{ $dbStatus['status'] }}
                        </p>
                    </div>
                    <div class="p-6 bg-white/5 rounded-3xl border border-white/10">
                        <p class="text-slate-400 text-sm mb-1">Last Update</p>
                        <p class="font-bold">{{ $lastUpdate }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection