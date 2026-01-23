@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4 max-w-2xl">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
        <p class="text-gray-500">Ubah informasi dan role user.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 border border-red-200">
            {{ session('error') }}
        </div>
    @endif

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
        <form action="{{ route('admin.users.update', $user['id']) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="full_name" class="block text-sm font-semibold text-gray-900 mb-2">
                    Nama Lengkap <span class="text-red-500">*</span>
                </label>
                <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $user['full_name'] ?? '') }}"
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
                <input type="email" id="email" name="email" value="{{ old('email', $user['email'] ?? '') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="user@example.com" required>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">
                    Identifier / Username
                </label>
                <input type="text" value="{{ $user['identifier'] ?? 'N/A' }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600"
                    disabled>
                <p class="text-xs text-gray-500 mt-1">Identifier tidak dapat diubah</p>
            </div>

            <div>
                <label for="role" class="block text-sm font-semibold text-gray-900 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <select id="role" name="role"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required>
                    <option value="">-- Pilih Role --</option>
                    <option value="mhs" @selected(old('role', $user['role'] ?? '') === 'mhs')>Mahasiswa</option>
                    <option value="verifikator" @selected(old('role', $user['role'] ?? '') === 'verifikator')>Verifikator</option>
                    <option value="dosen" @selected(old('role', $user['role'] ?? '') === 'dosen')>Dosen</option>
                    <option value="kemahasiswaan" @selected(old('role', $user['role'] ?? '') === 'kemahasiswaan')>Kemahasiswaan</option>
                    <option value="kaprodi" @selected(old('role', $user['role'] ?? '') === 'kaprodi')>Kaprodi</option>
                    <option value="superadmin" @selected(old('role', $user['role'] ?? '') === 'superadmin')>Superadmin</option>
                </select>
                @error('role')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-gray-600">
                    <strong>Status:</strong>
                    @if($user['is_verified'] ?? false)
                        <span class="text-green-600">✓ Terverifikasi</span>
                    @else
                        <span class="text-yellow-600">○ Belum Terverifikasi</span>
                    @endif
                </p>
            </div>

            <div class="flex gap-3 pt-6">
                <a href="{{ route('admin.users.index') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-center font-semibold text-gray-900 hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
