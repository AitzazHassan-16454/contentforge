<?php

use App\Http\Controllers\AuthorController;
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
use App\Http\Controllers\SeoSuggestionsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [PostController::class, 'index'])->name('posts.index');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
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
Route::get('/authors/{user:username}', [AuthorController::class, 'show'])->name('authors.show');

Route::middleware('auth')->group(function () {
    Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::delete('/posts/{post:slug}/comments/{comment}', [CommentController::class, 'destroy'])->name('posts.comments.destroy');
    Route::post('/posts/{post:slug}/reactions', [PostReactionController::class, 'toggle'])->name('posts.reactions.toggle');
});

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
        ->middleware('throttle:30,1')
        ->name('dashboard.posts.seo-suggestions');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
