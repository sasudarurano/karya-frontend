@extends('layouts.app')

@section('title', 'Detail Karya - Moderasi')

@section('content')
<div class="max-w-7xl mx-auto pb-20 px-4 sm:px-6">
    {{-- Breadcrumb --}}
    <nav class="flex text-sm font-medium text-gray-500 mb-8">
        <a href="{{ route('admin.posts.index') }}" class="hover:text-blue-600 transition">Moderasi Karya</a>
        <span class="mx-3 text-gray-300">/</span>
        <span class="text-gray-900 truncate">{{ substr($post['title'] ?? 'Tanpa Judul', 0, 50) }}...</span>
    </nav>

    @if(isset($post))
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Header Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                {{-- Status Badges --}}
                <div class="flex items-center gap-3 mb-6 flex-wrap">
                    @php
                        $isPublished = $post['is_published'] ?? false;
                        $isRejected = isset($post['rejected_at']) && $post['rejected_at'];
                        $isRevision = isset($post['revision_requested_at']) && $post['revision_requested_at'];
                    @endphp
                    @if($isPublished)
                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-semibold uppercase tracking-wide">
                            ✓ Dipublikasikan
                        </span>
                    @elseif($isRejected)
                        <span class="px-4 py-2 bg-red-100 text-red-700 rounded-full text-sm font-semibold uppercase tracking-wide">
                            ❌ Ditolak
                        </span>
                    @elseif($isRevision)
                        <span class="px-4 py-2 bg-amber-100 text-amber-700 rounded-full text-sm font-semibold uppercase tracking-wide">
                            ⚠️ Revisi Diminta
                        </span>
                    @else
                        <span class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold uppercase tracking-wide">
                            ⏳ Menunggu Persetujuan
                        </span>
                    @endif
                    @if(isset($post['category']))
                    <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold uppercase tracking-wide">
                        {{ $post['category'] }}
                    </span>
                    @endif
                </div>

                {{-- Title --}}
                <h1 class="text-4xl font-bold text-gray-900 mb-6 leading-tight">
                    {{ $post['title'] ?? 'Tanpa Judul' }}
                </h1>

                {{-- Author Info --}}
                <div class="flex items-center gap-4 pb-6 border-b border-gray-100">
                    @php
                        $profilePicture = null;
                        if (isset($post['author']['profile_picture']['file_url'])) {
                            $path = str_replace('\\', '/', $post['author']['profile_picture']['file_url']);
                            $profilePicture = str_starts_with($path, 'http') ? $path : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($path, '/');
                        } elseif (isset($post['author']['profile_picture']) && is_string($post['author']['profile_picture'])) {
                            $path = str_replace('\\', '/', $post['author']['profile_picture']);
                            $profilePicture = str_starts_with($path, 'http') ? $path : rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim($path, '/');
                        }
                    @endphp
                    @if($profilePicture)
                        <img src="{{ $profilePicture }}" 
                             alt="{{ $post['author']['full_name'] ?? 'Unknown' }}" 
                             class="w-16 h-16 rounded-full object-cover shadow-md"
                             onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl" style="display:none;">
                            {{ strtoupper(substr($post['author']['full_name'] ?? 'U', 0, 1)) }}
                        </div>
                    @else
                        <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($post['author']['full_name'] ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">{{ $post['author']['full_name'] ?? 'Unknown Author' }}</h3>
                        <p class="text-sm text-gray-500">{{ $post['author']['username'] ?? 'user' }}</p>
                        <p class="text-sm text-gray-400">{{ \Carbon\Carbon::parse($post['created_at'])->diffForHumans() }}</p>
                    </div>
                </div>

                {{-- Program Studi & User Type --}}
                @if(isset($post['program_studi']) || isset($post['author']['role']))
                <div class="grid grid-cols-2 gap-4 py-6 border-b border-gray-100">
                    @if(isset($post['program_studi']))
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Program Studi</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $post['program_studi'] }}</p>
                    </div>
                    @endif
                    @if(isset($post['author']['role']))
                    <div>
                        <p class="text-sm text-gray-500 font-medium">Tipe User</p>
                        <p class="text-lg font-semibold text-gray-900">{{ ucfirst($post['author']['role']) }}</p>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Supervisor Info --}}
                @if(isset($post['supervisor']) && !empty($post['supervisor']))
                <div class="py-6 border-b border-gray-100">
                    <p class="text-sm text-gray-500 font-medium mb-3">Dosen Pembimbing</p>
                    <div class="flex items-center gap-3 p-4 bg-blue-50 rounded-lg border border-blue-100">
                        <div class="w-12 h-12 bg-blue-200 rounded-full flex items-center justify-center text-blue-700 font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($post['supervisor']['full_name'] ?? 'D', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $post['supervisor']['full_name'] ?? 'Unknown Supervisor' }}</p>
                            <p class="text-sm text-gray-600">{{ $post['supervisor']['username'] ?? 'user' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Caption/Description --}}
                @if(isset($post['caption']) && !empty($post['caption']))
                <div class="py-6">
                    <p class="text-gray-600 whitespace-pre-line leading-relaxed">{{ $post['caption'] }}</p>
                </div>
                @endif
            </div>

            {{-- Engagement Stats --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Statistik Keterlibatan</h3>
                <div class="grid grid-cols-3 gap-6">
                    <div class="text-center p-4 bg-gradient-to-br from-red-50 to-pink-50 rounded-xl border border-red-100">
                        <div class="text-4xl font-bold text-red-600">{{ $post['likeCount'] ?? 0 }}</div>
                        <p class="text-sm text-gray-600 mt-2">Suka</p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                        <div class="text-4xl font-bold text-blue-600">{{ $post['commentCount'] ?? 0 }}</div>
                        <p class="text-sm text-gray-600 mt-2">Komentar</p>
                    </div>
                    <div class="text-center p-4 bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl border border-purple-100">
                        <div class="text-4xl font-bold text-purple-600">{{ $post['viewCount'] ?? 0 }}</div>
                        <p class="text-sm text-gray-600 mt-2">Lihat</p>
                    </div>
                </div>
            </div>

            {{-- Attachments --}}
            @if(isset($post['attachments']) && count($post['attachments']) > 0)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">File & Attachments</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($post['attachments'] as $file)
                    @php
                        $filePath = $file['file_url'] ?? $file['file_path'] ?? '';
                        $fileName = $file['filename'] ?? $file['original_name'] ?? 'File';
                        $mimeType = $file['mime'] ?? '';
                        
                        // Determine if it's an image
                        $isImage = str_contains($mimeType, 'image');
                        
                        // Build full URL if relative path
                        if ($filePath && !str_starts_with($filePath, 'http')) {
                            $backendUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/');
                            $filePath = $backendUrl . '/' . ltrim(str_replace('\\', '/', $filePath), '/');
                        }
                    @endphp
                    
                    @if($isImage && $filePath)
                        <div class="relative rounded-lg overflow-hidden border border-gray-200 hover:shadow-lg transition">
                            <img src="{{ $filePath }}" alt="{{ $fileName }}" class="w-full h-48 object-cover">
                            <div class="absolute inset-0 bg-black/0 hover:bg-black/40 transition flex items-center justify-center">
                                <a href="{{ $filePath }}" target="_blank" class="px-4 py-2 bg-white/90 text-gray-900 rounded-lg font-semibold opacity-0 hover:opacity-100 transition">
                                    Buka
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ $filePath }}" target="_blank" class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition group">
                            <svg class="w-8 h-8 text-gray-400 group-hover:text-blue-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $fileName }}</p>
                                <p class="text-xs text-gray-500">{{ $mimeType ?? 'File' }}</p>
                            </div>
                            <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

            {{-- HKI Document Section --}}
            @php
                // Cari dokumen PDF di dalam attachments dengan mime type application/pdf
                $hkiDoc = null;
                if (isset($post['attachments']) && is_array($post['attachments'])) {
                    foreach ($post['attachments'] as $attachment) {
                        if (isset($attachment['mime']) && str_contains($attachment['mime'], 'pdf')) {
                            $hkiDoc = $attachment;
                            break;
                        }
                    }
                }
                
                // Fallback: cek field alternatif jika ada
                if (!$hkiDoc) {
                    $hkiDocPath = $post['hki_document'] ?? $post['post_document'] ?? $post['document'] ?? $post['pdf'] ?? null;
                    if ($hkiDocPath) {
                        $hkiDoc = ['file_url' => $hkiDocPath];
                    }
                }
            @endphp
            @if(isset($hkiDoc) && !empty($hkiDoc))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    Dokumen HKI / Laporan
                </h3>
                @php
                    $hkiUrl = $hkiDoc['file_url'];
                    if (!str_starts_with($hkiUrl, 'http')) {
                        $hkiUrl = rtrim(str_replace('/api', '', env('BACKEND_API_URL')), '/') . '/' . ltrim(str_replace('\\', '/', $hkiUrl), '/');
                    }
                    $hkiFileName = $hkiDoc['filename'] ?? basename($hkiDoc['file_url']);
                @endphp
                <a href="{{ $hkiUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 px-6 py-3 bg-amber-50 text-amber-700 rounded-xl border border-amber-200 hover:bg-amber-100 hover:border-amber-300 transition-all font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Download {{ $hkiFileName }}
                </a>
            </div>
            @endif

            {{-- Content/Body --}}
            @if(isset($post['content']) && !empty($post['content']))
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Konten Lengkap</h3>
                <div class="prose prose-sm max-w-none">
                    <p class="text-gray-600 whitespace-pre-wrap leading-relaxed">{{ $post['content'] }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar - Moderation Actions --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Moderation Actions Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Aksi Moderasi
                </h3>

                {{-- Status Info --}}
                @php
                    $statusClass = 'bg-yellow-50 border border-yellow-200';
                    $statusTextClass = 'text-yellow-700';
                    $statusMessage = '⏳ Menunggu Persetujuan';
                    
                    if ($post['is_published']) {
                        $statusClass = 'bg-green-50 border border-green-200';
                        $statusTextClass = 'text-green-700';
                        $statusMessage = '✓ Sudah Dipublikasikan';
                    } elseif (isset($post['rejected_at']) && $post['rejected_at']) {
                        $statusClass = 'bg-red-50 border border-red-200';
                        $statusTextClass = 'text-red-700';
                        $statusMessage = '❌ Ditolak';
                    } elseif (isset($post['revision_requested_at']) && $post['revision_requested_at']) {
                        $statusClass = 'bg-amber-50 border border-amber-200';
                        $statusTextClass = 'text-amber-700';
                        $statusMessage = '⚠️ Revisi Diminta';
                    }
                @endphp
                <div class="mb-6 p-4 rounded-lg {{ $statusClass }}">
                    <p class="text-xs font-semibold {{ $statusTextClass }} uppercase tracking-wide mb-2">Status Saat Ini</p>
                    <p class="text-sm {{ $statusTextClass }} font-semibold">
                        {!! $statusMessage !!}
                    </p>
                    
                    {{-- Rejection or Revision Details --}}
                    @if(isset($post['rejected_at']) && $post['rejected_at'])
                    <div class="mt-3 pt-3 border-t border-red-200">
                        <p class="text-xs text-red-600 font-medium mb-1">Alasan Penolakan:</p>
                        <p class="text-sm text-red-700">{{ $post['rejection_reason'] ?? '-' }}</p>
                    </div>
                    @elseif(isset($post['revision_requested_at']) && $post['revision_requested_at'])
                    <div class="mt-3 pt-3 border-t border-amber-200">
                        <p class="text-xs text-amber-600 font-medium mb-1">Komentar Revisi:</p>
                        <p class="text-sm text-amber-700">{{ $post['revision_comment'] ?? '-' }}</p>
                    </div>
                    @endif
                </div>

                {{-- Toggle Publish --}}
                {{-- Tampilkan selama tidak berstatus ditolak (revisi tetap bisa dipublish) --}}
                @if(!(isset($post['rejected_at']) && $post['rejected_at']))
                <form action="{{ route('admin.posts.toggle-publish', $post['id']) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    @if(!$post['is_published'])
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin mempublikasikan karya ini?')" 
                                class="w-full px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold rounded-lg hover:from-green-600 hover:to-emerald-700 transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Setujui & Publikasikan
                        </button>
                    @else
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin membatalkan publikasi?')" 
                                class="w-full px-4 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-semibold rounded-lg hover:from-amber-600 hover:to-orange-700 transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Batalkan Publikasi
                        </button>
                    @endif
                </form>

                {{-- Revision Button --}}
                @if(!$post['is_published'])
                <button type="button" onclick="document.getElementById('revisionModal').classList.remove('hidden')" 
                        class="w-full px-4 py-3 bg-yellow-100 text-yellow-700 font-semibold rounded-lg hover:bg-yellow-200 transition flex items-center justify-center gap-2 mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Minta Revisi
                </button>
                @endif

                {{-- Reject Button --}}
                @if(!$post['is_published'])
                <button type="button" onclick="document.getElementById('rejectModal').classList.remove('hidden')" 
                        class="w-full px-4 py-3 bg-red-100 text-red-700 font-semibold rounded-lg hover:bg-red-200 transition flex items-center justify-center gap-2 mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Tolak Karya
                </button>
                @endif
                @else
                {{-- Jika sudah ditolak, tampilkan opsi untuk menghapus status penolakan --}}
                @if(isset($post['rejected_at']) && $post['rejected_at'])
                <div class="mb-3 p-4 bg-red-50 rounded-lg border border-red-200">
                    <p class="text-sm font-semibold text-red-700 mb-2">Karya ini telah ditolak</p>
                    <p class="text-xs text-red-600 mb-4">Anda dapat memulai ulang proses review dengan menghapus status penolakan.</p>
                    <form action="{{ route('admin.posts.clear-rejection', $post['id']) }}" method="POST" onsubmit="return confirm('Batalkan status penolakan untuk karya ini?');">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-white text-red-700 border border-red-200 rounded-lg hover:bg-red-100 transition font-semibold">
                            Batalkan Penolakan
                        </button>
                    </form>
                </div>
                @endif
                @endif

                <a href="{{ route('admin.posts.index') }}" 
                   class="w-full mt-4 px-4 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Daftar
                </a>
            </div>

            {{-- Technical Info Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Informasi Teknis
                </h3>
                <div class="space-y-4 text-sm">
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-gray-500 font-medium mb-1">ID Karya</p>
                        <p class="text-gray-900 font-mono text-xs break-all">{{ $post['id'] }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-gray-500 font-medium mb-1">ID User</p>
                        <p class="text-gray-900 font-mono text-xs break-all">{{ $post['user_id'] }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-gray-500 font-medium mb-1">Dibuat</p>
                        <p class="text-gray-900">{{ \Carbon\Carbon::parse($post['created_at'])->format('d M Y H:i') }}</p>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg">
                        <p class="text-gray-500 font-medium mb-1">Diperbarui</p>
                        <p class="text-gray-900">{{ \Carbon\Carbon::parse($post['updated_at'])->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modals --}}
    {{-- Revision Modal --}}
    <div id="revisionModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Minta Revisi
                </h3>
            </div>
            <form action="{{ route('admin.posts.request-revision', $post['id']) }}" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">Komentar Revisi</label>
                    <textarea class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none transition" 
                              name="revision_comment" rows="4" 
                              placeholder="Jelaskan apa yang perlu direvisi..." required></textarea>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="document.getElementById('revisionModal').classList.add('hidden')" 
                            class="flex-1 px-4 py-2 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-lg transition">
                        Kirim Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Rejection Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-rose-600 px-6 py-4">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Tolak Karya
                </h3>
            </div>
            <form action="{{ route('admin.posts.reject', $post['id']) }}" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-sm font-semibold text-gray-900 mb-3">Alasan Penolakan</label>
                    <textarea class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-100 outline-none transition" 
                              name="rejection_reason" rows="4" 
                              placeholder="Jelaskan alasan penolakan..." required></textarea>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex gap-3">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" 
                            class="flex-1 px-4 py-2 text-gray-700 font-semibold rounded-lg hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-lg transition">
                        Tolak Karya
                    </button>
                </div>
            </form>
        </div>
    </div>

    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Karya Tidak Ditemukan</h3>
        <p class="text-gray-600 mb-6">Karya yang Anda cari tidak tersedia atau telah dihapus.</p>
        <a href="{{ route('admin.posts.index') }}" class="inline-block px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
            Kembali ke Daftar Karya
        </a>
    </div>
    @endif
</div>

<style>
    #revisionModal.hidden, #rejectModal.hidden {
        display: none;
    }
</style>
@endsection
