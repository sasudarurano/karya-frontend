@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('profile.show', $userId) }}" class="text-blue-600 hover:underline text-sm mb-2 inline-block">
            ← Kembali ke Profil
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Followers</h1>
        <p class="text-gray-600">Daftar pengguna yang mengikuti</p>
    </div>

    @if(count($followers) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
        @foreach($followers as $follower)
        <div class="p-6 hover:bg-gray-50 transition">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    {{-- Avatar --}}
                    <a href="{{ route('profile.show', $follower['id']) }}">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-2xl font-bold text-white">
                            {{ strtoupper(substr($follower['username'] ?? 'U', 0, 1)) }}
                        </div>
                    </a>
                    
                    {{-- User Info --}}
                    <div>
                        <a href="{{ route('profile.show', $follower['id']) }}" class="font-bold text-gray-900 hover:text-blue-600 transition">
                            {{ $follower['full_name'] ?? $follower['username'] }}
                        </a>
                        <p class="text-sm text-gray-500">@<span>{{ $follower['username'] }}</span></p>
                        @if(!empty($follower['bio']))
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $follower['bio'] }}</p>
                        @endif
                    </div>
                </div>

                {{-- Follow Button --}}
                @if(Session::has('user') && Session::get('user')['id'] !== $follower['id'])
                <button onclick="toggleFollow({{ $follower['id'] }}, this)" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm">
                    Follow
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <h3 class="text-lg font-bold text-gray-900 mb-2">Belum Ada Followers</h3>
        <p class="text-gray-500">User ini belum memiliki followers</p>
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
