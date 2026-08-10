<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->withCount('posts')
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return inertia('Admin/Categories', [
            'categories' => $categories->through(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'posts_count' => $category->posts_count,
            ]),
            'filters' => $request->only('search'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return back()->with('success', 'Category created.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:categories,name,{$category->id}"],
        ]);

        $category->update($validated);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Cannot delete a category that still has posts.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
