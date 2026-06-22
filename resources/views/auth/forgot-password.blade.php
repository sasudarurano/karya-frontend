@extends('layouts.app')

@section('title', 'Lupa Password')
@section('full_width', true)

@section('content')
<section class="relative min-h-[calc(100vh-4rem)] flex items-center justify-center bg-slate-50 selection:bg-red-500/30 py-12 px-6">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-[20%] -left-[10%] h-[70%] w-[50%] rounded-full bg-red-400/10 blur-[120px]"></div>
        <div class="absolute top-[60%] right-[0%] h-[60%] w-[40%] rounded-full bg-rose-400/10 blur-[100px]"></div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(0,0,0,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(0,0,0,0.03)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_60%_at_50%_50%,#000_70%,transparent_100%)]"></div>
    </div>

    <div class="relative z-10 w-full max-w-md rounded-3xl border border-white bg-white/60 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-2xl">
        
        <a href="{{ route('home') }}" class="mb-8 flex items-center justify-center gap-3 transition-transform hover:scale-105">
            <img src="{{ asset('storage/branding/logo1.png') }}" alt="KARYA.UMDP" class="h-10 w-10 object-contain drop-shadow-sm">
            <span class="text-2xl font-black tracking-tighter text-slate-900">
                KARYA<span class="text-red-600">.UMDP</span>
            </span>
        </a>

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-red-50 rounded-2xl mb-4 border border-red-100 text-red-500">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 mb-2">Lupa Password?</h2>
            <p class="text-sm text-slate-500 leading-relaxed">Masukkan email atau username Anda untuk menerima instruksi pemulihan password.</p>
        </div>

        {{-- Messages Section --}}
        <div id="messagesContainer"></div>

        <form id="forgotPasswordForm" class="space-y-6">
            @csrf
            
            <div class="space-y-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500">Email atau Username</label>
                <div class="relative group">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex w-14 items-center justify-center text-slate-400 group-focus-within:text-red-500 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-4.5 7.794"/>
                        </svg>
                    </div>
                    <input type="text" id="identifier" name="identifier" required placeholder="admin@example.com atau username"
                        class="h-12 w-full rounded-xl border border-slate-200 bg-white/80 pl-14 pr-4 text-sm font-medium text-slate-900 placeholder-slate-400 outline-none transition-all hover:bg-white focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10">
                </div>
            </div>

            <button type="submit" id="submitBtn" class="group relative flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-bold text-white transition-all hover:bg-red-700 hover:shadow-lg hover:shadow-red-600/30 focus:outline-none focus:ring-4 focus:ring-red-500/30">
                <span>Reset Password</span>
                <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-sm font-bold text-red-600 transition-colors hover:text-red-700 inline-flex items-center gap-1.5 hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Login
            </a>
        </div>
    </div>
</section>

{{-- Modal Component --}}
<div id="notificationModal" class="hidden fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="if(event.target === this) closeModal()">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl max-w-sm w-full transform transition-all p-6" onclick="event.stopPropagation()">
        <div id="modalIcon" class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-2xl"></div>
        <h3 id="modalTitle" class="text-xl font-bold text-slate-900 text-center mb-2"></h3>
        <p id="modalMessage" class="text-sm text-slate-500 text-center mb-6 leading-relaxed"></p>
        <button onclick="closeModal()" id="modalCloseBtn" class="w-full py-3 bg-slate-100 text-slate-800 rounded-xl hover:bg-slate-200 transition font-bold text-sm">
            Tutup
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const messagesContainer = document.getElementById('messagesContainer');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const identifier = document.getElementById('identifier').value.trim();
        
        if (!identifier) {
            showModal('error', 'Validasi Gagal', 'Email atau username harus diisi.');
            return;
        }

        // Disable button dan tampilkan loading
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

        try {
            const baseUrl = @json(rtrim(env('BACKEND_API_URL'), '/'));
            
            const response = await fetch(baseUrl + '/v1/users/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ identifier })
            });

            const data = await response.json();

            if (response.ok) {
                form.reset();
                showModal('success', 'Email Terkirim!', `Link reset password telah dikirim ke email Anda. Silakan cek inbox atau folder spam.`);
            } else {
                const errorMsg = data.message || data.error || 'Gagal mengirim email reset password';
                showModal('error', 'Gagal Mengirim Email', errorMsg);
            }
        } catch (error) {
            console.error('Forgot password error:', error);
            showModal('error', 'Error Koneksi', 'Gagal terhubung ke server. Silakan coba lagi.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span>Reset Password</span><svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>';
        }
    });
});

// Modal Helper Functions
function showModal(type, title, message) {
    const modal = document.getElementById('notificationModal');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    
    // Set icon based on type
    if (type === 'success') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-500';
        modalIcon.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
    } else if (type === 'error') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-2xl bg-rose-50 border border-rose-100 text-rose-500';
        modalIcon.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
    } else if (type === 'info') {
        modalIcon.className = 'flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-50 border border-blue-100 text-blue-500';
        modalIcon.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
    }
    
    modalTitle.textContent = title;
    modalMessage.textContent = message;
    modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>
@endsection
