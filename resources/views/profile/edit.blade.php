@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Profil</h1>
        <p class="text-gray-600">Perbarui informasi profil Anda</p>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-600 rounded-lg">
        <p class="text-green-700 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
        <p class="text-red-700 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Profile Picture --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <label class="block text-sm font-semibold text-gray-900 mb-4">Foto Profil</label>
            <div class="flex items-center gap-6">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-4xl font-bold text-white">
                    {{ strtoupper(substr($profile['username'] ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1">
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="mt-2 text-xs text-gray-500">JPG, PNG atau GIF. Maksimal 2MB.</p>
                    @error('profile_picture')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
            
            {{-- Full Name (Read Only) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                <input type="text" value="{{ $profile['full_name'] ?? '' }}" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                <p class="mt-1 text-xs text-gray-500">Tidak dapat diubah. Hubungi admin jika ada kesalahan.</p>
            </div>

            {{-- Username (Read Only) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <input type="text" value="{{ '@' . ($profile['username'] ?? '') }}" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                <p class="mt-1 text-xs text-gray-500">Username tidak dapat diubah.</p>
            </div>

            {{-- Email (Read Only) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" value="{{ $profile['email'] ?? '' }}" disabled
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                <p class="mt-1 text-xs text-gray-500">Email tidak dapat diubah.</p>
            </div>
        </div>

        {{-- Editable Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>
            
            {{-- Bio --}}
            <div>
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                <textarea id="bio" name="bio" rows="4"
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none @error('bio') border-red-500 @enderror"
                          placeholder="Ceritakan tentang diri Anda...">{{ old('bio', $profile['bio'] ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Maksimal 500 karakter</p>
                @error('bio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone"
                       value="{{ old('phone', $profile['phone'] ?? $profile['phone_number'] ?? '') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('phone') border-red-500 @enderror"
                       placeholder="08xxxxxxxxxx">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Program Studi --}}
            <div>
                <label for="program_studi_id" class="block text-sm font-medium text-gray-700 mb-2">Program Studi</label>
                <select id="program_studi_id" name="program_studi_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('program_studi_id') border-red-500 @enderror">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach($programStudiList as $prodi)
                        @php
                            $prodiName = $prodi['name'] ?? $prodi['nama_program_studi'] ?? $prodi['program_name'] ?? 'N/A';
                            $prodiFaculty = $prodi['faculty'] ?? $prodi['fakultas'] ?? '';
                        @endphp
                        <option value="{{ $prodi['id'] }}" 
                                {{ old('program_studi_id', $profile['program_studi_id'] ?? '') == $prodi['id'] ? 'selected' : '' }}>
                            {{ $prodiName }}{{ $prodiFaculty ? ' (' . $prodiFaculty . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('program_studi_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Account Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Informasi Akun</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">NIM/NIP:</span>
                    <span class="font-medium text-gray-900">{{ $profile['nid'] ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Role:</span>
                    <span class="font-medium text-gray-900 capitalize">{{ $profile['role'] ?? 'Mahasiswa' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Bergabung:</span>
                    <span class="font-medium text-gray-900">{{ isset($profile['created_at']) ? \Carbon\Carbon::parse($profile['created_at'])->format('d F Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            <button type="submit" 
                    class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold text-center">
                💾 Simpan Perubahan
            </button>
            <a href="{{ route('profile.show', Session::get('user')['id']) }}" 
               class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold text-center">
                Batal
            </a>
        </div>
    </form>

    {{-- Danger Zone --}}
    <div class="mt-8 bg-red-50 border border-red-200 rounded-xl p-6">
        <h3 class="font-semibold text-red-900 mb-2">Danger Zone</h3>
        <p class="text-sm text-red-700 mb-4">
            Tindakan di bawah ini bersifat permanen dan tidak dapat dibatalkan.
        </p>
        <button onclick="alert('Fitur ini akan segera tersedia')" 
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium">
            Hapus Akun
        </button>
    </div>
</div>
@endsection
