@extends('layouts.app')

@section('title', 'Detail Karya - Moderasi')

@section('content')
@php
    $backendBaseUrl = rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/');
    $fileBaseUrl = rtrim(str_replace('/api', '', $backendBaseUrl), '/');

    $safeUrl = function ($path) use ($fileBaseUrl) {
        if (!$path) return null;
        $path = str_replace('\\', '/', $path);
        return str_starts_with($path, 'http') ? $path : $fileBaseUrl . '/' . ltrim($path, '/');
    };

    $isPublished = $post['is_published'] ?? false;
    $isRejected = !empty($post['rejected_at']);
    $isRevision = !empty($post['revision_requested_at']);

    $status = [
        'label' => 'Menunggu Persetujuan',
        'tone' => 'amber',
        'dot' => 'bg-amber-500',
        'pill' => 'bg-amber-50 text-amber-700 border-amber-200',
    ];

    if ($isPublished) {
        $status = ['label' => 'Dipublikasikan', 'tone' => 'emerald', 'dot' => 'bg-emerald-500', 'pill' => 'bg-emerald-50 text-emerald-700 border-emerald-200'];
    } elseif ($isRejected) {
        $status = ['label' => 'Ditolak', 'tone' => 'rose', 'dot' => 'bg-rose-500', 'pill' => 'bg-rose-50 text-rose-700 border-rose-200'];
    } elseif ($isRevision) {
        $status = ['label' => 'Revisi Diminta', 'tone' => 'amber', 'dot' => 'bg-amber-500', 'pill' => 'bg-amber-50 text-amber-700 border-amber-200'];
    }

    $author = $post['author'] ?? [];
    $authorName = $author['full_name'] ?? 'Unknown Author';
    $authorUsername = $author['username'] ?? '-';
    $authorPhoto = $safeUrl($author['profile_picture']['file_url'] ?? (is_string($author['profile_picture'] ?? null) ? $author['profile_picture'] : null));
    $programStudi = $author['profile']['program_studi']['nama_program_studi'] ?? ($post['program_studi_display'] ?? null);

    $mediaItems = [];

    if (!empty($post['attachments']) && is_array($post['attachments'])) {
        foreach ($post['attachments'] as $attachment) {
            if (str_contains($attachment['mime'] ?? '', 'image')) {
                $url = $safeUrl($attachment['file_url'] ?? null);
                if ($url) {
                    $mediaItems[] = [
                        'type' => 'image',
                        'url' => $url,
                        'name' => $attachment['filename'] ?? 'Upload Foto Karya',
                    ];
                }
            }
        }
    }

    if (empty($mediaItems) && !empty($post['gdrive_folder_items']) && is_array($post['gdrive_folder_items'])) {
        foreach ($post['gdrive_folder_items'] as $item) {
            $mime = $item['mimeType'] ?? '';
            if (str_contains($mime, 'image') && !empty($item['thumbnailLink'])) {
                $mediaItems[] = [
                    'type' => 'image',
                    'url' => str_replace('=s220', '=w1400', $item['thumbnailLink']),
                    'name' => $item['name'] ?? 'Google Drive Image',
                ];
            }
        }
    }

    if (empty($mediaItems) && !empty($post['gdrive_url'])) {
        $gdriveUrl = $post['gdrive_url'];
        $fileId = null;
        if (preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
            $fileId = $matches[1];
        } elseif (preg_match('/id=([a-zA-Z0-9_-]+)/', $gdriveUrl, $matches)) {
            $fileId = $matches[1];
        }
        if ($fileId) {
            $mediaItems[] = [
                'type' => 'image',
                'url' => 'https://drive.google.com/thumbnail?id=' . $fileId . '&sz=w1400',
                'name' => 'Google Drive Preview',
            ];
        }
    }

    $pdfFiles = [];
    foreach (($post['attachments'] ?? []) as $attachment) {
        if (str_contains($attachment['mime'] ?? '', 'pdf')) {
            $pdfFiles[] = $attachment;
        }
    }

    $createdAt = !empty($post['created_at']) ? \Carbon\Carbon::parse($post['created_at']) : null;
    $updatedAt = !empty($post['updated_at']) ? \Carbon\Carbon::parse($post['updated_at']) : null;
@endphp

<div class="min-h-screen bg-slate-50 pb-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
        <nav class="flex items-center gap-2 text-sm font-semibold text-slate-500 mb-6">
            <a href="{{ route('admin.posts.index') }}" class="hover:text-blue-600 transition">Moderasi Karya</a>
            <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-slate-900 truncate">{{ $post['title'] ?? 'Tanpa Judul' }}</span>
        </nav>

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">{{ session('error') }}</div>
        @endif

        @if(isset($post))
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <main class="lg:col-span-8 space-y-6">
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    @if(count($mediaItems) > 0)
                        <div class="relative bg-slate-900 aspect-video">
                            <div class="absolute inset-0 bg-cover bg-center opacity-25 blur-3xl scale-110" style="background-image:url('{{ $mediaItems[0]['url'] }}')"></div>
                            <img src="{{ $mediaItems[0]['url'] }}" alt="{{ $post['title'] ?? 'Preview Karya' }}" class="relative z-10 w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden'); this.nextElementSibling.classList.add('flex');">
                            <div class="hidden relative z-10 w-full h-full items-center justify-center text-slate-300">Preview media tidak dapat dimuat.</div>
                        </div>
                    @else
                        <div class="aspect-video bg-slate-100 flex items-center justify-center text-slate-400">
                            <div class="text-center">
                                <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="text-sm font-bold">Tidak ada preview media</p>
                            </div>
                        </div>
                    @endif

                    <div class="p-6 sm:p-8">
                        <div class="flex flex-wrap items-center gap-3 mb-5">
                            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-bold {{ $status['pill'] }}">
                                <span class="h-2 w-2 rounded-full {{ $status['dot'] }}"></span>
                                {{ $status['label'] }}
                            </span>
                            <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold uppercase text-blue-700">
                                {{ $post['category'] ?? 'Umum' }}
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl font-black tracking-tight text-slate-950 leading-tight">{{ $post['title'] ?? 'Tanpa Judul' }}</h1>

                        <div class="mt-6 flex flex-col sm:flex-row sm:items-center gap-4 border-t border-slate-100 pt-6">
                            @if($authorPhoto)
                                <img src="{{ $authorPhoto }}" alt="{{ $authorName }}" class="w-14 h-14 rounded-xl object-cover border border-slate-200">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xl font-black">{{ strtoupper(substr($authorName, 0, 1)) }}</div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-base font-black text-slate-900">{{ $authorName }}</p>
                                <p class="text-sm text-slate-500">{{ '@' . $authorUsername }}{{ $programStudi ? ' · ' . $programStudi : '' }}</p>
                                <p class="text-xs text-slate-400">{{ $createdAt ? $createdAt->diffForHumans() : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
                    <h2 class="text-lg font-black text-slate-950 mb-5">Deskripsi Karya</h2>
                    <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed">
                        @if(!empty($post['caption']))
                            {!! $post['caption'] !!}
                        @else
                            <p class="text-slate-400">Penulis belum menambahkan deskripsi.</p>
                        @endif
                    </div>
                </section>

                @if(!empty($post['supervisor']) || !empty($post['contributors']))
                <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if(!empty($post['supervisor']))
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                        <h2 class="text-sm font-black uppercase tracking-wide text-slate-500 mb-4">Dosen Pembimbing</h2>
                        <div class="flex items-center gap-4 rounded-xl bg-blue-50 border border-blue-100 p-4">
                            <div class="w-12 h-12 rounded-xl bg-blue-200 text-blue-700 flex items-center justify-center font-black">{{ strtoupper(substr($post['supervisor']['full_name'] ?? 'D', 0, 1)) }}</div>
                            <div class="min-w-0">
                                <p class="font-black text-slate-900 truncate">{{ $post['supervisor']['full_name'] ?? '-' }}</p>
                                <p class="text-sm text-slate-500">{{ $post['supervisor']['username'] ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!empty($post['contributors']))
                    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                        <h2 class="text-sm font-black uppercase tracking-wide text-slate-500 mb-4">Kontributor</h2>
                        <div class="space-y-3">
                            @foreach($post['contributors'] as $contributor)
                            <div class="flex items-center gap-3 rounded-xl bg-slate-50 border border-slate-100 p-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-black">{{ strtoupper(substr($contributor['full_name'] ?? 'U', 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-slate-900 truncate">{{ $contributor['full_name'] ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $contributor['username'] ?? '-' }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </section>
                @endif

                @if(!empty($post['attachments']) || !empty($pdfFiles) || !empty($post['gdrive_url']))
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
                    <h2 class="text-lg font-black text-slate-950 mb-5">Lampiran</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if(!empty($post['gdrive_url']))
                            <a href="{{ $post['gdrive_url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-4 rounded-xl border border-blue-200 bg-blue-50 p-4 hover:bg-blue-100 transition">
                                <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656l-2 2a4 4 0 01-5.656-5.656l1-1m5.656-1L14.828 8.172a4 4 0 015.656 5.656l-1 1"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-blue-900">Google Drive</p>
                                    <p class="text-xs text-blue-700 truncate">{{ $post['gdrive_url'] }}</p>
                                </div>
                            </a>
                        @endif

                        @foreach(($post['attachments'] ?? []) as $attachment)
                            @php
                                $fileUrl = $safeUrl($attachment['file_url'] ?? null);
                                $fileName = $attachment['filename'] ?? 'File';
                                $mime = $attachment['mime'] ?? 'File';
                            @endphp
                            @if($fileUrl)
                            <a href="{{ $fileUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 hover:bg-slate-100 transition">
                                <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 text-slate-500 flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707L13.293 3.293A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-slate-900 truncate">{{ $fileName }}</p>
                                    <p class="text-xs text-slate-500 truncate">{{ $mime }}</p>
                                </div>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </section>
                @endif
            </main>

            <aside class="lg:col-span-4 space-y-6">
                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 lg:sticky lg:top-24">
                    <h2 class="text-xl font-black text-slate-950 mb-5 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Aksi Moderasi
                    </h2>

                    <div class="rounded-xl border p-4 mb-5 {{ $status['pill'] }}">
                        <p class="text-xs font-black uppercase tracking-wide mb-1">Status Saat Ini</p>
                        <p class="font-black">{{ $status['label'] }}</p>
                        @if($isRejected)
                            <p class="mt-3 pt-3 border-t border-rose-200 text-sm">{{ $post['rejection_reason'] ?? '-' }}</p>
                        @elseif($isRevision)
                            <p class="mt-3 pt-3 border-t border-amber-200 text-sm">{{ $post['revision_comment'] ?? '-' }}</p>
                        @endif
                    </div>

                    @if(!$isRejected)
                        <form action="{{ route('admin.posts.toggle-publish', $post['id']) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PATCH')
                            <button type="submit" onclick="return confirm('{{ $isPublished ? 'Batalkan publikasi karya ini?' : 'Setujui dan publikasikan karya ini?' }}')" class="w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3 font-black text-white transition {{ $isPublished ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $isPublished ? 'M6 18L18 6M6 6l12 12' : 'M5 13l4 4L19 7' }}"/></svg>
                                {{ $isPublished ? 'Batalkan Publikasi' : 'Setujui & Publikasikan' }}
                            </button>
                        </form>

                        @if(!$isPublished)
                            <button type="button" onclick="openModerationModal('revisionModal')" class="mb-3 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-amber-100 px-4 py-3 font-black text-amber-800 hover:bg-amber-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Minta Revisi
                            </button>
                            <button type="button" onclick="openModerationModal('rejectModal')" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-rose-100 px-4 py-3 font-black text-rose-700 hover:bg-rose-200 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Tolak Karya
                            </button>
                        @endif
                    @else
                        <form action="{{ route('admin.posts.clear-rejection', $post['id']) }}" method="POST" onsubmit="return confirm('Batalkan status penolakan untuk karya ini?');">
                            @csrf
                            <button type="submit" class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 font-black text-rose-700 hover:bg-rose-50 transition">Batalkan Penolakan</button>
                        </form>
                    @endif

                    <a href="{{ route('admin.posts.index') }}" class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-3 font-black text-slate-700 hover:bg-slate-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Kembali ke Daftar
                    </a>
                </section>

                <section class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                    <h2 class="text-lg font-black text-slate-950 mb-5">Informasi Teknis</h2>
                    <dl class="space-y-3 text-sm">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-slate-500 font-bold mb-1">ID Karya</dt>
                            <dd class="font-mono text-xs text-slate-900 break-all">{{ $post['id'] }}</dd>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <dt class="text-slate-500 font-bold mb-1">ID User</dt>
                            <dd class="font-mono text-xs text-slate-900 break-all">{{ $post['user_id'] }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <dt class="text-slate-500 font-bold mb-1">Dibuat</dt>
                                <dd class="text-slate-900">{{ $createdAt ? $createdAt->format('d M Y H:i') : '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <dt class="text-slate-500 font-bold mb-1">Diperbarui</dt>
                                <dd class="text-slate-900">{{ $updatedAt ? $updatedAt->format('d M Y H:i') : '-' }}</dd>
                            </div>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
                <h3 class="text-lg font-black text-slate-900 mb-2">Karya Tidak Ditemukan</h3>
                <p class="text-slate-600 mb-6">Karya yang Anda cari tidak tersedia atau telah dihapus.</p>
                <a href="{{ route('admin.posts.index') }}" class="inline-flex px-5 py-3 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 transition">Kembali ke Daftar</a>
            </div>
        @endif
    </div>
</div>

<div id="revisionModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-950/60 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
        <form action="{{ route('admin.posts.request-revision', $post['id']) }}" method="POST">
            @csrf
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-xl font-black text-slate-950">Minta Revisi</h3>
                <p class="text-sm text-slate-500 mt-1">Tuliskan perbaikan yang perlu dilakukan penulis.</p>
            </div>
            <div class="p-6">
                <textarea name="revision_comment" rows="5" class="w-full rounded-xl border-slate-200 focus:border-amber-500 focus:ring-amber-500" required placeholder="Contoh: Mohon unggah dokumen laporan final dan perjelas deskripsi karya."></textarea>
            </div>
            <div class="p-6 bg-slate-50 flex gap-3 justify-end rounded-b-2xl">
                <button type="button" onclick="closeModerationModal('revisionModal')" class="px-4 py-2.5 rounded-xl font-black text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-600 font-black text-white hover:bg-amber-700 transition">Kirim</button>
            </div>
        </form>
    </div>
</div>

<div id="rejectModal" class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-950/60 p-4">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl">
        <form action="{{ route('admin.posts.reject', $post['id']) }}" method="POST">
            @csrf
            <div class="p-6 border-b border-slate-100">
                <h3 class="text-xl font-black text-slate-950">Tolak Karya</h3>
                <p class="text-sm text-slate-500 mt-1">Alasan ini akan membantu penulis memahami keputusan moderasi.</p>
            </div>
            <div class="p-6">
                <textarea name="rejection_reason" rows="5" class="w-full rounded-xl border-slate-200 focus:border-rose-500 focus:ring-rose-500" required placeholder="Tulis alasan penolakan dengan jelas."></textarea>
            </div>
            <div class="p-6 bg-slate-50 flex gap-3 justify-end rounded-b-2xl">
                <button type="button" onclick="closeModerationModal('rejectModal')" class="px-4 py-2.5 rounded-xl font-black text-slate-600 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-600 font-black text-white hover:bg-rose-700 transition">Tolak</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModerationModal(id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModerationModal(id) {
    const modal = document.getElementById(id);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>
@endsection
