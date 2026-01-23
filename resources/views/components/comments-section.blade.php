{{-- Comment Section Component --}}
@props(['postId', 'comments' => []])

<div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <h3 class="text-xl font-bold text-gray-900 mb-6">
        Komentar ({{ count($comments) }})
    </h3>

    {{-- Comment Form (Only for logged in users) --}}
    @if(Session::has('api_token'))
    <form id="commentForm" class="mb-8">
        @csrf
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr(Session::get('user')['username'] ?? 'U', 0, 1)) }}
                </div>
            </div>
            <div class="flex-1">
                <textarea id="commentContent" name="content" rows="3" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                          placeholder="Tulis komentar Anda..."></textarea>
                <div class="mt-2 flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm" id="submitCommentBtn">
                        Kirim Komentar
                    </button>
                </div>
            </div>
        </div>
    </form>
    @else
    <div class="mb-8 p-4 bg-gray-50 rounded-lg text-center">
        <p class="text-gray-600">
            <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:underline">Login</a> untuk memberikan komentar
        </p>
    </div>
    @endif

    {{-- Comments List --}}
    @if(count($comments) > 0)
    <div class="space-y-4">
        @foreach($comments as $comment)
        <div class="flex gap-3 pb-4 border-b border-gray-100 last:border-0">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-gradient-to-br from-gray-400 to-gray-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr($comment['author']['username'] ?? 'U', 0, 1)) }}
                </div>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <span class="font-semibold text-gray-900">{{ $comment['author']['full_name'] ?? $comment['author']['username'] }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ \Carbon\Carbon::parse($comment['created_at'])->diffForHumans() }}</span>
                    </div>
                     
                    {{-- Edit/Delete buttons (only for comment owner) --}}
                    @if(Session::has('user') && Session::get('user')['id'] === $comment['user_id'])
                    <div class="flex gap-2">
                        <button type="button" class="comment-edit-btn text-xs text-blue-600 hover:underline font-medium" data-comment-id="{{ $comment['id'] }}">
                            Edit
                        </button>
                        <button type="button" class="comment-delete-btn text-xs text-red-600 hover:underline font-medium" data-comment-id="{{ $comment['id'] }}">
                            Hapus
                        </button>
                    </div>
                    @endif
                </div>
                <p class="text-gray-700 whitespace-pre-wrap" id="content-{{ $comment['id'] }}">{{ e($comment['comment']) }}</p>
                
                {{-- Edit Form (Hidden by default) --}}
                <div id="form-{{ $comment['id'] }}" class="hidden mt-2">
                    <textarea id="textarea-{{ $comment['id'] }}" rows="2" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none">{{ e($comment['comment']) }}</textarea>
                    <div class="mt-2 flex gap-2">
                        <button type="button" class="comment-save-btn px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700 font-medium" data-comment-id="{{ $comment['id'] }}">
                            Simpan
                        </button>
                        <button type="button" class="comment-cancel-btn px-3 py-1 bg-gray-200 text-gray-700 rounded text-sm hover:bg-gray-300 font-medium" data-comment-id="{{ $comment['id'] }}">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-8 text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
    </div>
    @endif
</div>

<script type="text/javascript">
const commentPostId = @json($postId);
const commentCsrfToken = @json(csrf_token());
const commentApiToken = @json(Session::get('api_token', ''));
const commentApiBase = @json(rtrim(env('BACKEND_API_URL', 'http://localhost:3000/api'), '/'));

// Namespace untuk UI operations
const commentUI = {
    edit(commentId) {
        console.log('[COMMENT UI] Edit clicked for:', commentId);
        const contentEl = document.getElementById('content-' + commentId);
        const formEl = document.getElementById('form-' + commentId);
        if (contentEl) contentEl.classList.add('hidden');
        if (formEl) formEl.classList.remove('hidden');
    },

    cancel(commentId) {
        console.log('[COMMENT UI] Cancel clicked for:', commentId);
        const contentEl = document.getElementById('content-' + commentId);
        const formEl = document.getElementById('form-' + commentId);
        if (contentEl) contentEl.classList.remove('hidden');
        if (formEl) formEl.classList.add('hidden');
    },

    save(commentId) {
        console.log('[COMMENT UI] Save clicked for:', commentId);
        const textarea = document.getElementById('textarea-' + commentId);
        const content = textarea ? textarea.value.trim() : '';

        if (!content) {
            alert('Komentar tidak boleh kosong');
            return;
        }

        // Disable button saat proses
        const buttons = document.querySelectorAll('#form-' + commentId + ' button');
        buttons.forEach(btn => btn.disabled = true);

        const url = `${commentApiBase}/posts/${commentPostId}/comments/${commentId}`;
        console.log('[COMMENT SAVE] Sending PUT to:', url);
        console.log('[COMMENT SAVE] Payload:', { comment: content });

        fetch(url, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${commentApiToken}`
            },
            credentials: 'include',
            body: JSON.stringify({ comment: content })
        })
        .then(response => {
            console.log('[COMMENT SAVE] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('[COMMENT SAVE] Response data:', data);
            if (data && (data.success === true || data.status === 'success')) {
                console.log('[COMMENT SAVE] Success!');
                // Update DOM langsung tanpa reload
                const contentEl = document.getElementById('content-' + commentId);
                if (contentEl) {
                    contentEl.textContent = content;
                }
                commentUI.cancel(commentId);
                alert('Komentar berhasil diperbarui');
            } else {
                throw new Error(data?.message || 'Gagal mengupdate komentar');
            }
        })
        .catch(error => {
            console.error('[COMMENT SAVE] Error:', error);
            alert('Gagal mengupdate: ' + error.message);
        })
        .finally(() => {
            buttons.forEach(btn => btn.disabled = false);
        });
    },

    delete(commentId) {
        console.log('[COMMENT UI] Delete clicked for:', commentId);
        if (!confirm('Hapus komentar ini? Tindakan ini tidak bisa dibatalkan.')) {
            return;
        }

        const url = `${commentApiBase}/posts/${commentPostId}/comments/${commentId}`;
        console.log('[COMMENT DELETE] Sending DELETE to:', url);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${commentApiToken}`
            },
            credentials: 'include'
        })
        .then(response => {
            console.log('[COMMENT DELETE] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('[COMMENT DELETE] Response data:', data);
            if (data && (data.success === true || data.status === 'success')) {
                console.log('[COMMENT DELETE] Success!');
                // Hapus comment element dari DOM
                const commentDiv = document.getElementById('content-' + commentId).closest('.flex.gap-3');
                if (commentDiv) {
                    commentDiv.style.opacity = '0.5';
                    setTimeout(() => {
                        commentDiv.remove();
                    }, 300);
                }
                alert('Komentar berhasil dihapus');
            } else {
                throw new Error(data?.message || 'Gagal menghapus komentar');
            }
        })
        .catch(error => {
            console.error('[COMMENT DELETE] Error:', error);
            alert('Gagal menghapus: ' + error.message);
        });
    }
};

// Comment Form AJAX Handler
document.addEventListener('DOMContentLoaded', function() {
    console.log('[COMMENT] Initializing event listeners...');
    
    // Edit button - Event Delegation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.comment-edit-btn');
        if (btn) {
            const commentId = btn.getAttribute('data-comment-id');
            console.log('[COMMENT EDIT BTN] Button element:', btn);
            console.log('[COMMENT EDIT BTN] data-comment-id attribute value:', commentId);
            if (commentId) {
                commentUI.edit(commentId);
            } else {
                console.error('[COMMENT EDIT BTN] Comment ID is empty or missing!');
            }
        }
    });

    // Delete button - Event Delegation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.comment-delete-btn');
        if (btn) {
            const commentId = btn.getAttribute('data-comment-id');
            console.log('[COMMENT DELETE BTN] Button element:', btn);
            console.log('[COMMENT DELETE BTN] data-comment-id attribute value:', commentId);
            if (commentId) {
                commentUI.delete(commentId);
            } else {
                console.error('[COMMENT DELETE BTN] Comment ID is empty or missing!');
            }
        }
    });

    // Save button - Event Delegation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.comment-save-btn');
        if (btn) {
            const commentId = btn.getAttribute('data-comment-id');
            console.log('[COMMENT SAVE BTN] Button element:', btn);
            console.log('[COMMENT SAVE BTN] data-comment-id attribute value:', commentId);
            if (commentId) {
                commentUI.save(commentId);
            } else {
                console.error('[COMMENT SAVE BTN] Comment ID is empty or missing!');
            }
        }
    });

    // Cancel button - Event Delegation
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.comment-cancel-btn');
        if (btn) {
            const commentId = btn.getAttribute('data-comment-id');
            console.log('[COMMENT CANCEL BTN] Button element:', btn);
            console.log('[COMMENT CANCEL BTN] data-comment-id attribute value:', commentId);
            if (commentId) {
                commentUI.cancel(commentId);
            } else {
                console.error('[COMMENT CANCEL BTN] Comment ID is empty or missing!');
            }
        }
    });

    // Comment Form Handler
    const commentForm = document.getElementById('commentForm');
    if (!commentForm) {
        console.log('[COMMENT] Comment form not found');
        return;
    }
    
    console.log('[COMMENT] Comment form initialized');

    commentForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        console.log('[COMMENT] Form submitted');
        
        const content = document.getElementById('commentContent').value.trim();
        const submitBtn = document.getElementById('submitCommentBtn');
        
        console.log('[COMMENT] Content:', content);
        console.log('[COMMENT] API Token:', commentApiToken ? 'exists' : 'missing');
        
        if (!content) {
            alert('Komentar tidak boleh kosong');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengirim...';
        
        console.log('[COMMENT] Sending POST to /api/posts/' + commentPostId + '/comments');

        fetch(`${commentApiBase}/posts/${commentPostId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${commentApiToken}`
            },
            credentials: 'include',
            body: JSON.stringify({ comment: content })
        })
        .then(response => {
            console.log('[COMMENT] Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('[COMMENT] Response data:', data);
            if (data && (data.success === true || data.status === 'success')) {
                console.log('[COMMENT] Success!');
                // Refresh halaman untuk menampilkan komentar baru
                location.reload();
            } else {
                throw new Error(data?.message || 'Gagal menambahkan komentar');
            }
        })
        .catch(error => {
            console.error('[COMMENT] Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
            submitBtn.disabled = false;
            submitBtn.textContent = 'Kirim Komentar';
        });
    });
});
</script>
