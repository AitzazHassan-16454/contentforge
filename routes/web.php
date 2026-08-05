<?php

use App\Http\Controllers\AuthorPostController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SeoSuggestionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/categories/{category:slug}', [PostController::class, 'category'])->name('posts.category');
Route::get('/tags/{tag:slug}', [PostController::class, 'tag'])->name('posts.tag');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::middleware('auth')->group(function () {
    Route::redirect('/dashboard', '/dashboard/posts')->name('dashboard');

    Route::get('/dashboard/posts', [AuthorPostController::class, 'index'])->name('dashboard.posts.index');
    Route::get('/dashboard/posts/create', [AuthorPostController::class, 'create'])->name('dashboard.posts.create');
    Route::post('/dashboard/posts', [AuthorPostController::class, 'store'])->name('dashboard.posts.store');
    Route::get('/dashboard/posts/{post}/edit', [AuthorPostController::class, 'edit'])->name('dashboard.posts.edit');
    Route::patch('/dashboard/posts/{post}', [AuthorPostController::class, 'update'])->name('dashboard.posts.update');
    Route::delete('/dashboard/posts/{post}', [AuthorPostController::class, 'destroy'])->name('dashboard.posts.destroy');
    Route::post('/dashboard/posts/{post}/publish', [AuthorPostController::class, 'publish'])->name('dashboard.posts.publish');
    Route::post('/dashboard/posts/{post}/unpublish', [AuthorPostController::class, 'unpublish'])->name('dashboard.posts.unpublish');

    Route::get('/dashboard/posts/ai/generate', [GenerateController::class, 'stream'])
        ->middleware('throttle:5,1')
        ->name('dashboard.posts.ai.generate');

    Route::post('/dashboard/posts/seo-suggestions', [SeoSuggestionsController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('dashboard.posts.seo-suggestions');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
