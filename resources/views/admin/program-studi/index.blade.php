@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Manajemen Program Studi</h1>
            <p class="text-gray-600">Kelola data program studi dan fakultas</p>
        </div>
        <a href="{{ route('admin.program-studi.create') }}" class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
            <span class="mr-2 text-xl">+</span> Tambah Program Studi
        </a>
    </div>

    {{-- Search Bar --}}
    <div class="mb-8">
        <form action="{{ route('admin.program-studi.index') }}" method="GET" class="relative max-w-md w-full">
            <input type="text" 
                   name="search" 
                   value="{{ $search ?? '' }}" 
                   placeholder="Cari kode atau nama program studi..." 
                   class="w-full pl-10 pr-10 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
            <div class="absolute left-4 top-3.5 text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            @if($search)
            <a href="{{ route('admin.program-studi.index') }}" class="absolute right-4 top-3.5 text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
            @endif
        </form>
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

    @if(count($programStudiList) > 0)
    <div class="space-y-4">
        @foreach($programStudiList as $prodi)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Kode</p>
                    <span class="px-3 py-1 text-sm font-bold bg-blue-100 text-blue-800 rounded-lg inline-block">
                        {{ $prodi['code'] ?? $prodi['kode_program_studi'] ?? '-' }}
                    </span>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Nama Program Studi</p>
                    <p class="text-base font-bold text-gray-900">{{ $prodi['name'] ?? $prodi['nama_program_studi'] ?? '-' }}</p>
                </div>
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.program-studi.edit', $prodi['id']) }}" 
                       class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition font-medium text-sm">
                        ✏️ Edit
                    </a>
                    <form action="{{ route('admin.program-studi.destroy', $prodi['id']) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus program studi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition font-medium text-sm">
                            🗑️ Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-16 text-center">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-2">
            @if($search)
                Hasil tidak ditemukan
            @else
                Belum Ada Program Studi
            @endif
        </h3>
        <p class="text-gray-600 mb-6">
            @if($search)
                Tidak ada program studi yang cocok dengan kata kunci "{{ $search }}"
            @else
                Tambahkan program studi pertama Anda
            @endif
        </p>
        @if($search)
            <a href="{{ route('admin.program-studi.index') }}" class="text-blue-600 font-semibold hover:underline">
                Reset Pencarian
            </a>
        @else
            <a href="{{ route('admin.program-studi.create') }}" class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
                <span class="mr-2 text-xl">+</span> Tambah Program Studi
            </a>
        @endif
    </div>
    @endif

    {{-- Pagination Links --}}
    @if(isset($meta) && isset($meta['last_page']) && $meta['last_page'] > 1)
    <div class="mt-10 flex flex-col md:flex-row justify-between items-center gap-4">
        <p class="text-sm text-gray-500">
            Menampilkan <span class="font-semibold text-gray-700">{{ count($programStudiList) }}</span> dari <span class="font-semibold text-gray-700">{{ $meta['total'] }}</span> data
        </p>
        <div class="flex items-center gap-2">
            @if($meta['page'] > 1)
            <a href="{{ route('admin.program-studi.index', array_merge(request()->query(), ['page' => $meta['page'] - 1])) }}" 
               class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition shadow-sm">
                Sebelumnya
            </a>
            @else
            <button class="px-4 py-2 bg-gray-50 border border-gray-100 rounded-lg text-sm font-medium text-gray-300 cursor-not-allowed shadow-none" disabled>
                Sebelumnya
            </button>
            @endif

            <div class="flex items-center px-4 py-2 bg-blue-50 rounded-lg text-sm font-bold text-blue-700 border border-blue-100">
                {{ $meta['page'] }} / {{ $meta['last_page'] }}
            </div>

            @if($meta['page'] < $meta['last_page'])
            <a href="{{ route('admin.program-studi.index', array_merge(request()->query(), ['page' => $meta['page'] + 1])) }}" 
               class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-blue-600 transition shadow-sm">
                Selanjutnya
            </a>
            @else
            <button class="px-4 py-2 bg-gray-50 border border-gray-100 rounded-lg text-sm font-medium text-gray-300 cursor-not-allowed shadow-none" disabled>
                Selanjutnya
            </button>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
