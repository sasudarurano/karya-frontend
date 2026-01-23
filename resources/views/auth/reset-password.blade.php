@extends('layouts.app')

@section('content')
<div class="flex justify-center items-center min-h-[60vh] py-8">
    <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-lg border border-gray-100">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Buat Password Baru</h2>
            <p class="text-sm text-gray-500">Masukkan password baru Anda</p>
        </div>

        {{-- Loading State --}}
        <div id="loadingState" class="text-center py-8">
            <svg class="animate-spin h-10 w-10 text-blue-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600">Memverifikasi token...</p>
        </div>

        {{-- Error State --}}
        <div id="errorState" class="hidden text-center py-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Token Tidak Valid</h3>
            <p id="errorMessage" class="text-gray-600 mb-4"></p>
            <a href="{{ route('forgot-password') }}" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                Request Ulang
            </a>
        </div>

        {{-- Form State --}}
        <form id="resetPasswordForm" class="space-y-4 hidden">
            @csrf
            
            <input type="hidden" id="token" name="token" value="">

            <div id="userInfo" class="bg-blue-50 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800"><strong>Reset password untuk:</strong></p>
                <p id="userEmail" class="text-blue-900 font-medium"></p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                <input type="password" 
                       id="password"
                       name="password" 
                       required 
                       minlength="8"
                       placeholder="Minimal 8 karakter"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                <input type="password" 
                       id="password_confirmation"
                       name="password_confirmation" 
                       required 
                       minlength="8"
                       placeholder="Ulangi password baru"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
            </div>

            <button type="submit" id="submitBtn" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                Reset Password
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Login
            </a>
        </div>
    </div>
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
        </div>
        <div id="modalFooter" class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
            <button onclick="closeModal()" id="modalCloseBtn" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
const baseUrl = @json(rtrim(env('BACKEND_API_URL'), '/'));
const loginUrl = @json(route('login'));

document.addEventListener('DOMContentLoaded', async () => {
    // Get token from URL
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    if (!token) {
        showErrorState('Token tidak ditemukan. Silakan request ulang reset password.');
        return;
    }

    // Verify token
    try {
        const response = await fetch(baseUrl + '/v1/users/verify-reset-token/' + token, {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok && data.data.valid) {
            // Token valid, show form
            document.getElementById('loadingState').classList.add('hidden');
            document.getElementById('resetPasswordForm').classList.remove('hidden');
            document.getElementById('token').value = token;
            document.getElementById('userEmail').textContent = data.data.user.email;
        } else {
            showErrorState(data.message || data.data?.message || 'Token tidak valid atau sudah kadaluarsa');
        }
    } catch (error) {
        console.error('Verify token error:', error);
        showErrorState('Gagal memverifikasi token. Silakan coba lagi.');
    }

    // Form submit handler
    const form = document.getElementById('resetPasswordForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const token = document.getElementById('token').value;
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        // Validation
        if (password.length < 8) {
            showModal('error', 'Validasi Gagal', 'Password minimal 8 karakter.');
            return;
        }

        if (password !== passwordConfirmation) {
            showModal('error', 'Validasi Gagal', 'Password dan konfirmasi password tidak cocok.');
            return;
        }

        // Disable button
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        try {
            const response = await fetch(baseUrl + '/v1/users/reset-password-with-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token, password })
            });

            const data = await response.json();

            if (response.ok) {
                showModal('success', 'Password Berhasil Diubah!', 'Password Anda telah berhasil diubah. Silakan login dengan password baru Anda.');
                form.reset();
                
                // Redirect to login after 3 seconds
                setTimeout(() => {
                    window.location.href = loginUrl;
                }, 3000);
            } else {
                const errorMsg = data.message || data.error || 'Gagal reset password';
                showModal('error', 'Reset Password Gagal', errorMsg);
            }
        } catch (error) {
            console.error('Reset password error:', error);
            showModal('error', 'Error Koneksi', 'Gagal terhubung ke server. Silakan coba lagi.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Reset Password';
        }
    });
});

function showErrorState(message) {
    document.getElementById('loadingState').classList.add('hidden');
    document.getElementById('errorState').classList.remove('hidden');
    document.getElementById('errorMessage').textContent = message;
}

// Modal Helper Functions
function showModal(type, title, message) {
    const modal = document.getElementById('notificationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    
    if (type === 'success') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    } else if (type === 'error') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100';
        modalIcon.innerHTML = '<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
    }
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection
