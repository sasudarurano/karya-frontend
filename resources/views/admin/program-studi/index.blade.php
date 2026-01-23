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
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M12 14l9-5-9-5-9 5 9 5z"/>
            <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
        </svg>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Program Studi</h3>
        <p class="text-gray-600 mb-6">Tambahkan program studi pertama Anda</p>
        <a href="{{ route('admin.program-studi.create') }}" class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">
            <span class="mr-2 text-xl">+</span> Tambah Program Studi
        </a>
    </div>
    @endif
</div>
@endsection
