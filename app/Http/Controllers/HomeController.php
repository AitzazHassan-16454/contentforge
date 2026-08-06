<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Render the marketing homepage.
     */
    public function index()
    {
        $posts = Post::query()
            ->published()
            ->with(['author:id,name,username', 'categories', 'tags'])
            ->latest('published_at')
            ->take(3)
            ->get()
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'cover_image' => $post->coverImageUrl(),
                'published_at' => $post->published_at?->diffForHumans(),
                'author' => $post->author?->name,
                'author_username' => $post->author?->username,
                'categories' => $post->categories->map(fn ($category) => $category->only('id', 'name', 'slug')),
            ]);

        $publishedCount = Post::query()->published()->count();
        $authorCount = User::query()->whereHas('posts', fn ($query) => $query->published())->count();
        $totalViews = Post::query()->published()->sum('view_count');

        return inertia('Home', [
            'posts' => $posts,
            'stats' => [
                'posts' => $publishedCount,
                'authors' => $authorCount,
                'views' => $totalViews,
            ],
            'plans' => config('plans'),
        ]);
    }
}
