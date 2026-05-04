<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\PostManagementController;
use App\Http\Controllers\Admin\ProgramStudiController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Debug Routes (Temporary - Remove in Production)
|--------------------------------------------------------------------------
*/
if (config('app.debug')) {
    Route::get('/debug/test-post/{id}', function($id) {
        $api = app(\App\Services\KaryaApi::class);
        $response = $api->getPostById($id);
        return [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'data' => $response->json(),
        ];
    })->name('debug.test-post');
    
    Route::get('/debug/my-posts', function() {
        $api = app(\App\Services\KaryaApi::class);
        $response = $api->getMyPosts();
        return [
            'status' => $response->status(),
            'successful' => $response->successful(),
            'data' => $response->json(),
        ];
    })->name('debug.my-posts');
}

/*
|--------------------------------------------------------------------------
| Public Routes (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/

// Halaman Utama (Home) - Menampilkan karya Populer & Terbaru
Route::get('/', [PostController::class, 'index'])->name('home');

// Halaman Detail Karya
Route::get('/karya/{id}', [PostController::class, 'show'])->name('posts.show');

// Proxy Image - Menampilkan gambar dari Backend Node.js
Route::get('/post-image', [PostController::class, 'getPostImage'])->name('posts.image');

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Forgot Password & Reset Password
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('reset-password');

// Registration
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);


/*
|--------------------------------------------------------------------------
| Protected Routes (Harus Login / Mahasiswa)
|--------------------------------------------------------------------------
*/
Route::middleware(['session.auth'])->group(function () {
    // Dashboard: dialihkan sesuai role
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/feed', [DashboardController::class, 'feed'])->name('dashboard.feed');
    
    // Manajemen Karya Mahasiswa: Daftar karya milik sendiri (My Posts)
    Route::get('/my-posts', [PostController::class, 'myPosts'])->name('posts.my-posts');
    
    // Fitur Upload Karya Baru
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    
    // Edit & Delete Post
    Route::get('/posts/{id}/edit', [PostManagementController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{id}', [PostManagementController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostManagementController::class, 'destroy'])->name('posts.destroy');
    
    // Comments
    Route::post('/posts/{postId}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/posts/{postId}/comments/{commentId}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/posts/{postId}/comments/{commentId}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    // Votes (Like/Dislike)
    Route::post('/posts/{postId}/vote', [VoteController::class, 'vote'])->name('posts.vote');
    Route::delete('/posts/{postId}/vote', [VoteController::class, 'unvote'])->name('posts.unvote');
    
    // Follow System
    Route::post('/users/{userId}/follow', [FollowController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{userId}/unfollow', [FollowController::class, 'unfollow'])->name('users.unfollow');
    Route::get('/users/{userId}/followers', [FollowController::class, 'followers'])->name('users.followers');
    Route::get('/users/{userId}/following', [FollowController::class, 'following'])->name('users.following');
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::patch('/api/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::patch('/api/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
    Route::delete('/api/notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
    
    // Bookmarks (Karya Favorit) - dari localStorage
    Route::get('/bookmarks', function() {
        return view('bookmarks');
    })->name('bookmarks');
    
    // API Routes (untuk AJAX calls dengan prefix /api)
    Route::prefix('api')->group(function () {
        // Comments API
        Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
        Route::put('/posts/{postId}/comments/{commentId}', [CommentController::class, 'update']);
        Route::delete('/posts/{postId}/comments/{commentId}', [CommentController::class, 'destroy']);
        
        // Votes (Like) API - PATCH method untuk toggle
        Route::patch('/posts/{postId}/vote', [VoteController::class, 'toggleVote']);
        
        // Follow API - DELETE untuk unfollow juga menggunakan /follow
        Route::post('/users/{userId}/follow', [FollowController::class, 'follow']);
        Route::delete('/users/{userId}/follow', [FollowController::class, 'unfollow']);
    });
    
    // Profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Profile Publik
Route::get('/profile/{userId}', [ProfileController::class, 'show'])->name('profile.show');


/*
|--------------------------------------------------------------------------
| Admin Routes (Khusus Verifikator / Admin)
|--------------------------------------------------------------------------
| Menggunakan prefix 'admin' dan penamaan rute 'admin.'
| Roles yang dapat akses: superadmin, kemahasiswaan, verifikator, kaprodi, dosen pembimbing
*/
Route::middleware(['session.auth', \App\Http\Middleware\CheckAdminRole::class])->prefix('admin')->name('admin.')->group(function () {
    // Halaman Kurasi Karya: Melihat semua karya termasuk yang is_published: false
    Route::get('/posts', [AdminPostController::class, 'index'])->name('posts.index');
    
    // Detail Karya: Lihat detail lengkap karya (untuk moderasi)
    Route::get('/posts/{id}', [AdminPostController::class, 'show'])->name('posts.show');
    
    // Tombol Publish/Unpublish: Mengubah status publikasi karya (PATCH)
    Route::patch('/posts/{id}/toggle-publish', [AdminPostController::class, 'togglePublish'])->name('posts.toggle-publish');
    
    // Minta Revisi: Mengirim komentar revisi ke user (POST)
    Route::post('/posts/{id}/request-revision', [AdminPostController::class, 'requestRevision'])->name('posts.request-revision');
    
    // Tolak Karya: Menolak publikasi karya dengan alasan (POST)
    Route::post('/posts/{id}/reject', [AdminPostController::class, 'reject'])->name('posts.reject');
    // Batalkan Penolakan
    Route::post('/posts/{id}/clear-rejection', [AdminPostController::class, 'clearRejection'])->name('posts.clear-rejection');
    
    // Program Studi Management
    Route::get('/program-studi', [ProgramStudiController::class, 'index'])->name('program-studi.index');
    Route::get('/program-studi/create', [ProgramStudiController::class, 'create'])->name('program-studi.create');
    Route::post('/program-studi', [ProgramStudiController::class, 'store'])->name('program-studi.store');
    Route::get('/program-studi/{id}/edit', [ProgramStudiController::class, 'edit'])->name('program-studi.edit');
    Route::put('/program-studi/{id}', [ProgramStudiController::class, 'update'])->name('program-studi.update');
    Route::delete('/program-studi/{id}', [ProgramStudiController::class, 'destroy'])->name('program-studi.destroy');
});

// User Management Routes (Superadmin only)
Route::middleware(['session.auth', \App\Http\Middleware\CheckSuperAdminRole::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
    Route::patch('/users/{id}/change-to-verifikator', [AdminUserController::class, 'changeToVerifikator'])->name('users.change-to-verifikator');
    Route::post('/users/{id}/verify', [AdminUserController::class, 'verifyUser'])->name('users.verify');
});