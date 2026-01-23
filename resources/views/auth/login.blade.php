@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-[60vh]">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Login Karya</h2>
        
        @if($errors->any())
            <div class="bg-red-50 text-red-600 p-3 rounded mb-4 text-sm border border-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email atau Username</label>
                <input type="text" 
                       name="identifier" 
                       value="{{ old('identifier') }}"
                       required 
                       placeholder="admin@example.com"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" 
                       name="password" 
                       required 
                       placeholder="••••••"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('forgot-password') }}" class="text-sm text-blue-600 hover:underline">
                    Lupa Password?
                </a>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-500/30 cursor-pointer">
                Masuk
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-500">
            Belum punya akun? <a href="#" class="text-blue-600 hover:underline">Daftar sekarang</a>
        </div>
    </div>
</div>
@endsection