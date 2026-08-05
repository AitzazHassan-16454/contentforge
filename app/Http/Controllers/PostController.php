<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a paginated listing of published posts, optionally filtered.
     */
    public function index(Request $request)
    {
        $posts = Post::query()
            ->published()
            ->with(['author:id,name', 'categories', 'tags'])
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('categories', fn ($q) => $q->where('categories.slug', $request->query('category')));
            })
            ->when($request->filled('tag'), function ($query) use ($request) {
                $query->whereHas('tags', fn ($q) => $q->where('tags.slug', $request->query('tag')));
            })
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return inertia('Posts/Index', [
            'posts' => $posts->through(fn (Post $post) => $this->present($post)),
        ]);
    }

    /**
     * Display posts in a single category.
     */
    public function category(Category $category)
    {
        $posts = Post::query()
            ->published()
            ->with(['author:id,name', 'categories', 'tags'])
            ->whereHas('categories', fn ($q) => $q->whereKey($category->getKey()))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return inertia('Posts/Index', [
            'heading' => $category->name,
            'posts' => $posts->through(fn (Post $post) => $this->present($post)),
        ]);
    }

    /**
     * Display posts with a single tag.
     */
    public function tag(Tag $tag)
    {
        $posts = Post::query()
            ->published()
            ->with(['author:id,name', 'categories', 'tags'])
            ->whereHas('tags', fn ($q) => $q->whereKey($tag->getKey()))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return inertia('Posts/Index', [
            'heading' => '#'.$tag->name,
            'posts' => $posts->through(fn (Post $post) => $this->present($post)),
        ]);
    }

    /**
     * Display a single published post.
     */
    public function show(Post $post)
    {
        abort_unless($post->isPublished(), 404);

        return inertia('Posts/Show', [
            'post' => $this->present($post->load('author:id,name', 'categories', 'tags')),
        ]);
    }

    /**
     * Prepare a post for presentation to the public.
     */
    private function present(Post $post): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'content' => $post->content,
            'excerpt' => $post->excerpt,
            'published_at' => $post->published_at?->diffForHumans(),
            'author' => $post->author?->name,
            'categories' => $post->categories->map(fn ($category) => $category->only('id', 'name', 'slug')),
            'tags' => $post->tags->map(fn ($tag) => $tag->only('id', 'name', 'slug')),
        ];
    }
}
