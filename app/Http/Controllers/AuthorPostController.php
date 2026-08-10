<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
                'cover_image' => $post->coverImageUrl(),
                'status' => $post->status,
                'views' => $post->view_count,
                'published_at' => $post->published_at?->format('M j, Y'),
                'scheduled_at' => $post->scheduled_at?->format('M j, Y g:i A'),
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
                'cover_image' => null,
                'cover_image_url' => null,
                'cover_image_alt' => '',
                'gallery_images' => [],
                'gallery_image_urls' => [],
                'status' => Post::STATUS_DRAFT,
                'scheduled_at' => '',
                'category_ids' => [],
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
        $data = $request->validated();

        $post = $request->user()->posts()->create([
            ...$data,
            'cover_image' => $this->coverImage(null, $request),
            'cover_image_alt' => $data['cover_image_alt'] ?? $data['title'],
            'gallery_images' => $this->galleryImages(null, $request),
            'published_at' => $request->input('status') === Post::STATUS_PUBLISHED ? now() : null,
            'scheduled_at' => $this->scheduledAt($request),
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
                'cover_image' => $post->cover_image,
                'cover_image_url' => $post->coverImageUrl(),
                'cover_image_alt' => $post->cover_image_alt,
                'gallery_images' => $post->gallery_images ?? [],
                'gallery_image_urls' => $post->galleryImageUrls(),
                'status' => $post->status,
                'views' => $post->view_count,
                'scheduled_at' => $post->scheduled_at?->format('Y-m-d\TH:i') ?? '',
                'category_ids' => $post->categories->pluck('id'),
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

        $data = $request->validated();

        $post->update([
            ...$data,
            'cover_image' => $this->coverImage($post, $request),
            'cover_image_alt' => $data['cover_image_alt'] ?? $post->title,
            'gallery_images' => $this->galleryImages($post, $request),
            'published_at' => $request->input('status') === Post::STATUS_PUBLISHED
                ? ($post->published_at ?? now())
                : null,
            'scheduled_at' => $request->input('status') === Post::STATUS_PUBLISHED
                ? null
                : ($request->filled('scheduled_at')
                    ? Carbon::parse($request->input('scheduled_at'))
                    : ($request->has('scheduled_at') ? null : $post->scheduled_at)),
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

    /**
     * Resolve the post's cover image path from the incoming request,
     * storing newly uploaded files and removing replaced ones.
     */
    private function coverImage(?Post $post, Request $request): ?string
    {
        if ($request->boolean('remove_cover')) {
            $post?->cover_image && Storage::disk('public')->delete($post->cover_image);

            return null;
        }

        if ($request->hasFile('cover_image')) {
            $post?->cover_image && Storage::disk('public')->delete($post->cover_image);

            return $request->file('cover_image')->store('covers/'.$request->user()->id, 'public');
        }

        return $post?->cover_image;
    }

    /**
     * Resolve the gallery images.
     */
    private function galleryImages(?Post $post, Request $request): array
    {
        $images = $post?->gallery_images ?? [];

        // Remove deleted images
        if ($request->has('remove_gallery_images')) {
            $remove = $request->input('remove_gallery_images');
            foreach ($remove as $path) {
                if (in_array($path, $images)) {
                    Storage::disk('public')->delete($path);
                    $images = array_values(array_diff($images, [$path]));
                }
            }
        }

        // Add new images
        if ($request->hasFile('new_gallery_images')) {
            foreach ($request->file('new_gallery_images') as $file) {
                $images[] = $file->store('galleries/'.$request->user()->id, 'public');
            }
        }

        return $images;
    }

    /**
     * Resolve the scheduled publish time. Publishing immediately clears it.
     */
    private function scheduledAt(Request $request): ?Carbon
    {
        if ($request->input('status') === Post::STATUS_PUBLISHED) {
            return null;
        }

        return $request->filled('scheduled_at')
            ? Carbon::parse($request->input('scheduled_at'))
            : null;
    }
}
