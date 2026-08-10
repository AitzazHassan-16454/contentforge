<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $tags = Tag::query()
            ->withCount('posts')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return inertia('Admin/Tags', [
            'tags' => $tags->through(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'posts_count' => $tag->posts_count,
            ]),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tags,name'],
        ]);

        Tag::create($validated);

        return back()->with('success', 'Tag created.');
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:tags,name,{$tag->id}"],
        ]);

        $tag->update($validated);

        return back()->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->posts()->detach();
        $tag->delete();

        return back()->with('success', 'Tag deleted.');
    }
}
