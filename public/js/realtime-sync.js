(function () {
    const postPollMs = 10000;
    const notificationPollMs = 10000;
    const state = {
        postTimer: null,
        notificationTimer: null,
        postSyncInFlight: false,
        notificationSyncInFlight: false
    };

    function apiBase() {
        return (window.globalApiBase || document.querySelector('meta[name="api-base"]')?.content || '').replace(/\/$/, '');
    }

    function apiToken() {
        return window.globalApiToken || document.querySelector('meta[name="api-token"]')?.content || '';
    }

    function canSync() {
        return document.visibilityState !== 'hidden' && apiBase();
    }

    function selectorValue(value) {
        return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
    }

    function getPostIds() {
        const ids = new Set();

        document.querySelectorAll('[data-post-like-button], [data-post-id]').forEach((element) => {
            const postId = element.dataset.postLikeButton || element.dataset.postId;
            if (postId) ids.add(String(postId));
        });

        if (window.postId) ids.add(String(window.postId));

        return [...ids].filter(Boolean);
    }

    async function fetchPost(postId) {
        const headers = { Accept: 'application/json' };
        const token = apiToken();
        if (token) headers.Authorization = `Bearer ${token}`;

        const response = await fetch(`${apiBase()}/posts/${postId}`, { headers });
        if (!response.ok) return null;

        const result = await response.json();
        return result.data || null;
    }

    function updatePostUI(postId, post) {
        if (!post) return;

        const likeCount = Number(post.likeCount || 0);
        const commentCount = Number(post.commentCount);
        const isLiked = Boolean(post.isLiked);
        const safePostId = selectorValue(postId);

        document.querySelectorAll(`.like-count-${safePostId}`).forEach((element) => {
            element.textContent = likeCount;
        });

        if (Number.isFinite(commentCount)) {
            document.querySelectorAll(`.comment-count-${safePostId}`).forEach((element) => {
                element.textContent = commentCount;
            });
        }

        document.querySelectorAll(`[data-post-like-button="${safePostId}"]`).forEach((button) => {
            if (window.likeManager?.updateButtonUI) {
                window.likeManager.updateButtonUI(button, postId, isLiked, likeCount);
            } else {
                button.dataset.liked = isLiked ? 'true' : 'false';
            }
        });

        if (String(window.postId || '') === String(postId)) {
            if (window.likeStateManager?.updateUI) {
                window.likeStateManager.updateUI(postId, isLiked, likeCount);
            } else {
                const likeCountElement = document.getElementById('likeCount');
                if (likeCountElement) likeCountElement.textContent = likeCount;
            }

            const detailCommentCount = document.getElementById('commentCount');
            if (detailCommentCount && Number.isFinite(commentCount)) {
                detailCommentCount.textContent = commentCount;
            }
        }
    }

    async function syncPosts() {
        if (!canSync() || state.postSyncInFlight) return;

        const postIds = getPostIds();
        if (!postIds.length) return;

        state.postSyncInFlight = true;

        try {
            await Promise.all(postIds.map(async (postId) => {
                try {
                    const post = await fetchPost(postId);
                    updatePostUI(postId, post);
                } catch (error) {
                    console.warn('[REALTIME_SYNC] Failed to sync post:', postId, error);
                }
            }));
        } finally {
            state.postSyncInFlight = false;
        }
    }

    async function syncNotifications() {
        if (!canSync() || !apiToken() || state.notificationSyncInFlight) return;

        state.notificationSyncInFlight = true;

        try {
            if (typeof window.fetchUnreadCount === 'function') {
                await window.fetchUnreadCount();
            }

            const notificationPanel = document.getElementById('notification-list');
            const panelVisible = notificationPanel && notificationPanel.offsetParent !== null;
            if (panelVisible && typeof window.loadNotifications === 'function') {
                await window.loadNotifications();
            }
        } catch (error) {
            console.warn('[REALTIME_SYNC] Failed to sync notifications:', error);
        } finally {
            state.notificationSyncInFlight = false;
        }
    }

    function syncNow() {
        syncPosts();
        syncNotifications();
    }

    function start() {
        stop();
        syncNow();
        state.postTimer = window.setInterval(syncPosts, postPollMs);
        state.notificationTimer = window.setInterval(syncNotifications, notificationPollMs);
    }

    function stop() {
        if (state.postTimer) window.clearInterval(state.postTimer);
        if (state.notificationTimer) window.clearInterval(state.notificationTimer);
        state.postTimer = null;
        state.notificationTimer = null;
    }

    window.KaryaRealtimeSync = { start, stop, syncNow, syncPosts, syncNotifications };

    window.addEventListener('karya:sync-now', syncNow);
    window.addEventListener('karya:like-changed', (event) => {
        const detail = event.detail || {};
        if (detail.postId) {
            updatePostUI(detail.postId, {
                likeCount: detail.likeCount,
                commentCount: Number.NaN,
                isLiked: detail.isLiked
            });
        }
        window.setTimeout(syncPosts, 600);
    });

    window.addEventListener('storage', (event) => {
        if (event.key === 'karya-sync-event') syncNow();
    });

    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') syncNow();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
