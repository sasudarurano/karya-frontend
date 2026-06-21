/**
 * Post Show Page - Reactive State Management
 * Handles: Like button state, Follow button state, Image slider
 */

// ============================================================================
// LIKE STATE MANAGER - Handle like/bookmark persistence
// ============================================================================
const likeStateManager = {
    storageKey: 'karya-liked-posts',

    normalize(raw) {
        if (!raw) return {};
        let parsed;
        try {
            parsed = JSON.parse(raw);
        } catch (e) {
            console.warn('[LIKE] Corrupted JSON, resetting storage');
            return {};
        }
        if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) return parsed;
        if (Array.isArray(parsed)) {
            const obj = {};
            parsed.forEach(val => { if (val) obj[val] = true; });
            console.warn('[LIKE] Converted legacy array storage to object map');
            return obj;
        }
        return {};
    },
    
    // Get all liked posts from localStorage
    getLikedPosts() {
        try {
            const raw = localStorage.getItem(this.storageKey);
            const normalized = this.normalize(raw);
            localStorage.setItem(this.storageKey, JSON.stringify(normalized));
            return normalized;
        } catch (e) {
            console.error('[LIKE] Failed to read localStorage', e);
            return {};
        }
    },
    
    // Check if a post is liked
    isLiked(postId) {
        const liked = this.getLikedPosts();
        return liked[postId] === true;
    },
    
    // Save like state to localStorage
    saveLike(postId, isLiked) {
        const liked = this.getLikedPosts();
        if (isLiked) {
            liked[postId] = true;
        } else {
            delete liked[postId];
        }
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(liked));
            console.log('[LIKE] Saved to localStorage:', postId, isLiked);
        } catch (e) {
            console.error('[LIKE] Failed to save', e);
        }
    },
    
    // Update UI to reflect like state
    updateUI(postId, isLiked, likeCount) {
        const btn = document.getElementById('likeButton');
        const icon = document.getElementById('likeIcon');
        const count = document.getElementById('likeCount');
        
        if (!btn) return;
        
        btn.dataset.liked = isLiked ? 'true' : 'false';
        
        if (icon) {
            icon.setAttribute('fill', isLiked ? 'currentColor' : 'none');
        }
        
        btn.classList.remove('text-gray-500', 'hover:text-red-500', 'text-red-500');
        
        if (isLiked) {
            btn.classList.add('text-red-500');
        } else {
            btn.classList.add('text-gray-500', 'hover:text-red-500');
        }
        
        if (count) {
            count.textContent = likeCount;
        }
        
        console.log('[LIKE] UI Updated - Liked:', isLiked, 'Count:', likeCount);
    }
};

window.likeStateManager = likeStateManager;

// ============================================================================
// FOLLOW STATE MANAGER - Handle follow/unfollow persistence
// ============================================================================
const followStateManager = {
    storageKey: 'karya-follow-state',
    
    // Get all follow states from localStorage
    getFollowStates() {
        try {
            const raw = localStorage.getItem(this.storageKey);
            return raw ? JSON.parse(raw) : {};
        } catch (e) {
            console.error('[FOLLOW] Failed to read localStorage', e);
            return {};
        }
    },
    
    // Check if current user is following target user
    isFollowing(targetId) {
        const states = this.getFollowStates();
        return states[targetId] === true;
    },
    
    // Save follow state to localStorage
    saveFollow(targetId, isFollowing) {
        const states = this.getFollowStates();
        if (isFollowing) {
            states[targetId] = true;
        } else {
            delete states[targetId];
        }
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(states));
            console.log('[FOLLOW] Saved to localStorage:', states);
        } catch (e) {
            console.error('[FOLLOW] Failed to save', e);
        }
    },
    
    // Update UI to reflect follow state
    updateUI(targetId, isFollowing) {
        const btn = document.getElementById('followButton');
        if (!btn) return;
        
        btn.dataset.isFollowing = isFollowing ? 'true' : 'false';
        
        if (isFollowing) {
            btn.textContent = '✓ Mengikuti';
            btn.style.backgroundColor = '#dcfce7';
            btn.style.color = '#15803d';
        } else {
            btn.textContent = '+ Ikuti';
            btn.style.backgroundColor = '#e8f0ff';
            btn.style.color = '#0066cc';
        }
        
        console.log('[FOLLOW] UI Updated - Following:', isFollowing);
    }
};

window.followStateManager = followStateManager;

// ============================================================================
// LIKE BUTTON HANDLER
// ============================================================================
function toggleLike() {
    if (!isLoggedIn) {
        window.location.href = loginUrl;
        return;
    }

    const btn = document.getElementById('likeButton');
    if (!btn) return;

    const currentState = btn.dataset.liked === 'true';
    btn.disabled = true;
    btn.style.opacity = '0.6';

    const url = `${apiBase}/posts/${postId}/vote`;
    console.log('[LIKE] Requesting:', url);
    console.log('[LIKE] Token:', apiToken ? 'Present' : 'Missing');

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiToken}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ vote_type: true })
    })
    .then(response => {
        console.log('[LIKE] Response status:', response.status);
        console.log('[LIKE] Response headers:', response.headers.get('content-type'));
        
        // Handle 401 Unauthorized
        if (response.status === 401) {
            alert('Sesi Anda telah berakhir. Silakan login kembali.');
            window.location.href = loginUrl;
            throw new Error('Unauthorized');
        }
        
        // Check if response is JSON or HTML
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('[LIKE] Server returned non-JSON response');
            console.error('[LIKE] Content-Type:', contentType);
            throw new Error('Server mengembalikan response yang tidak valid. Silakan login ulang.');
        }
        
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('[LIKE] Server Response:', data);

        if (data.success !== false && data.message) {
            // Determine new like state based on backend message
            const likedNow = data.message !== 'Vote removed';
            const likeCount = data.data?.likeCount || 0;

            // Save to localStorage
            likeStateManager.saveLike(postId, likedNow);

            // Update UI
            likeStateManager.updateUI(postId, likedNow, likeCount);

            window.dispatchEvent(new CustomEvent('karya:like-changed', {
                detail: { postId: String(postId), isLiked: likedNow, likeCount }
            }));
            window.dispatchEvent(new CustomEvent('karya:sync-now', {
                detail: { postId: String(postId), isLiked: likedNow, likeCount }
            }));

            try {
                localStorage.setItem('karya-sync-event', JSON.stringify({
                    type: 'like',
                    postId: String(postId),
                    isLiked: likedNow,
                    likeCount,
                    at: Date.now()
                }));
            } catch (e) {
                console.warn('[LIKE] Failed to broadcast sync event', e);
            }

            // Show feedback
            if (likedNow) {
                console.log('✅ Post liked dan ditambahkan ke bookmarks');
            } else {
                console.log('❌ Post dihapus dari like');
            }
        } else {
            alert(data.message || 'Gagal memproses like');
        }
    })
    .catch(err => {
        console.error('[LIKE] Error:', err);
        alert('Terjadi kesalahan saat memproses like: ' + err.message);
    })
    .finally(() => {
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}

// ============================================================================
// FOLLOW BUTTON HANDLER
// ============================================================================
function toggleFollow() {
    if (!isLoggedIn) {
        window.location.href = loginUrl;
        return;
    }

    const btn = document.getElementById('followButton');
    if (!btn) return;

    const currentState = btn.dataset.isFollowing === 'true';
    
    // If already following, ask for confirmation
    if (currentState) {
        const confirmed = confirm('Apakah Anda yakin ingin berhenti mengikuti user ini? Post dari user ini akan hilang dari dashboard Anda.');
        if (!confirmed) return;
    }
    
    const method = currentState ? 'DELETE' : 'POST';
    
    btn.disabled = true;
    btn.style.opacity = '0.6';
    const originalText = btn.textContent;
    btn.textContent = 'Memproses...';

    const url = `${apiBase}/users/${authorId}/follow`;
    console.log('[FOLLOW] Requesting:', url);
    console.log('[FOLLOW] Token:', apiToken ? 'Present' : 'Missing');

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${apiToken}`,
            'Accept': 'application/json'
        },
        credentials: 'include'
    })
    .then(response => {
        console.log('[FOLLOW] Response status:', response.status);
        console.log('[FOLLOW] Response headers:', response.headers.get('content-type'));
        
        // Handle 401 Unauthorized
        if (response.status === 401) {
            alert('Sesi Anda telah berakhir. Silakan login kembali.');
            window.location.href = loginUrl;
            throw new Error('Unauthorized');
        }
        
        // Check if response is JSON or HTML
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('[FOLLOW] Server returned non-JSON response');
            console.error('[FOLLOW] Content-Type:', contentType);
            throw new Error('Server mengembalikan response yang tidak valid. Silakan login ulang.');
        }
        
        if (!response.ok) {
            return response.json().then(errorData => {
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        console.log('[FOLLOW] Server Response:', data);

        if (data.success || data.message) {
            const isFollowingNow = !currentState;

            // Save to localStorage
            followStateManager.saveFollow(authorId, isFollowingNow);

            // Update UI
            followStateManager.updateUI(authorId, isFollowingNow);

            // Dispatch event for dashboard stats sync
            const delta = isFollowingNow ? 1 : -1;
            const event = new CustomEvent('follow-changed', {
                detail: { userId: authorId, isFollowing: isFollowingNow, delta: delta }
            });
            document.dispatchEvent(event);
            console.log('[FOLLOW EVENT] Dispatched with delta:', delta);

            // Show feedback
            if (isFollowingNow) {
                console.log('✅ Anda sekarang mengikuti user ini');
            } else {
                console.log('❌ Anda berhenti mengikuti user ini');
            }
        } else {
            alert(data.message || 'Gagal memproses follow');
            btn.textContent = originalText;
        }
    })
    .catch(err => {
        console.error('[FOLLOW] Error:', err);
        alert('Terjadi kesalahan saat memproses follow: ' + err.message);
        btn.textContent = originalText;
    })
    .finally(() => {
        btn.disabled = false;
        btn.style.opacity = '1';
    });
}

// ============================================================================
// IMAGE SLIDER - Handle multi-image posts
// ============================================================================
let currentSlide = 0;
const slides = document.querySelectorAll('.slide-item');
const dots = document.querySelectorAll('.dot-indicator');

function showSlide(index) {
    if (!slides.length) return;

    if (index >= slides.length) currentSlide = 0;
    else if (index < 0) currentSlide = slides.length - 1;
    else currentSlide = index;

    slides.forEach((s, i) => {
        s.classList.toggle('opacity-100', i === currentSlide);
        s.classList.toggle('z-10', i === currentSlide);
        s.classList.toggle('opacity-0', i !== currentSlide);
        s.classList.toggle('z-0', i !== currentSlide);
    });

    dots.forEach((d, i) => {
        d.classList.toggle('bg-white', i === currentSlide);
        d.classList.toggle('w-8', i === currentSlide);
        d.classList.toggle('bg-white/50', i !== currentSlide);
    });
}

function changeSlide(n) {
    showSlide(currentSlide + n);
}

function goToSlide(i) {
    showSlide(i);
}

// ============================================================================
// PAGE INITIALIZATION - Sync state on page load
// ============================================================================
document.addEventListener('DOMContentLoaded', () => {
    console.log('[INIT] Initializing show page...');
    console.log('Current User ID:', currentUserId);
    console.log('Post ID:', postId);
    console.log('Author ID:', authorId);

    // Initialize Like Button
    const likeBtn = document.getElementById('likeButton');
    if (likeBtn) {
        // Check server state first (most authoritative on initial load)
        const serverLiked = likeBtn.dataset.liked === 'true';
        
        // Get like count from the span element's text
        const countSpan = document.getElementById('likeCount');
        const likeCountText = countSpan ? countSpan.textContent : '';
        const likeCount = parseInt(likeCountText.match(/\d+/)?.[0] || '0') || 0;

        // Check localStorage for persisted state from previous session
        const localLiked = likeStateManager.isLiked(postId);

        // Use server state as source of truth on page load
        const shouldBeLiked = serverLiked;
        
        console.log('[LIKE] Init - Server:', serverLiked, 'Local:', localLiked, 'Final:', shouldBeLiked, 'Count:', likeCount);
        
        // Update UI and persist to localStorage
        likeStateManager.updateUI(postId, shouldBeLiked, likeCount);
        likeStateManager.saveLike(postId, shouldBeLiked);
    }

    // Initialize Follow Button (only if not own profile)
    const followBtn = document.getElementById('followButton');
    if (followBtn && authorId && authorId !== currentUserId) {
        // Check server state first (most authoritative on initial load)
        const serverFollowing = followBtn.dataset.isFollowing === 'true';

        // Check localStorage for persisted state from previous session
        const localFollowing = followStateManager.isFollowing(authorId);

        // Use server state as source of truth on page load
        const shouldBeFollowing = serverFollowing;

        console.log('[FOLLOW] Init - Server:', serverFollowing, 'Local:', localFollowing, 'Final:', shouldBeFollowing);

        // Update UI and persist to localStorage
        followStateManager.updateUI(authorId, shouldBeFollowing);
        followStateManager.saveFollow(authorId, shouldBeFollowing);
    }

    console.log('[INIT] Page initialization complete');
});

// ============================================================================
// LISTEN TO STORAGE CHANGES - Sync across tabs/windows
// ============================================================================
window.addEventListener('storage', (e) => {
    // Sync likes when changed in another tab
    if (e.key === likeStateManager.storageKey) {
        console.log('[LIKE] Storage changed in another tab');
        const liked = likeStateManager.isLiked(postId);
        const btn = document.getElementById('likeButton');
        if (btn) {
            const count = parseInt(btn.textContent.match(/\d+/) || [0])[0] || 0;
            likeStateManager.updateUI(postId, liked, count);
        }
    }

    // Sync follows when changed in another tab
    if (e.key === followStateManager.storageKey) {
        console.log('[FOLLOW] Storage changed in another tab');
        const following = followStateManager.isFollowing(authorId);
        followStateManager.updateUI(authorId, following);
    }
});
