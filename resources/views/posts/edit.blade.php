@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Karya</h1>
        <p class="text-gray-500 mt-2">Perbarui informasi karya Anda</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-4 m-6 mb-0">
                <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 m-6 mb-0">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('posts.update', $post['id']) }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8"
              x-data="{ isGrouped: {{ $post['is_grouped'] == 1 || $post['is_grouped'] == true ? 'true' : 'false' }} }">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2">Informasi Karya</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Karya <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $post['title']) }}" required placeholder="Masukkan judul karya..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Tipe Karya <span class="text-red-500">*</span></label>
                        <div class="flex space-x-4">
                            <label class="flex items-center p-3 border rounded-xl cursor-pointer transition w-full" 
                                   :class="!isGrouped ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="is_grouped" value="false" x-model="isGrouped" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Individu</span>
                                    <span class="block text-xs text-gray-500">1 Anggota Saja</span>
                                </div>
                            </label>

                            <label class="flex items-center p-3 border rounded-xl cursor-pointer transition w-full"
                                   :class="isGrouped ? 'border-blue-500 bg-blue-50 ring-1 ring-blue-500' : 'border-gray-200 hover:bg-gray-50'">
                                <input type="radio" name="is_grouped" value="true" x-model="isGrouped" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Kelompok</span>
                                    <span class="block text-xs text-gray-500">Banyak Anggota</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="col-span-2 md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                        <select name="category" required class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
                            <option value="">Pilih Kategori...</option>
                            <option value="lomba" {{ old('category', $post['category']) == 'lomba' ? 'selected' : '' }}>🏆 Lomba</option>
                            <option value="tugas kelas" {{ old('category', $post['category']) == 'tugas kelas' ? 'selected' : '' }}>📚 Projek Akhir Kelas</option>
                            <option value="ta/skripsi" {{ old('category', $post['category']) == 'ta/skripsi' ? 'selected' : '' }}>🎓 TA / Skripsi</option>
                            <option value="kp/magang" {{ old('category', $post['category']) == 'kp/magang' ? 'selected' : '' }}>💼 KP / Magang</option>
                            <option value="penelitian/pkm" {{ old('category', $post['category']) == 'penelitian/pkm' ? 'selected' : '' }}>🔬 Penelitian / PKM</option>
                            <option value="project mandiri" {{ old('category', $post['category']) == 'project mandiri' ? 'selected' : '' }}>🚀 Project Mandiri</option>
                        </select>
                    </div>

                    <div class="col-span-2" x-data="searchableSelect({ 
                            options: @js($lecturers), 
                            value: '{{ old('supervisor_id', $post['supervisor']['nid'] ?? $post['supervisor_id'] ?? '') }}', 
                            placeholder: 'Cari nama dosen...',
                            emptyText: 'Tidak ada dosen pembimbing' 
                        })">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen Pembimbing <span class="text-gray-400 text-xs">(Opsional)</span></label>
                        <input type="hidden" name="supervisor_id" :value="selected || ''">
                        
                        <div class="relative" @click.away="open = false">
                            <div @click="open = !open" class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-white flex items-center justify-between cursor-pointer shadow-sm">
                                <span x-text="selectedLabel || emptyText" :class="selected ? 'text-gray-900' : 'text-gray-400'"></span>
                                <div class="flex items-center space-x-2">
                                    <button type="button" x-show="selected" @click.stop="clear()" class="text-gray-400 hover:text-red-500">✕</button>
                                    <span class="text-gray-400">▼</span>
                                </div>
                            </div>
                            <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-auto py-1" style="display: none;">
                                <div class="px-3 py-2 sticky top-0 bg-white border-b border-gray-100">
                                    <input x-model="search" type="text" placeholder="Ketik..." class="w-full text-sm border-gray-200 rounded-lg px-3 py-2 bg-gray-50 focus:outline-none">
                                </div>
                                <template x-for="item in filteredOptions" :key="item.id">
                                    <div @click="select(item.id)" class="px-4 py-2 hover:bg-blue-50 cursor-pointer">
                                        <div class="text-sm font-medium text-gray-800" x-text="item.full_name"></div>
                                        <div class="text-xs text-gray-500" x-show="item.username" x-text="item.username"></div>
                                    </div>
                                </template>
                                <div x-show="filteredOptions.length === 0" class="px-4 py-2 text-sm text-gray-500 text-center">Tidak ditemukan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Abstrak</label>
                    <textarea name="caption" rows="4" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500" placeholder="Jelaskan secara singkat...">{{ old('caption', $post['caption'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2">Tim & Anggota</h3>
                
                <div x-data="{ 
                        contributors: [],
                        allUsers: @js($students),
                        init() {
                            // Load existing contributors
                            const existing = @js($post['contributors'] ?? []);
                            if (existing.length > 0) {
                                this.contributors = existing.map((c, i) => ({ id: c.nid ?? c.id ?? c.user_id, key: Date.now() + i }));
                            } else if (!{{ var_export($post['is_grouped']) }}) {
                                this.contributors.push({ id: null, key: Date.now() });
                            }
                        }
                    }" x-init="init()">
                    
                    <label class="block text-sm font-medium text-gray-700 mb-3" x-text="isGrouped ? 'Daftar Anggota Kelompok' : 'Anggota / Penulis Utama'"></label>

                    <template x-for="(contributor, index) in contributors" :key="contributor.key">
                        <div class="flex items-start space-x-3 mb-3">
                            <div class="flex-1" x-data="searchableSelect({ 
                                    options: allUsers, 
                                    value: contributor.id ?? null, 
                                    placeholder: 'Pilih Anggota...',
                                    emptyText: 'Pilih Anggota'
                                })">
                                <input type="hidden" name="contributor_ids[]" :value="selected" @input="$el.value = selected">
                                
                                <div class="relative" @click.away="open = false">
                                    <div @click="open = !open" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 flex items-center justify-between cursor-pointer focus-within:ring-2 focus-within:ring-blue-500 transition" :class="selected ? 'border-green-500 bg-green-50 ring-2 ring-green-500' : 'hover:border-gray-400'">
                                        <span x-text="selectedLabel || placeholder" :class="selected ? 'text-gray-900 font-medium' : 'text-gray-400'"></span>
                                        <span x-text="selected ? '✓' : '▼'" :class="selected ? 'text-green-600 font-bold' : 'text-gray-400'"></span>
                                    </div>

                                    <div x-show="open" class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-auto py-1" style="display: none;">
                                        <div class="px-2 py-2 sticky top-0 bg-white border-b border-gray-100">
                                            <input x-model="search" type="text" class="w-full text-sm border-gray-200 rounded px-2 py-1 bg-gray-50 focus:outline-none" placeholder="Cari anggota...">
                                        </div>
                                        <template x-for="item in filteredOptions" :key="item.id">
                                            <div @click="select(item.id)" class="px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-50 last:border-0">
                                                <div class="text-sm font-medium" x-text="item.full_name"></div>
                                                <div class="text-xs text-gray-400" x-show="item.username" x-text="item.username"></div>
                                            </div>
                                        </template>
                                        <div x-show="filteredOptions.length === 0" class="px-3 py-2 text-sm text-gray-500 text-center">Tidak ditemukan</div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" x-show="isGrouped" @click="contributors.splice(index, 1)" class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition border border-gray-200">
                                🗑️
                            </button>
                        </div>
                    </template>

                    <button type="button" x-show="isGrouped" @click="contributors.push({id: null, key: Date.now()})" class="inline-flex items-center px-4 py-2 border border-blue-600 shadow-sm text-sm font-medium rounded-lg text-blue-600 bg-white hover:bg-blue-50 transition mt-2">
                        + Tambah Anggota
                    </button>
                    
                    <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-xs text-blue-700">
                            <strong>ℹ️ Catatan:</strong> 
                            <span x-show="!isGrouped">Jika Anda tidak memilih anggota, karya akan otomatis dikreditkan ke Anda saja.</span>
                            <span x-show="isGrouped">Pilih minimal 1 anggota. Jika tidak, karya akan dikreditkan ke Anda saja.</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2">Lampiran File</h3>

                {{-- Current Files --}}
                @if(!empty($post['attachments']) && count($post['attachments']) > 0)
                @php $existingIds = array_map(function($f){ return $f['id'] ?? null; }, $post['attachments']); @endphp
                <div x-data="{ 
                        existingIds: @js(array_values(array_filter($existingIds))), 
                        removeExisting(id){ 
                            this.existingIds = this.existingIds.filter(i => i !== id)
                        },
                        get existingIdsJson(){ return JSON.stringify(this.existingIds) }
                    }">
                    <label class="block text-sm font-medium text-gray-700 mb-3">File Saat Ini</label>
                    <input type="hidden" name="content_file_ids" :value="existingIdsJson">
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        @foreach($post['attachments'] as $file)
                        <div class="relative group" x-show="existingIds.includes('{{ $file['id'] ?? '' }}')" x-transition>
                            @php
                                $isImage = str_contains($file['mime'] ?? '', 'image');
                                $cleanPath = str_replace('\\', '/', $file['file_url'] ?? '');
                                if (str_starts_with($cleanPath, 'http')) {
                                    $imageUrl = $cleanPath;
                                } else {
                                    $backendUrl = rtrim(env('BACKEND_API_URL', 'http://localhost:3000'), '/');
                                    $imageUrl = $backendUrl . '/' . ltrim($cleanPath, '/');
                                }
                            @endphp
                            
                            @if($isImage)
                                <img src="{{ $imageUrl }}" alt="current file" class="w-full h-24 object-cover rounded-lg border border-gray-200">
                            @else
                                <div class="w-full h-24 bg-white rounded-lg border border-gray-200 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            <button type="button" @click="removeExisting('{{ $file['id'] ?? '' }}')" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 text-xs flex items-center justify-center shadow">×</button>
                            <p class="text-xs text-gray-600 mt-1 text-center truncate">{{ substr($file['filename'] ?? 'File', 0, 12) }}</p>
                        </div>
                        @endforeach
                    </div>
            
                </div>
                @endif

                {{-- Upload Foto --}}
                <div x-data="imageUploader()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Foto Karya Baru <span class="text-gray-400 text-xs">(Opsional, Max 1 file, PNG/JPG/JPEG, Maks 2.5MB)</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="cursor-pointer bg-white border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-50 font-medium flex items-center space-x-2 shadow-sm transition">
                            <span>📷 Pilih Foto</span>
                            <input id="imageInput" type="file" name="post_image" accept=".png,.jpg,.jpeg" class="hidden" @change="handleImage">
                        </label>
                        <div x-show="fileName" class="flex items-center space-x-3 bg-blue-50 text-blue-700 px-3 py-2 rounded-lg border border-blue-100" style="display: none;">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" class="w-10 h-10 object-cover rounded-md border border-blue-200">
                            </template>
                            <span class="text-sm font-medium truncate max-w-xs" x-text="fileName"></span>
                            <button type="button" @click="removeImage()" class="text-red-500 font-bold text-lg hover:text-red-700">×</button>
                        </div>
                    </div>
                </div>

                {{-- Upload Gdrive URL --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Link Dokumen / Gambar Karya (Google Drive) 
                    </label>
                    <input type="url" name="gdrive_url" value="{{ old('gdrive_url', $post['gdrive_url'] ?? '') }}" placeholder="https://drive.google.com/..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Pastikan akses link diubah ke "Anyone with the link"</p>
                </div>

                {{-- Upload PDF HKI --}}
                <div x-data="docUploader()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dokumen HKI / Laporan (PDF) <span class="text-gray-400 text-xs">(Opsional, Max 1 file)</span>
                    </label>
                    <div class="flex items-center space-x-4">
                        <label class="cursor-pointer bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 font-medium flex items-center space-x-2">
                            <span>📄 Pilih Dokumen PDF</span>
                            <input id="docInput" type="file" name="post_document" accept=".pdf" class="hidden" @change="handleDoc">
                        </label>
                        <div x-show="fileName" class="flex items-center bg-blue-50 text-blue-700 px-3 py-2 rounded-lg border border-blue-100" style="display: none;">
                            <span class="mr-2">📎</span>
                            <span class="text-sm font-medium truncate max-w-xs" x-text="fileName"></span>
                            <button type="button" @click="removeDoc()" class="ml-3 text-red-500 font-bold">×</button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ old('start_date', isset($post['start_date']) ? \Carbon\Carbon::parse($post['start_date'])->format('Y-m-d') : '') }}" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date', isset($post['end_date']) ? \Carbon\Carbon::parse($post['end_date'])->format('Y-m-d') : '') }}" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Youtube <span class="text-gray-400 text-xs">(Opsional)</span></label>
                        <input type="url" name="url_youtube" value="{{ old('url_youtube', $post['url_youtube'] ?? '') }}" placeholder="https://youtube.com/..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Repository <span class="text-gray-400 text-xs">(Opsional)</span></label>
                        <input type="url" name="url_karya" value="{{ old('url_karya', $post['url_karya'] ?? '') }}" placeholder="https://github.com/..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end space-x-3 border-t border-gray-100">
                <a href="{{ route('posts.my-posts') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition">Batal</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>

    function imageUploader() {
        return {
            fileName: null,
            previewUrl: null,
            handleImage(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2.5 * 1024 * 1024) {
                        alert('Error: Ukuran foto maksimal 2.5MB!');
                        e.target.value = '';
                        return;
                    }
                    this.fileName = file.name;
                    this.previewUrl = URL.createObjectURL(file);
                }
            },
            removeImage() {
                this.fileName = null;
                if (this.previewUrl) {
                    URL.revokeObjectURL(this.previewUrl);
                    this.previewUrl = null;
                }
                document.getElementById('imageInput').value = '';
            }
        }
    }

    function docUploader() {
        return {
            fileName: null,
            handleDoc(e) {
                const file = e.target.files[0];
                if (file) {
                    this.fileName = file.name;
                }
            },
            removeDoc() {
                this.fileName = null;
                document.getElementById('docInput').value = '';
            }
        }
    }

    function searchableSelect(config) {
        return {
            options: config.options || [],
            selected: config.value || null,
            search: '',
            open: false,
            placeholder: config.placeholder || 'Pilih...',
            emptyText: config.emptyText || 'Tidak ada opsi',
            get selectedLabel() {
                if (!this.selected) return null;
                const found = this.options.find(o => String(o.id) === String(this.selected));
                return found ? (found.full_name || found.username) : null;
            },
            get filteredOptions() {
                if (!this.search) return this.options;
                const query = this.search.toLowerCase();
                return this.options.filter(o => 
                    (o.full_name && o.full_name.toLowerCase().includes(query)) ||
                    (o.username && o.username.toLowerCase().includes(query))
                );
            },
            select(id) {
                this.selected = String(id);
                this.search = '';
                this.open = false;
            },
            clear() {
                this.selected = null;
                this.search = '';
            }
        }
    }

    // Clean up empty values and form submit for edit
    setTimeout(() => {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Remove empty contributor_ids
                const inputs = this.querySelectorAll('input[name="contributor_ids[]"]');
                inputs.forEach(input => {
                    if (!input.value || input.value.trim() === '' || input.value === 'null') {
                        input.remove();
                    }
                });
            });
        }
    }, 0);
</script>
@endsection
