@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Tambah User Baru</h1>
        <p class="text-gray-500">Buat akun user baru untuk sistem.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 border border-red-200">
            <h3 class="font-bold mb-2">Validation Error:</h3>
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="full_name" class="block text-sm font-semibold text-gray-900 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: Ahmad Rizki" required>
                @error('full_name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="user@example.com" required>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="identifier" class="block text-sm font-semibold text-gray-900 mb-2">
                    Identifier / Username <span class="text-red-500">*</span>
                </label>
                <input type="text" id="identifier" name="identifier" value="{{ old('identifier') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="user123" required>
                @error('identifier')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="••••••••" required minlength="8">
                    @error('password')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-900 mb-2">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="••••••••" required>
                    @error('password_confirmation')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-gray-900 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select id="role" name="role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                    <option value="">-- Pilih Role --</option>
                    <option value="mhs" @selected(old('role') === 'mhs')>Mahasiswa</option>
                    <option value="verifikator" @selected(old('role') === 'verifikator')>Verifikator</option>
                    <option value="dosen" @selected(old('role') === 'dosen')>Dosen</option>
                    <option value="kemahasiswaan" @selected(old('role') === 'kemahasiswaan')>Kemahasiswaan</option>
                    <option value="kaprodi" @selected(old('role') === 'kaprodi')>Kaprodi</option>
                    <option value="superadmin" @selected(old('role') === 'superadmin')>Superadmin</option>
                </select>
                @error('role')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 pt-6">
                <a href="{{ route('admin.users.index') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    Buat User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
