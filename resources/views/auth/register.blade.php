@extends('layouts.app')

@section('title', 'Daftar')
@section('full_width', true)

@section('content')
<section class="relative min-h-[calc(100vh-4rem)] bg-slate-50 selection:bg-red-500/30">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-[20%] -left-[10%] h-[70%] w-[50%] rounded-full bg-red-400/10 blur-[120px]"></div>
        <div class="absolute top-[60%] right-[0%] h-[60%] w-[40%] rounded-full bg-rose-400/10 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(0,0,0,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)]"></div>
    </div>

    <div class="relative z-10 grid min-h-[calc(100vh-4rem)] lg:grid-cols-2">
        
        <div class="flex items-center justify-center px-4 py-8 sm:px-8 lg:px-12">
            <div class="w-full max-w-2xl rounded-3xl border border-white bg-white/60 p-6 sm:p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-2xl">
                
                <a href="{{ route('home') }}" class="mb-6 flex items-center gap-3 transition-transform hover:scale-105">
                    <img src="{{ asset('storage/branding/logo1.png') }}" alt="KARYA.UMDP" class="h-10 w-10 object-contain drop-shadow-sm">
                    <span class="text-2xl font-black tracking-tighter text-slate-900">
                        KARYA<span class="text-red-600">.UMDP</span>
                    </span>
                </a>

                <div class="mb-6">
                    <h1 class="text-3xl font-bold tracking-tight text-slate-900">Daftar Akun</h1>
                    <p class="mt-2 text-sm text-slate-500">Mulai unggah dan publikasikan karya terbaik Anda.</p>
                </div>

                @if(session('success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any() && !$errors->has('username') && !$errors->has('email') && !$errors->has('password') && !$errors->has('full_name') && !$errors->has('nid') && !$errors->has('program_studi_id') && !$errors->has('role'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                        <p class="font-bold mb-1">Terjadi kesalahan:</p>
                        <ul class="list-disc list-inside text-xs space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Username --}}
                        <div class="space-y-1.5">
                            <label for="username" class="text-xs font-bold uppercase tracking-wider text-slate-500">Username</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <input id="username" type="text" name="username" value="{{ old('username') }}" required placeholder="username_kamu"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-4 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('username') border-red-500 @enderror">
                            </div>
                            @error('username')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div class="space-y-1.5">
                            <label for="email" class="text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-4 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('email') border-red-500 @enderror">
                            </div>
                            @error('email')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama Lengkap --}}
                        <div class="space-y-1.5">
                            <label for="full_name" class="text-xs font-bold uppercase tracking-wider text-slate-500">Nama Lengkap</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a3 3 0 100-6 3 3 0 000 6zm-7 6a7 7 0 0114 0H3z"/>
                                    </svg>
                                </div>
                                <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Nama Lengkap Sesuai KTP"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-4 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('full_name') border-red-500 @enderror">
                            </div>
                            @error('full_name')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- NPM / NIDN --}}
                        <div class="space-y-1.5">
                            <label for="nid" class="text-xs font-bold uppercase tracking-wider text-slate-500">NPM / NIDN</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <input id="nid" type="text" name="nid" value="{{ old('nid') }}" required placeholder="Nomor Induk Mahasiswa"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-4 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('nid') border-red-500 @enderror">
                            </div>
                            @error('nid')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Program Studi --}}
                        <div class="space-y-1.5">
                            <label for="program_studi_id" class="text-xs font-bold uppercase tracking-wider text-slate-500">Program Studi</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                    </svg>
                                </div>
                                <select id="program_studi_id" name="program_studi_id" required
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-10 text-sm font-medium text-slate-900 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('program_studi_id') border-red-500 @enderror appearance-none">
                                    <option value="" class="text-slate-400">Pilih Program Studi</option>
                                    @foreach($programStudiList as $prodi)
                                        <option value="{{ $prodi['id'] }}" {{ old('program_studi_id') == $prodi['id'] ? 'selected' : '' }}>
                                            {{ $prodi['name'] ?? $prodi['nama_program_studi'] ?? 'Unknown' }}
                                            @if(isset($prodi['code']) || isset($prodi['kode_program_studi']))
                                                - {{ $prodi['code'] ?? $prodi['kode_program_studi'] ?? '' }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('program_studi_id')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Role --}}
                        <div class="space-y-1.5">
                            <label for="role" class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Sebagai</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <select id="role" name="role" required
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-10 text-sm font-medium text-slate-900 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('role') border-red-500 @enderror appearance-none">
                                    <option value="mhs" {{ old('role') == 'mhs' ? 'selected' : '' }}>Mahasiswa</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex w-10 items-center justify-center text-slate-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </div>
                            @error('role')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="space-y-1.5" x-data="{ show: false }">
                            <label for="password" class="text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input id="password" :type="show ? 'text' : 'password'" name="password" required placeholder="••••••••"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-12 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 @error('password') border-red-500 @enderror">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="space-y-1.5" x-data="{ show: false }">
                            <label for="password_confirmation" class="text-xs font-bold uppercase tracking-wider text-slate-500">Konfirmasi Password</label>
                            <div class="relative group">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required placeholder="••••••••"
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-12 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10">
                                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none">
                                    <svg x-show="!show" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="group relative flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-bold text-white transition-all hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/30 focus:outline-none focus:ring-4 focus:ring-red-500/30">
                        <span>Daftar Akun Baru</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm font-medium text-slate-500">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" class="font-bold text-red-600 transition-colors hover:text-red-700">Masuk di sini</a>
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
                    Gabung Komunitas
                </div>
                
                <h2 class="mt-6 text-5xl font-black leading-tight tracking-tighter text-slate-900">
                    Mulai Perjalanan <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-rose-500">Kreatif</span> Anda di Kampus.
                </h2>
                
                <p class="mt-4 text-lg text-slate-600 font-medium leading-relaxed">
                    Daftarkan akun portofolio Anda untuk membagikan riset, proyek pengembangan perangkat lunak, desain kreatif, dan karya inovatif lainnya.
                </p>

                <div class="mt-12 grid grid-cols-3 gap-4">
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white/60 p-5 backdrop-blur-sm transition-all hover:bg-white hover:border-red-300 hover:shadow-md hover:shadow-red-500/5">
                        <div class="text-4xl font-black text-slate-100 transition-colors group-hover:text-red-100">01</div>
                        <div class="mt-2 text-sm font-bold tracking-wide text-slate-800">Portofolio</div>
                    </div>
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white/60 p-5 backdrop-blur-sm transition-all hover:bg-white hover:border-red-300 hover:shadow-md hover:shadow-red-500/5">
                        <div class="text-4xl font-black text-slate-100 transition-colors group-hover:text-red-100">02</div>
                        <div class="mt-2 text-sm font-bold tracking-wide text-slate-800">Apresiasi</div>
                    </div>
                    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white/60 p-5 backdrop-blur-sm transition-all hover:bg-white hover:border-red-300 hover:shadow-md hover:shadow-red-500/5">
                        <div class="text-4xl font-black text-slate-100 transition-colors group-hover:text-red-100">03</div>
                        <div class="mt-2 text-sm font-bold tracking-wide text-slate-800">Jejaring</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
