@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('admin.program-studi.index') }}" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            ← Kembali ke Daftar
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Tambah Program Studi</h1>
        <p class="text-gray-600">Tambahkan program studi baru</p>
    </div>

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-600 rounded-lg">
        <p class="text-red-700 font-medium">{{ session('error') }}</p>
        @if($errors->any())
            <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif

    <form action="{{ route('admin.program-studi.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Kode Program Studi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <label for="code" class="block text-sm font-semibold text-gray-900 mb-2">
                Kode Program Studi <span class="text-red-500">*</span>
            </label>
            <input type="text" id="code" name="code" required
                   value="{{ old('code') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono @error('code') border-red-500 @enderror"
                   placeholder="Contoh: IF, SI, TI">
            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">Kode singkat untuk identifikasi program studi</p>
        </div>

        {{-- Nama Program Studi --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <label for="name" class="block text-sm font-semibold text-gray-900 mb-2">
                Nama Program Studi <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" required
                   value="{{ old('name') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                   placeholder="Contoh: Teknik Informatika">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fakultas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <label for="faculty" class="block text-sm font-semibold text-gray-900 mb-2">
                Fakultas <span class="text-red-500">*</span>
            </label>
            <input type="text" id="faculty" name="faculty" required
                   value="{{ old('faculty') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('faculty') border-red-500 @enderror"
                   placeholder="Contoh: Fakultas Teknik">
            @error('faculty')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Deskripsi (Opsional) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <label for="description" class="block text-sm font-semibold text-gray-900 mb-2">
                Deskripsi
            </label>
            <textarea id="description" name="description" rows="4"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                      placeholder="Deskripsi singkat tentang program studi...">{{ old('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="mt-2 text-xs text-gray-500">Opsional - Informasi tambahan tentang program studi</p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            <button type="submit" 
                    class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold text-center">
                💾 Simpan
            </button>
            <a href="{{ route('admin.program-studi.index') }}" 
               class="flex-1 bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition font-semibold text-center">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
