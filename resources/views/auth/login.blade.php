@extends('layouts.app')

@section('title', 'Masuk')
@section('full_width', true)

@section('content')
<section class="relative min-h-[calc(100vh-4rem)] bg-slate-50 selection:bg-red-500/30">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-[20%] -left-[10%] h-[70%] w-[50%] rounded-full bg-red-400/10 blur-[120px]"></div>
        <div class="absolute top-[60%] right-[0%] h-[60%] w-[40%] rounded-full bg-rose-400/10 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(0,0,0,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)]"></div>
    </div>

    <div class="relative z-10 grid min-h-[calc(100vh-4rem)] lg:grid-cols-2">
        
        <div class="flex items-center justify-center px-6 py-12 sm:px-12 lg:px-16">
            <div class="w-full max-w-sm rounded-3xl border border-white bg-white/60 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-2xl">
                
                <a href="{{ route('home') }}" class="mb-10 flex items-center gap-3 transition-transform hover:scale-105">
                    <img src="{{ asset('storage/branding/logo1.png') }}" alt="KARYA.UMDP" class="h-10 w-10 object-contain drop-shadow-sm">
                    <span class="text-2xl font-black tracking-tighter text-slate-900">
                        KARYA<span class="text-red-600">.UMDP</span>
                    </span>
                </a>

                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Selamat Datang</h1>
                    <p class="mt-2 text-sm text-slate-500">Akses portal portofolio karya mahasiswa.</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error') || $errors->any())
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ session('error') ?? $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="space-y-2">
                        <label for="identifier" class="text-xs font-bold uppercase tracking-wider text-slate-500">Email / Username</label>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-4.5 7.794"/>
                                </svg>
                            </div>
                            <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" required autocomplete="username" placeholder="email@mdp.ac.id"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-16 pr-4 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10">
                        </div>
                    </div>

                    <div class="space-y-2" x-data="{ show: false }">
                        <div class="flex items-center justify-between">
                            <label for="password" class="text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
                            <a href="{{ route('forgot-password') }}" class="text-xs font-bold text-red-600 transition-colors hover:text-red-700 hover:underline">Lupa?</a>
                        </div>
                        <div class="relative group">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-16 pr-14 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10">
                            
                            <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                                <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="group relative flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-bold text-white transition-all hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/30 focus:outline-none focus:ring-4 focus:ring-red-500/30">
                        <span>Otorisasi Akses</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-8 text-center">
                    <p class="text-sm font-medium text-slate-500">
                        Belum terdaftar? 
                        <a href="{{ route('register') }}" class="font-bold text-red-600 transition-colors hover:text-red-700">Buat Akun</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="relative hidden flex-col justify-center p-12 lg:flex">
            <div class="relative z-10 w-full max-w-lg">
                <div class="inline-flex items-center gap-2 rounded-full border border-red-200 bg-red-50 px-3 py-1 text-[10px] font-bold uppercase tracking-widest text-red-600">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-red-600"></span>
                    </span>
                    Sistem Aktif
                </div>
                
                <h2 class="mt-6 text-5xl font-black leading-tight tracking-tighter text-slate-900">
                    Jelajahi <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-500">Masa Depan</span> Karya Mahasiswa.
                </h2>
                
                <p class="mt-4 text-lg text-slate-600 font-medium leading-relaxed">
                    Etalase digital terintegrasi untuk menemukan, memvalidasi, dan mengapresiasi inovasi terbaik di lingkungan kampus.
                </p>

                <div class="mt-12 grid grid-cols-3 gap-4">
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white/60 p-5 backdrop-blur-sm transition-all hover:bg-white hover:border-red-300 hover:shadow-md hover:shadow-red-500/5">
                        <div class="text-4xl font-black text-slate-100 transition-colors group-hover:text-red-100">01</div>
                        <div class="mt-2 text-sm font-bold tracking-wide text-slate-800">Unggah</div>
                    </div>
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white/60 p-5 backdrop-blur-sm transition-all hover:bg-white hover:border-red-300 hover:shadow-md hover:shadow-red-500/5">
                        <div class="text-4xl font-black text-slate-100 transition-colors group-hover:text-red-100">02</div>
                        <div class="mt-2 text-sm font-bold tracking-wide text-slate-800">Validasi</div>
                    </div>
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white/60 p-5 backdrop-blur-sm transition-all hover:bg-white hover:border-red-300 hover:shadow-md hover:shadow-red-500/5">
                        <div class="text-4xl font-black text-slate-100 transition-colors group-hover:text-red-100">03</div>
                        <div class="mt-2 text-sm font-bold tracking-wide text-slate-800">Publikasi</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection