@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 to-indigo-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl">
        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900">
                Daftar Akun Baru
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    Login di sini
                </a>
            </p>
        </div>

        @if(session('success'))
        <div class="p-4 bg-green-50 border-l-4 border-green-600 rounded-lg">
            <p class="text-green-700 font-medium">{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
            <p class="text-red-700 font-medium">{{ session('error') }}</p>
        </div>
        @endif

        @if($errors->any() && !$errors->has('username') && !$errors->has('email') && !$errors->has('password') && !$errors->has('full_name') && !$errors->has('nid') && !$errors->has('program_studi_id') && !$errors->has('role'))
        <div class="p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
            <p class="text-red-700 font-bold mb-2">Terjadi kesalahan:</p>
            <ul class="text-red-600 text-sm list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                {{-- Username --}}
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input id="username" name="username" type="text" required
                           value="{{ old('username') }}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('username') border-red-500 @enderror"
                           placeholder="Username unik Anda">
                    @error('username')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" name="email" type="email" required
                           value="{{ old('email') }}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-500 @enderror"
                           placeholder="email@example.com">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Full Name --}}
                <div>
                    <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input id="full_name" name="full_name" type="text" required
                           value="{{ old('full_name') }}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('full_name') border-red-500 @enderror"
                           placeholder="Nama lengkap sesuai KTP">
                    @error('full_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- NID (NPM/NID) --}}
                <div>
                    <label for="nid" class="block text-sm font-medium text-gray-700 mb-1">NPM /NIDN</label>
                    <input id="nid" name="nid" type="text" required
                           value="{{ old('nid') }}"
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('nid') border-red-500 @enderror"
                           placeholder="Nomor Induk Mahasiswa/Pegawai">
                    @error('nid')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Program Studi --}}
                <div>
                    <label for="program_studi_id" class="block text-sm font-medium text-gray-700 mb-1">Program Studi</label>
                    <select id="program_studi_id" name="program_studi_id" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('program_studi_id') border-red-500 @enderror">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($programStudiList as $prodi)
                            @php
                                // Extract ID - bisa berupa integer atau string yang berisi angka
                                $prodiId = $prodi['id'] ?? '';
                                // Jika ID berupa string dengan text, extract angkanya
                                if (is_string($prodiId)) {
                                    preg_match('/\d+/', $prodiId, $matches);
                                    $prodiId = $matches[0] ?? $prodiId;
                                }
                                $prodiId = (int) $prodiId;
                            @endphp
                            <option value="{{ $prodiId }}" {{ old('program_studi_id') == $prodiId ? 'selected' : '' }}>
                                {{ $prodi['name'] ?? $prodi['nama_program_studi'] ?? 'Unknown' }}
                                @if(isset($prodi['code']) || isset($prodi['kode_program_studi']))
                                    - {{ $prodi['code'] ?? $prodi['kode_program_studi'] ?? '' }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('program_studi_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Role --}}
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Daftar Sebagai</label>
                    <select id="role" name="role" required
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-500 @enderror">
                        <option value="mhs" {{ old('role') == 'mhs' ? 'selected' : '' }}>Mahasiswa</option>
                        <!-- <option value="dosen" {{ old('role') == 'dosen' ? 'selected' : '' }}>Dosen</option> -->
                    </select>
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Untuk role lainnya, hubungi administrator</p>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" name="password" type="password" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-500 @enderror"
                           placeholder="Minimal 6 karakter">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Confirmation --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="appearance-none relative block w-full px-3 py-2 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="Ketik ulang password">
                </div>
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    Daftar Sekarang
                </button>
            </div>
        </form>

        <div class="text-center text-xs text-gray-500 mt-4">
            Dengan mendaftar, Anda menyetujui <a href="#" class="text-blue-600 hover:underline">Syarat & Ketentuan</a> kami
        </div>
    </div>
</div>
@endsection
