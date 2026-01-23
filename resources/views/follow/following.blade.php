@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('profile.show', $userId) }}" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            ← Kembali ke Profil
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Following</h1>
        <p class="text-gray-600">Daftar pengguna yang diikuti</p>
    </div>

    @if(count($following) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        @foreach($following as $user)
        <div class="p-6 hover:bg-gray-50 transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    {{-- Avatar --}}
                    <a href="{{ route('profile.show', $user['id']) }}">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-full flex items-center justify-center text-2xl font-bold text-white">
                            {{ strtoupper(substr($user['username'] ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                    
                    {{-- User Info --}}
                    <div>
                        <a href="{{ route('profile.show', $user['id']) }}" class="font-bold text-gray-900 hover:text-blue-600 transition">
                            {{ $user['full_name'] ?? $user['username'] }}
                        </a>
                        <p class="text-sm text-gray-500">@<span>{{ $user['username'] }}</span></p>
                        @if(!empty($user['bio']))
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $user['bio'] }}</p>
                        @endif
                    </div>
                </div>

                {{-- Unfollow Button --}}
                @if(Session::has('user') && Session::get('user')['id'] !== $user['id'])
                <button onclick="toggleFollow({{ $user['id'] }}, this)" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition font-medium text-sm">
                    Following
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Belum Mengikuti Siapapun</h3>
        <p class="text-gray-500">User ini belum mengikuti pengguna lain</p>
    </div>
    @endif
</div>

<script>
function toggleFollow(userId, button) {
    const isFollowing = button.textContent.trim() === 'Following';
    
    const url = isFollowing ? `/users/${userId}/unfollow` : `/users/${userId}/follow`;
    const method = isFollowing ? 'DELETE' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isFollowing) {
                button.textContent = 'Follow';
                button.classList.remove('bg-gray-200', 'text-gray-700');
                button.classList.add('bg-blue-600', 'text-white');
            } else {
                button.textContent = 'Following';
                button.classList.remove('bg-blue-600', 'text-white');
                button.classList.add('bg-gray-200', 'text-gray-700');
            }
        }
    });
}
</script>

@endsection
