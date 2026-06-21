@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50/50 py-12">
    <div class="container mx-auto px-4 lg:px-8">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen User</h1>
                <p class="text-slate-500 mt-1 font-medium">Otorisasi akses dan manajemen peran pengguna sistem.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition-all shadow-lg shadow-blue-200 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Tambah User Baru
            </a>
        </div>

        {{-- Search Bar --}}
        <div class="mb-8">
            <form action="{{ route('admin.users.index') }}" method="GET" class="relative max-w-md w-full">
                <input type="text" 
                       name="search" 
                       value="{{ $search ?? '' }}" 
                       placeholder="Cari nama, email, atau username..." 
                       class="w-full pl-10 pr-10 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm font-medium text-slate-700">
                <div class="absolute left-4 top-3.5 text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                @if($search)
                <a href="{{ route('admin.users.index') }}" class="absolute right-4 top-3.5 text-slate-400 hover:text-slate-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </a>
                @endif
            </form>
        </div>

        {{-- Modal Component --}}
        <div id="notificationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeModal()">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all" onclick="event.stopPropagation()">
                <div id="modalHeader" class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 id="modalTitle" class="text-lg font-bold text-gray-900"></h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div id="modalBody" class="px-6 py-6">
                    <div id="modalIcon" class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full"></div>
                    <p id="modalMessage" class="text-gray-700 text-center mb-6"></p>
                    <div id="modalCopySection" class="hidden">
                        <div class="bg-gray-50 rounded-lg p-4 mb-4 border border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Password Baru:</p>
                                    <p id="modalPasswordText" class="text-2xl font-mono font-bold text-gray-900"></p>
                                </div>
                                <button onclick="copyPasswordToClipboard()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                                    📋 Copy
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 text-center">Berikan password ini kepada user untuk login</p>
                    </div>
                </div>
                <div id="modalFooter" class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                    <button onclick="closeModal()" id="modalCloseBtn" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 shadow-sm animate-fade-in">
                <svg class="w-5 h-5 mr-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 flex items-center p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 shadow-sm animate-fade-in">
                <svg class="w-5 h-5 mr-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 11.001 10 8 8 0 0118 10zM8.293 5.293a1 1 0 011.414 0L10 5.586l.293-.293a1 1 0 111.414 1.414L11.414 7l.293.293a1 1 0 01-1.414 1.414L10 8.414l-.293.293A1 1 0 018.293 7.293L8.586 7l-.293-.293a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        @if(isset($error_message) && $error_message)
            <div class="mb-6 flex items-center p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 shadow-sm">
                <svg class="w-5 h-5 mr-3 text-rose-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                <span class="font-bold">Error: {{ $error_message }}</span>
            </div>

            @if(isset($debug_info) && $debug_info)
                <div class="mb-6 bg-slate-900 text-slate-100 rounded-lg p-6 font-mono text-xs overflow-auto border border-slate-700 shadow-lg">
                    <div class="mb-4 text-blue-300 font-bold text-sm">🔍 DEBUG INFORMATION:</div>
                    
                    <div class="mb-3 border-b border-slate-700 pb-3">
                        <div class="text-yellow-300">📌 API Response Status:</div>
                        <div class="text-slate-300">{{ $debug_info['status'] ?? 'N/A' }}</div>
                    </div>

                    <div class="mb-3 border-b border-slate-700 pb-3">
                        <div class="text-yellow-300">📌 API URL:</div>
                        <div class="text-slate-300 break-all">{{ $debug_info['api_url'] ?? 'N/A' }}/v1/users</div>
                    </div>

                    <div class="mb-3 border-b border-slate-700 pb-3">
                        <div class="text-yellow-300">📌 Token Status:</div>
                        <div class="text-slate-300">
                            @if($debug_info['token_exists'])
                                <span class="text-green-300">✅ Token exists ({{ $debug_info['token_length'] ?? 0 }} chars)</span>
                            @else
                                <span class="text-red-300">❌ No token found in session!</span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3 border-b border-slate-700 pb-3">
                        <div class="text-yellow-300">📌 Response Body:</div>
                        <div class="text-slate-300 mt-2 bg-slate-800 p-2 rounded">
                            <pre class="whitespace-pre-wrap break-words">{{ $debug_info['body'] ?? 'No response body' }}</pre>
                        </div>
                    </div>

                    <div class="mt-4 text-slate-400 text-xs">
                        <details>
                            <summary class="cursor-pointer hover:text-slate-300">💡 Troubleshooting Tips</summary>
                            <div class="mt-2 space-y-1">
                                <p>1. Ensure backend is running: <code>npm start</code> in backend folder</p>
                                <p>2. Verify token exists: Check Session Storage in DevTools</p>
                                <p>3. Check backend logs for errors</p>
                                <p>4. Verify API_BASE_URL in .env matches your backend</p>
                                <p>5. Ensure you're logged in as superadmin or kemahasiswaan role</p>
                            </div>
                        </details>
                    </div>
                </div>
            @endif
        @endif

        @if(empty($users))
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 p-16 text-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-slate-800">
                    @if($search)
                        Hasil tidak ditemukan
                    @else
                        Database Kosong
                    @endif
                </h3>
                <p class="text-slate-500 mt-2 mb-8">
                    @if($search)
                        Tidak ada pengguna yang cocok dengan kata kunci "{{ $search }}"
                    @else
                        Belum ada data pengguna yang terdaftar dalam sistem.
                    @endif
                </p>
                @if($search)
                    <a href="{{ route('admin.users.index') }}" class="text-blue-600 font-bold hover:underline">Reset Pencarian</a>
                @else
                    <a href="{{ route('admin.users.create') }}" class="text-blue-600 font-bold hover:underline">Buat user pertama →</a>
                @endif
            </div>
        @else
            <div class="bg-white rounded-[1.5rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-200 text-slate-400 text-xs uppercase tracking-[0.1em] font-bold">
                                <th class="px-6 py-5">Informasi User</th>
                                <th class="px-6 py-5">Role</th>
                                <th class="px-6 py-5">Status Validasi</th>
                                <th class="px-6 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $user)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-500 font-bold shadow-sm">
                                            {{ substr($user['full_name'] ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 leading-none mb-1 group-hover:text-blue-600 transition-colors">{{ $user['full_name'] ?? 'Unknown User' }}</p>
                                            <p class="text-xs text-slate-400 font-medium">{{ $user['email'] ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex flex-col gap-1.5">
                                        @php
                                            $role = $user['role'] ?? 'mhs';
                                            $roleClasses = [
                                                'superadmin' => 'bg-rose-100 text-rose-700 border-rose-200',
                                                'kaprodi' => 'bg-purple-100 text-purple-700 border-purple-200',
                                                'kemahasiswaan' => 'bg-teal-100 text-teal-700 border-teal-200',
                                                'mhs' => 'bg-blue-100 text-blue-700 border-blue-200'
                                            ];
                                            $currentClass = $roleClasses[$role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                        @endphp
                                        <span class="w-fit px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $currentClass }}">
                                            {{ $role }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if(($user['role'] ?? 'mhs') === 'mhs')
                                        @if($user['is_active'] ?? false)
                                            <div class="flex items-center gap-1.5 text-emerald-600">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                <span class="text-xs font-bold">Tervalidasi</span>
                                            </div>
                                        @else
                                            <div class="flex items-center gap-1.5 text-amber-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                <span class="text-xs font-bold">Menunggu Validasi</span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 italic">N/A (Bukan Mahasiswa)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="flex items-center justify-center gap-3">
                                        {{-- Tombol Validasi untuk Mahasiswa yang Belum Divalidasi --}}
                                        @if(($user['role'] ?? 'mhs') === 'mhs' && !($user['is_active'] ?? false))
                                            <form action="{{ route('admin.users.verify', $user['id']) }}" method="POST" onsubmit="return confirm('Validasi akun mahasiswa ini?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg font-bold text-xs transition-all shadow-md shadow-emerald-200" title="Validasi Akun">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    Validasi
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('admin.users.edit', $user['id']) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors p-2 rounded-lg font-semibold text-sm" title="Edit User">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5M11 19l9-9a2.25 2.25 0 00-3.182-3.182L11 15.75V19z"></path></svg>
                                            Edit
                                        </a>

                                        <!-- Reset Password Button -->
                                        <button onclick="resetUserPassword('{{ $user['id'] }}', '{{ $user['full_name'] }}')" 
                                                class="inline-flex items-center gap-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 transition-colors p-2 rounded-lg font-semibold text-sm" 
                                                title="Reset Password">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                            Reset Pass
                                        </button>

                                        @if(($user['id'] ?? null) !== (Session::get('user')['id'] ?? null))
                                            <form action="{{ route('admin.users.deactivate', $user['id']) }}" method="POST" onsubmit="return confirm('Nonaktifkan user {{ $user['full_name'] ?? 'ini' }}? User akan hilang dari daftar ini dan akan otomatis dihapus jika tidak diaktifkan kembali dalam 30 hari.');">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors p-2 rounded-lg font-semibold text-sm"
                                                        title="Nonaktifkan User">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 105.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                    </svg>
                                                    Nonaktifkan
                                                </button>
                                            </form>
                                        @endif

                                        <!-- Role Dropdown -->
                                        <div class="relative group/dropdown">
                                            <button type="button" class="group/btn flex items-center gap-2 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg text-slate-600 text-xs font-bold transition-all" 
                                                    title="Ubah Role">
                                                {{ ucfirst($user['role'] ?? 'mhs') }}
                                                <svg class="w-3 h-3 group-hover/btn:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
                                            </button>
                                            
                                            <!-- Dropdown Menu -->
                                            <div class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-slate-200 opacity-0 invisible group-hover/dropdown:opacity-100 group-hover/dropdown:visible transition-all z-10">
                                                @php
                                                    $availableRoles = ['mhs', 'verifikator', 'kaprodi', 'dosen', 'kemahasiswaan', 'superadmin'];
                                                @endphp
                                                @foreach($availableRoles as $roleOption)
                                                    <form action="{{ route('admin.users.update', $user['id']) }}" method="POST" class="block w-full text-left" onsubmit="return confirm('Ubah role user menjadi {{ ucfirst($roleOption) }}?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="role" value="{{ $roleOption }}">
                                                        <input type="hidden" name="full_name" value="{{ $user['full_name'] }}">
                                                        <input type="hidden" name="email" value="{{ $user['email'] }}">
                                                        <button type="submit" 
                                                                class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-b-0 @if(($user['role'] ?? 'mhs') == $roleOption) bg-blue-50 text-blue-600 font-bold @endif">
                                                            {{ ucfirst($roleOption) }}
                                                            @if(($user['role'] ?? 'mhs') == $roleOption)
                                                                <svg class="w-4 h-4 inline-block ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                                            @endif
                                                        </button>
                                                    </form>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="bg-slate-50/50 px-6 py-4 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                    <p class="text-xs text-slate-400 font-medium">
                        Menampilkan <span class="font-bold text-slate-600">{{ count($users) }}</span> dari <span class="font-bold text-slate-600">{{ $meta['total'] ?? count($users) }}</span> pengguna.
                    </p>
                    
                    @if(isset($meta) && isset($meta['last_page']) && $meta['last_page'] > 1)
                    <div class="flex items-center gap-2">
                        @if($meta['page'] > 1)
                        <a href="{{ route('admin.users.index', array_merge(request()->query(), ['page' => $meta['page'] - 1])) }}" 
                           class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                            Sebelumnya
                        </a>
                        @endif

                        <span class="text-xs font-bold text-slate-500 px-2">
                            {{ $meta['page'] }} / {{ $meta['last_page'] }}
                        </span>

                        @if($meta['page'] < $meta['last_page'])
                        <a href="{{ route('admin.users.index', array_merge(request()->query(), ['page' => $meta['page'] + 1])) }}" 
                           class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                            Selanjutnya
                        </a>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
    // Debug: Log semua form submission untuk role change
    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('form[action*="admin.users.update"]');
        console.log(`Found ${forms.length} role change forms`);
        
        forms.forEach((form, index) => {
            form.addEventListener('submit', function(e) {
                console.log(`Form ${index} submitted`, {
                    action: form.action,
                    method: form.method,
                    role: form.querySelector('input[name="role"]')?.value,
                    full_name: form.querySelector('input[name="full_name"]')?.value,
                    email: form.querySelector('input[name="email"]')?.value
                });
            });
        });
    });

    // Reset Password Function
    async function resetUserPassword(userId, userName) {
        if (!confirm(`Reset password untuk user "${userName}"?\n\nPassword akan di-reset ke default dan harus diberitahukan kepada user.`)) {
            return;
        }

        try {
            const token = @json(Session::get('api_token'));
            const baseUrl = @json(rtrim(env('BACKEND_API_URL'), '/'));

            if (!token) {
                showModal('error', 'Token Tidak Ditemukan', 'Token tidak ditemukan. Silakan login kembali.');
                return;
            }

            const response = await fetch(baseUrl + '/v1/users/' + userId + '/reset-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                credentials: 'include'
            });

            const data = await response.json();

            if (response.ok) {
                const newPassword = data.data?.new_password || data.new_password || '123123';
                showModal('success', 'Password Berhasil Di-Reset!', `Password untuk user "${userName}" telah di-reset.`, newPassword);
            } else {
                const errorMsg = data.message || data.error || 'Gagal reset password';
                showModal('error', 'Gagal Reset Password', errorMsg);
            }
        } catch (error) {
            console.error('Reset password error:', error);
            showModal('error', 'Error Koneksi', error.message || 'Gagal koneksi ke server');
        }
    }

// Modal Helper Functions
function showModal(type, title, message, password = null) {
    const modal = document.getElementById('notificationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalCopySection = document.getElementById('modalCopySection');
    const modalPasswordText = document.getElementById('modalPasswordText');
    
    // Set icon based on type
    if (type === 'success') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    } else if (type === 'error') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
    } else if (type === 'info') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    
    // Show password copy section if provided
    if (password) {
        modalPasswordText.textContent = password;
        modalCopySection.classList.remove('hidden');
        window.currentPassword = password;
    } else {
        modalCopySection.classList.add('hidden');
        window.currentPassword = null;
    }
    
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
    window.currentPassword = null;
}

function copyPasswordToClipboard() {
    if (window.currentPassword && navigator.clipboard) {
        navigator.clipboard.writeText(window.currentPassword).then(() => {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '✅ Tersalin!';
            btn.classList.add('bg-green-600');
            btn.classList.remove('bg-blue-600');
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('bg-green-600');
                btn.classList.add('bg-blue-600');
            }, 2000);
        });
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<style>
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in {
        animation: fade-in 0.3s ease-out forwards;
    }
</style>
@endsection
