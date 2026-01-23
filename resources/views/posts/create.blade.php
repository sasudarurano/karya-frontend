@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Upload Karya Baru</h1>
        <p class="text-gray-500 mt-2">Bagikan hasil karya, tugas, atau penelitian Anda kepada komunitas kampus.</p>
    </div>

    {{-- Warning untuk Mahasiswa yang Belum Divalidasi --}}
    @if(Session::has('user') && Session::get('user.role') === 'mhs' && !(Session::get('user.is_active') ?? false))
        <div class="bg-amber-50 border-l-4 border-amber-500 p-6 mb-6 rounded-r-xl shadow-sm">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-bold text-amber-800 mb-1">⏳ Akun Anda Belum Divalidasi</h3>
                    <p class="text-sm text-amber-700">
                        Akun mahasiswa Anda sedang menunggu validasi dari pihak kemahasiswaan atau kaprodi. 
                        <strong>Anda tidak dapat mengupload karya</strong> sampai akun Anda divalidasi.
                    </p>
                    <p class="text-sm text-amber-700 mt-2">
                        Silakan hubungi pihak kemahasiswaan untuk mempercepat proses validasi akun Anda.
                    </p>
                </div>
            </div>
        </div>
        
        {{-- Disable Form untuk Mahasiswa yang Belum Divalidasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden opacity-60 pointer-events-none">
    @else
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    @endif
        
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

        <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="p-8 space-y-8" 
              x-data="{ isGrouped: {{ old('is_grouped', 'false') == 'true' ? 'true' : 'false' }} }">
            @csrf

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2">Informasi Karya</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Karya <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required placeholder="Masukkan judul karya..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
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
                            <option value="lomba">🏆 Lomba</option>
                            <option value="tugas kelas">📚 Projek Akhir Kelas</option>
                            <option value="ta/skripsi">🎓 TA / Skripsi</option>
                            <option value="kp/magang">💼 KP / Magang</option>
                            <option value="penelitian/pkm">🔬 Penelitian / PKM</option>
                            <option value="project mandiri">🚀 Project Mandiri</option>
                        </select>
                    </div>

                    <div class="col-span-2" x-data="searchableSelect({ 
                            options: @js($users), 
                            value: '{{ old('supervisor_id') }}', 
                            placeholder: 'Cari nama dosen...',
                            emptyText: 'Tidak ada dosen' 
                        }}
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen <span class="text-gray-400 text-xs"></label>
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
                                        <div class="text-xs text-gray-500" x-text="item.username"></div>
                                    </div>
                                </template>
                                <div x-show="filteredOptions.length === 0" class="px-4 py-2 text-sm text-gray-500 text-center">Tidak ditemukan</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Abstrak</label>
                    <div id="editor" style="height: 250px;" class="bg-white rounded-xl border border-gray-300 shadow-sm"></div>
                    <textarea name="caption" id="caption" style="display: none;">{{ old('caption') }}</textarea>
                    <p class="text-xs text-gray-500 mt-2">Format teks, tambahkan bold, italic, list, dan link untuk deskripsi yang lebih menarik.</p>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-800 border-b border-gray-100 pb-2">Tim & Anggota</h3>
                
                <div x-data="{ 
                        contributors: [], 
                        allUsers: @js($users),
                        init() {
                            // Selalu mulai dengan 1 slot kosong jika belum ada
                            if (this.contributors.length === 0) {
                                this.contributors.push({ id: null, key: Date.now() });
                            }
                            // Watch perubahan mode Individu/Kelompok
                            this.$watch('isGrouped', value => {
                                if (value === false) {
                                    // Jika pindah ke Individu, potong jadi 1 saja
                                    this.contributors = this.contributors.slice(0, 1);
                                }
                            });
                        }
                    }" x-init="init()">
                    
                    <label class="block text-sm font-medium text-gray-700 mb-3" x-text="isGrouped ? 'Daftar Anggota Kelompok' : 'Anggota / Penulis Utama'"></label>

                    <template x-for="(contributor, index) in contributors" :key="contributor.key">
                        <div class="flex items-start space-x-3 mb-3">
                            <div class="flex-1" x-data="searchableSelect({ 
                                    options: allUsers, 
                                    value: null, 
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
                                                <div class="text-xs text-gray-400" x-text="item.username"></div>
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

                {{-- Upload Gambar --}}
                <div x-data="imageUploader()">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Dokumentasi Karya (Screenshoot,Foto Kreator,dll) <span class="text-red-500">*</span>
                        <span class="text-xs text-gray-400 font-normal ml-1">(Maksimal 5 gambar)</span>
                    </label>

                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 hover:bg-blue-50 transition relative group cursor-pointer"
                         @click="document.getElementById('imgInput').click()">
                        <div class="space-y-1 text-center">
                            <div class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-500 transition">📸</div>
                            <div class="text-sm text-gray-600">
                                <span class="font-medium text-blue-600 hover:text-blue-500">Klik untuk upload</span>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG, WebP (Max 5 File)</p>
                        </div>
                        <input id="imgInput" type="file" name="post_images[]" multiple accept="image/png,image/jpeg,image/webp" class="hidden" @change="handleImages">
                    </div>

                    <div x-show="images.length > 0" class="mt-4" style="display: none;">
                        <div class="relative bg-gray-900 rounded-xl overflow-hidden shadow-lg h-64 flex items-center justify-center">
                            <template x-for="(img, index) in images" :key="index">
                                <div x-show="currentIndex === index" class="absolute inset-0 flex items-center justify-center">
                                    <img :src="img.url" class="h-full w-auto object-contain">
                                </div>
                            </template>
                            <div class="absolute bottom-0 inset-x-0 bg-black/60 p-3 flex justify-between items-center text-white backdrop-blur-sm">
                                <span class="text-xs font-mono" x-text="(currentIndex + 1) + ' / ' + images.length"></span>
                                <button type="button" @click="removeCurrent()" class="text-xs bg-red-500 hover:bg-red-600 px-2 py-1 rounded">Hapus</button>
                            </div>
                            <button type="button" x-show="images.length > 1" @click="prev()" class="absolute left-2 text-white text-2xl">◀</button>
                            <button type="button" x-show="images.length > 1" @click="next()" class="absolute right-2 text-white text-2xl">▶</button>
                        </div>
                    </div>
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
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Youtube <span class="text-gray-400 text-xs">(Opsional)</span> </label>
                        <input type="url" name="url_youtube" placeholder="https://youtube.com/..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Link Repository <span class="text-gray-400 text-xs">(Opsional)</span> </label>
                        <input type="url" name="url_karya" placeholder="https://github.com/..." class="w-full border-gray-300 rounded-xl px-4 py-3 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="pt-6 flex justify-end space-x-3 border-t border-gray-100">
                <a href="{{ route('dashboard') }}" class="px-6 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition">Batal</a>
                <button type="submit" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition transform hover:-translate-y-0.5">🚀 Upload Karya</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    // 1. Logic Searchable Select
    Alpine.data('searchableSelect', ({ options, value, placeholder, emptyText }) => ({
        open: false,
        search: '',
        selected: value || null,
        options: options || [],
        placeholder: placeholder,
        emptyText: emptyText,
        get filteredOptions() {
            if (this.search === '') return this.options;
            return this.options.filter(item => 
                (item.full_name && item.full_name.toLowerCase().includes(this.search.toLowerCase())) ||
                (item.username && item.username.toLowerCase().includes(this.search.toLowerCase()))
            );
        },
        get selectedLabel() {
            if (!this.selected) return null;
            const item = this.options.find(i => i.id == this.selected);
            return item ? item.full_name : null;
        },
        select(id) {
            this.selected = id;
            this.open = false;
            this.search = '';
        },
        clear() {
            this.selected = null;
            this.search = '';
        }
    }));

    // 2. Logic Image Uploader
    Alpine.data('imageUploader', () => ({
        images: [],
        currentIndex: 0,
        handleImages(e) {
            const newFiles = Array.from(e.target.files);
            if (this.images.length + newFiles.length > 5) {
                alert('Maksimal 5 gambar!');
                e.target.value = '';
                return;
            }
            newFiles.forEach(file => {
                this.images.push({ file: file, url: URL.createObjectURL(file) });
            });
            this.syncInput();
        },
        removeCurrent() {
            this.images.splice(this.currentIndex, 1);
            if (this.currentIndex >= this.images.length) this.currentIndex = Math.max(0, this.images.length - 1);
            this.syncInput();
        },
        syncInput() {
            const dt = new DataTransfer();
            this.images.forEach(img => dt.items.add(img.file));
            document.getElementById('imgInput').files = dt.files;
        },
        prev() { this.currentIndex = (this.currentIndex > 0) ? this.currentIndex - 1 : this.images.length - 1; },
        next() { this.currentIndex = (this.currentIndex < this.images.length - 1) ? this.currentIndex + 1 : 0; }
    }));

    // 3. Logic Document Uploader
    Alpine.data('docUploader', () => ({
        fileName: null,
        handleDoc(e) {
            const file = e.target.files[0];
            if (file) this.fileName = file.name;
        },
        removeDoc() {
            this.fileName = null;
            document.getElementById('docInput').value = '';
        }
    }));

    // 4. Clean up empty contributor_ids and form submit
    setTimeout(() => {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('Form submit started');
                
                // Remove empty contributor_ids
                const inputs = this.querySelectorAll('input[name="contributor_ids[]"]');
                inputs.forEach(input => {
                    if (!input.value || input.value.trim() === '' || input.value === 'null') {
                        input.remove();
                    }
                });
                
                // Debug: Log form data
                const formData = new FormData(this);
                console.log('Form data being sent:');
                for (let [key, value] of formData.entries()) {
                    console.log(`  ${key}: ${value}`);
                }
                
                // Check if images are present
                const imageFiles = formData.getAll('post_images[]');
                console.log(`Images count: ${imageFiles.length}`);
                if (imageFiles.length === 0) {
                    console.error('ERROR: No images selected!');
                    alert('Error: Anda harus memilih minimal 1 gambar!');
                    e.preventDefault();
                    return false;
                }
            });
        }
    }, 50);
});
</script>

<!-- Quill Editor Library -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.0/dist/quill.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Quill Editor
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Jelaskan secara singkat tentang karya Anda...',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link'],
                    ['clean']
                ]
            }
        });

        // Load existing content if available
        const captionField = document.getElementById('caption');
        if (captionField && captionField.value) {
            quill.root.innerHTML = captionField.value;
        }

        // Update hidden textarea with editor content before form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Get editor content
                const editorContent = quill.root.innerHTML;
                
                // Update hidden textarea
                captionField.value = editorContent;
                
                // Log for debugging
                console.log('Editor content saved:', editorContent);
            });
        }

        // Optional: Auto-save to hidden textarea on content change
        quill.on('text-change', function(delta, oldDelta, source) {
            if (source === 'user') {
                captionField.value = quill.root.innerHTML;
            }
        });
    });
</script>

@endsection