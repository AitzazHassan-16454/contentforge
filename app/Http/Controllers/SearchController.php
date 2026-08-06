<?php

namespace App\Http\Controllers;

use App\Services\SemanticSearch;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Search published posts by semantic similarity.
     */
    public function index(Request $request, SemanticSearch $search)
    {
        $query = trim((string) $request->query('q', ''));

        $results = $query !== '' ? $search->search($query, limit: 5) : [];

        return inertia('Posts/Search', [
            'query' => $query,
            'results' => collect($results)
                ->map(fn (array $result): array => $this->present($result))
                ->values(),
        ]);
    }

    /**
     * Prepare a semantic search result for the client.
     *
     * @param  array{post: \App\Models\Post, score: float, snippet: string}  $result
     */
    private function present(array $result): array
    {
        $post = $result['post'];

        return [
            'id' => $post->id,
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'published_at' => $post->published_at?->diffForHumans(),
            'author' => $post->author?->name,
            'categories' => $post->categories->map(fn ($category) => $category->only('id', 'name', 'slug')),
            'tags' => $post->tags->map(fn ($tag) => $tag->only('id', 'name', 'slug')),
            'snippet' => $result['snippet'],
            'score' => $result['score'],
        ];
    }
}
