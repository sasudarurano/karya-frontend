/**
 * Dashboard Stats Synchronizer
 * Synchronizes dashboard stats (following count) across all pages
 * when follow/unfollow events occur
 */

const dashboardStatsSyncer = {
    STORAGE_KEY: 'karya-follow-state',
    STATS_KEY: 'karya-dashboard-stats',

    /**
     * Initialize stats synchronizer
     */
    init() {
        console.log('[DASHBOARD STATS] Initializing...');
        
        // Listen for follow state changes from other tabs/windows
        window.addEventListener('storage', (e) => {
            if (e.key === this.STORAGE_KEY) {
                console.log('[DASHBOARD STATS] Follow state changed in another tab, syncing...');
                this.syncFollowingCount();
            }
        });

        // Listen for custom follow event dispatched after follow/unfollow
        document.addEventListener('follow-changed', (e) => {
            console.log('[DASHBOARD STATS] Follow event received:', e.detail);
            this.updateFollowingCount(e.detail.delta);
        });
    },

    /**
     * Get current following count from DOM
     */
    getFollowingCountFromDOM() {
        const followingLink = document.querySelector('[href*="/following/"]');
        if (!followingLink) {
            console.warn('[DASHBOARD STATS] Following count element not found in DOM');
            return null;
        }

        const countSpan = followingLink.querySelector('span.block.font-extrabold');
        if (!countSpan) {
            console.warn('[DASHBOARD STATS] Count value not found');
            return null;
        }

        const count = parseInt(countSpan.textContent.trim(), 10);
        return isNaN(count) ? null : count;
    },

    /**
     * Update following count in DOM
     */
    updateFollowingCountInDOM(newCount) {
        const followingLink = document.querySelector('[href*="/following/"]');
        if (!followingLink) {
            console.warn('[DASHBOARD STATS] Following count element not found in DOM');
            return false;
        }

        const countSpan = followingLink.querySelector('span.block.font-extrabold');
        if (!countSpan) {
            console.warn('[DASHBOARD STATS] Count value not found');
            return false;
        }

        countSpan.textContent = newCount;
        console.log('[DASHBOARD STATS] Updated following count in DOM to:', newCount);
        return true;
    },

    /**
     * Update count by delta (increment/decrement)
     * delta: +1 for follow, -1 for unfollow
     */
    updateFollowingCount(delta) {
        const currentCount = this.getFollowingCountFromDOM();
        if (currentCount === null) {
            console.warn('[DASHBOARD STATS] Could not read current count');
            return;
        }

        const newCount = Math.max(0, currentCount + delta);
        this.updateFollowingCountInDOM(newCount);

        // Save to sessionStorage for reference
        sessionStorage.setItem(this.STATS_KEY, JSON.stringify({
            followingCount: newCount,
            updatedAt: new Date().toISOString()
        }));
    },

    /**
     * Sync following count by recounting from follow state
     * (when follow state changes in another tab)
     */
    syncFollowingCount() {
        console.log('[DASHBOARD STATS] Syncing following count from storage...');
        
        try {
            const followStateRaw = localStorage.getItem('karya-follow-state');
            if (!followStateRaw) {
                console.log('[DASHBOARD STATS] No follow state found');
                return;
            }

            const followStates = JSON.parse(followStateRaw);
            
            // Count how many users we're following
            const followingCount = Object.values(followStates).filter(isFollowing => isFollowing === true).length;
            
            console.log('[DASHBOARD STATS] Calculated following count:', followingCount);
            this.updateFollowingCountInDOM(followingCount);

            // Save to sessionStorage
            sessionStorage.setItem(this.STATS_KEY, JSON.stringify({
                followingCount: followingCount,
                updatedAt: new Date().toISOString()
            }));
        } catch (e) {
            console.error('[DASHBOARD STATS] Error syncing:', e);
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    dashboardStatsSyncer.init();
});
