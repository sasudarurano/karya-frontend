/**
 * Global Like Manager - Handles like/bookmark across all pages
 * Syncs with localStorage for bookmark persistence
 */

const likeManager = {
    storageKey: 'karya-liked-posts',
    
    // Normalize any stored value to an object map { postId: true }
    normalize(raw) {
        if (!raw) return {};
        let parsed;
        try {
            parsed = JSON.parse(raw);
        } catch (e) {
            console.warn('[LIKE_MANAGER] Corrupted JSON, resetting storage');
            return {};
        }

        // If already an object map, return as-is
        if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
            return parsed;
        }

        // If it's an array or other format, convert to object map
        if (Array.isArray(parsed)) {
            const obj = {};
            parsed.forEach(val => {
                if (val) obj[val] = true;
            });
            console.warn('[LIKE_MANAGER] Converted legacy array storage to object map');
            return obj;
        }

        return {};
    },
    
    /**
     * Get all liked posts from localStorage
     */
    getLikedPosts() {
        try {
            const raw = localStorage.getItem(this.storageKey);
            const normalized = this.normalize(raw);
            // Persist normalized data back
            localStorage.setItem(this.storageKey, JSON.stringify(normalized));
            return normalized;
        } catch (e) {
            console.error('[LIKE_MANAGER] Failed to read localStorage', e);
            return {};
        }
    },
    
    /**
     * Check if a post is liked
     */
    isLiked(postId) {
        const liked = this.getLikedPosts();
        return liked[postId] === true;
    },
    
    /**
     * Save like state to localStorage
     */
    saveLike(postId, isLiked) {
        const liked = this.getLikedPosts();
        if (isLiked) {
            liked[postId] = true;
        } else {
            delete liked[postId];
        }
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(liked));
            console.log('[LIKE_MANAGER] Saved:', postId, isLiked);
        } catch (e) {
            console.error('[LIKE_MANAGER] Failed to save', e);
        }
    },
    
    /**
     * Toggle like on a post
     * @param {string|number} postId - The post ID
     * @param {HTMLElement} button - The like button element
     * @param {string} apiBase - Backend API base URL
     * @param {string} apiToken - User API token
     * @returns {Promise}
     */
    async toggleLike(postId, button, apiBase, apiToken) {
        if (!apiToken) {
            window.location.href = '/login';
            return;
        }
        
        const currentState = button.dataset.liked === 'true';
        button.disabled = true;
        const originalOpacity = button.style.opacity;
        button.style.opacity = '0.6';
        
        const url = `${apiBase}/posts/${postId}/vote`;
        console.log('[LIKE_MANAGER] Request:', url, 'Current state:', currentState);
        
        try {
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${apiToken}`,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ vote_type: true })
            });
            
            console.log('[LIKE_MANAGER] Response status:', response.status);
            
            // Handle 401 Unauthorized
            if (response.status === 401) {
                alert('Sesi Anda telah berakhir. Silakan login kembali.');
                window.location.href = '/login';
                return;
            }
            
            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                console.error('[LIKE_MANAGER] Non-JSON response');
                throw new Error('Server mengembalikan response yang tidak valid.');
            }
            
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log('[LIKE_MANAGER] Response data:', data);
            
            if (data.success !== false && data.message) {
                // Determine new state based on backend response
                const likedNow = data.message !== 'Vote removed';
                const likeCount = data.data?.likeCount || 0;
                
                // Save to localStorage for bookmark
                this.saveLike(postId, likedNow);
                
                // Update UI
                this.updateButtonUI(button, postId, likedNow, likeCount);
                
                console.log(`[LIKE_MANAGER] ${likedNow ? '✅ Liked' : '❌ Unliked'} post ${postId}`);
            } else {
                throw new Error(data.message || 'Gagal memproses like');
            }
        } catch (error) {
            console.error('[LIKE_MANAGER] Error:', error);
            alert('Terjadi kesalahan: ' + error.message);
        } finally {
            button.disabled = false;
            button.style.opacity = originalOpacity || '1';
        }
    },
    
    /**
     * Update button UI based on like state
     */
    updateButtonUI(button, postId, isLiked, likeCount) {
        // Update button data attribute
        button.dataset.liked = isLiked ? 'true' : 'false';
        
        // Find SVG icon
        const svg = button.querySelector('svg');
        if (svg) {
            svg.setAttribute('fill', isLiked ? 'currentColor' : 'none');
        }
        
        // Update classes for color
        if (isLiked) {
            button.classList.remove('text-gray-400', 'text-slate-400');
            button.classList.add('text-red-500', 'text-rose-500');
        } else {
            button.classList.remove('text-red-500', 'text-rose-500');
            button.classList.add('text-gray-400', 'text-slate-400');
        }
        
        // Update like count if element exists
        const countSelectors = [
            `.like-count-${postId}`,
            `#likeCount`,
            button.dataset.likeCountSelector
        ];
        
        for (const selector of countSelectors) {
            if (!selector) continue;
            const countElement = document.querySelector(selector);
            if (countElement) {
                countElement.textContent = likeCount;
                console.log('[LIKE_MANAGER] Updated count:', selector, likeCount);
            }
        }
    },
    
    /**
     * Initialize like buttons on page load
     * Syncs with localStorage state
     */
    initializeLikeButtons() {
        const buttons = document.querySelectorAll('[data-post-like-button]');
        console.log('[LIKE_MANAGER] Initializing', buttons.length, 'like buttons');
        
        buttons.forEach(button => {
            const postId = button.dataset.postLikeButton || button.dataset.postId;
            if (!postId) return;
            
            const localLiked = this.isLiked(postId);
            const serverLiked = button.dataset.liked === 'true';
            
            // Use server state as source of truth on initial load
            const shouldBeLiked = serverLiked;
            
            // Update localStorage to match server
            this.saveLike(postId, shouldBeLiked);
            
            console.log(`[LIKE_MANAGER] Post ${postId}: Server=${serverLiked}, Local=${localLiked}, Final=${shouldBeLiked}`);
        });
    }
};

/**
 * Global function for like button clicks
 * Can be called from any page
 */
window.toggleLikeCard = function(postId, button) {
    const apiBase = window.globalApiBase || document.querySelector('meta[name="api-base"]')?.content || 'http://localhost:3000/api';
    const apiToken = window.globalApiToken || document.querySelector('meta[name="api-token"]')?.content;
    likeManager.toggleLike(postId, button, apiBase, apiToken);
};

window.toggleLikeSearch = window.toggleLikeCard;
window.toggleLikeDashboard = window.toggleLikeCard;

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        likeManager.initializeLikeButtons();
    });
} else {
    likeManager.initializeLikeButtons();
}

console.log('[LIKE_MANAGER] Loaded successfully');
