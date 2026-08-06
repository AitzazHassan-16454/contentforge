<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;

class AuthorController extends Controller
{
    /**
     * Display a public profile for the author with their published posts.
     */
    public function show(User $user)
    {
        $posts = $user->posts()
            ->published()
            ->with(['categories', 'tags'])
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return inertia('Authors/Show', [
            'author' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'bio' => $user->bio,
                'post_count' => $user->posts()->published()->count(),
            ],
            'posts' => $posts->through(function (Post $post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'excerpt' => $post->excerpt,
                    'cover_image' => $post->coverImageUrl(),
                    'views' => $post->view_count,
                    'published_at' => $post->published_at?->diffForHumans(),
                    'categories' => $post->categories->map(fn ($category) => $category->only('id', 'name', 'slug')),
                    'tags' => $post->tags->map(fn ($tag) => $tag->only('id', 'name', 'slug')),
                ];
            }),
        ]);
    }
}
