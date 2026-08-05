<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthorPostController extends Controller
{
    /**
     * Display a listing of the authenticated user's posts.
     */
    public function index(Request $request)
    {
        $posts = $request->user()
            ->posts()
            ->with(['categories', 'tags'])
            ->orderByDesc('updated_at')
            ->paginate(12);

        return inertia('Dashboard/Posts/Index', [
            'posts' => $posts->through(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'status' => $post->status,
                'published_at' => $post->published_at?->format('M j, Y'),
                'updated_at' => $post->updated_at->diffForHumans(),
                'categories' => $post->categories->map(fn ($category) => $category->only('id', 'name', 'slug')),
                'tags' => $post->tags->map(fn ($tag) => $tag->only('id', 'name', 'slug')),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        return inertia('Dashboard/Posts/Edit', [
            'post' => [
                'title' => '',
                'slug' => '',
                'excerpt' => '',
                'content' => '',
                'status' => Post::STATUS_DRAFT,
                'categories' => [],
                'tags' => [],
            ],
            'categories' => $this->categories(),
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = $request->user()->posts()->create([
            ...$request->validated(),
            'published_at' => $request->input('status') === Post::STATUS_PUBLISHED ? now() : null,
        ]);

        $post->syncTaxonomy(
            $request->input('category_ids', []),
            $request->input('tags', []),
        );

        return redirect()
            ->route('dashboard.posts.edit', $post)
            ->with('success', 'Post created.');
    }

    /**
     * Show the form for editing the given post.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        $post->load(['categories', 'tags']);

        return inertia('Dashboard/Posts/Edit', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'status' => $post->status,
                'categories' => $post->categories->pluck('id'),
                'tags' => $post->tags->pluck('name'),
            ],
            'categories' => $this->categories(),
        ]);
    }

    /**
     * Update the given post.
     */
    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $post->update([
            ...$request->validated(),
            'published_at' => $request->input('status') === Post::STATUS_PUBLISHED
                ? ($post->published_at ?? now())
                : null,
        ]);

        $post->syncTaxonomy(
            $request->input('category_ids', []),
            $request->input('tags', []),
        );

        return redirect()
            ->route('dashboard.posts.edit', $post)
            ->with('success', 'Post saved.');
    }

    /**
     * Remove the given post.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()
            ->route('dashboard.posts.index')
            ->with('success', 'Post deleted.');
    }

    /**
     * Publish the given post.
     */
    public function publish(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);

        $post->publish();

        return back()->with('success', 'Post published.');
    }

    /**
     * Unpublish the given post.
     */
    public function unpublish(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);

        $post->unpublish();

        return back()->with('success', 'Post unpublished.');
    }

    /**
     * Categories available for assignment in the editor.
     */
    private function categories(): array
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->toArray();
    }
}
