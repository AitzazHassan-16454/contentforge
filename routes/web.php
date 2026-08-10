<?php

use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CommentController as AdminCommentController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthorController;
use App\Http\Controllers\AuthorAnalyticsController;
use App\Http\Controllers\AuthorPostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostReactionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TtsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');

Route::get('/search', [SearchController::class, 'index'])->name('posts.search');
Route::get('/categories/{category:slug}', [PostController::class, 'category'])->name('posts.category');
Route::get('/tags/{tag:slug}', [PostController::class, 'tag'])->name('posts.tag');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/posts/{post:slug}/audio', [TtsController::class, 'show'])
    ->middleware('throttle:5,1')
    ->name('posts.audio');
Route::get('/authors/{user:username}', [AuthorController::class, 'show'])->name('authors.show');

Route::middleware('auth')->group(function () {
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::delete('/posts/{post:slug}/comments/{comment}', [CommentController::class, 'destroy'])->name('posts.comments.destroy');
    Route::post('/posts/{post:slug}/reactions', [PostReactionController::class, 'toggle'])->name('posts.reactions.toggle');
});

Route::middleware('auth')->group(function () {
    Route::redirect('/dashboard', '/dashboard/posts')->name('dashboard');

    Route::get('/dashboard/analytics', [AuthorAnalyticsController::class, 'index'])->name('dashboard.analytics');

    Route::get('/dashboard/posts', [AuthorPostController::class, 'index'])->name('dashboard.posts.index');
    Route::get('/dashboard/posts/create', [AuthorPostController::class, 'create'])->name('dashboard.posts.create');    Route::post('/dashboard/posts', [AuthorPostController::class, 'store'])->name('dashboard.posts.store');
    Route::get('/dashboard/posts/{post}/edit', [AuthorPostController::class, 'edit'])->name('dashboard.posts.edit');
    Route::patch('/dashboard/posts/{post}', [AuthorPostController::class, 'update'])->name('dashboard.posts.update');
    Route::delete('/dashboard/posts/{post}', [AuthorPostController::class, 'destroy'])->name('dashboard.posts.destroy');
    Route::post('/dashboard/posts/{post}/publish', [AuthorPostController::class, 'publish'])->name('dashboard.posts.publish');
    Route::post('/dashboard/posts/{post}/unpublish', [AuthorPostController::class, 'unpublish'])->name('dashboard.posts.unpublish');

    Route::get('/dashboard/posts/ai/generate', [GenerateController::class, 'stream'])
        ->middleware('throttle:5,1')
        ->name('dashboard.posts.ai.generate');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin|moderator'])->group(function () {
    Route::redirect('/admin', '/admin/dashboard')->name('admin');

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/analytics', [AdminAnalyticsController::class, 'index'])->name('admin.analytics');

    Route::middleware('permission:manage taxonomy')->group(function () {
        Route::get('/admin/categories', [AdminCategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('/admin/categories', [AdminCategoryController::class, 'store'])->name('admin.categories.store');
        Route::patch('/admin/categories/{category}', [AdminCategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/admin/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('admin.categories.destroy');

        Route::get('/admin/tags', [AdminTagController::class, 'index'])->name('admin.tags.index');
        Route::post('/admin/tags', [AdminTagController::class, 'store'])->name('admin.tags.store');
        Route::patch('/admin/tags/{tag}', [AdminTagController::class, 'update'])->name('admin.tags.update');
        Route::delete('/admin/tags/{tag}', [AdminTagController::class, 'destroy'])->name('admin.tags.destroy');
    });

    Route::middleware('permission:moderate comments')->group(function () {
        Route::get('/admin/comments', [AdminCommentController::class, 'index'])->name('admin.comments.index');
        Route::post('/admin/comments/{comment}/approve', [AdminCommentController::class, 'approve'])->name('admin.comments.approve');
        Route::post('/admin/comments/{comment}/unapprove', [AdminCommentController::class, 'unapprove'])->name('admin.comments.unapprove');
        Route::delete('/admin/comments/{comment}', [AdminCommentController::class, 'destroy'])->name('admin.comments.destroy');
    });
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

    Route::middleware('permission:manage roles')->group(function () {
        Route::get('/admin/roles', [AdminRoleController::class, 'index'])->name('admin.roles.index');
        Route::post('/admin/roles', [AdminRoleController::class, 'store'])->name('admin.roles.store');
        Route::patch('/admin/roles/{role}', [AdminRoleController::class, 'update'])->name('admin.roles.update');
        Route::delete('/admin/roles/{role}', [AdminRoleController::class, 'destroy'])->name('admin.roles.destroy');
    });

    Route::get('/admin/settings', [AdminSettingController::class, 'edit'])->name('admin.settings.edit');
    Route::patch('/admin/settings', [AdminSettingController::class, 'update'])->name('admin.settings.update');
});

require __DIR__.'/auth.php';
