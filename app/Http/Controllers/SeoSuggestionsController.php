<?php

namespace App\Http\Controllers;

use App\Ai\Agents\SeoSuggestions;
use App\Http\Requests\SeoSuggestionsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SeoSuggestionsController extends Controller
{
    /**
     * Generate AI-powered SEO and tag suggestions for a draft post.
     */
    public function store(SeoSuggestionsRequest $request): JsonResponse
    {
        $agent = new SeoSuggestions(
            title: $request->input('title'),
            content: $request->input('content'),
            currentTags: $request->input('tags', []),
            currentExcerpt: $request->input('excerpt'),
        );

        $response = $agent->prompt($agent->userPrompt(), timeout: 60);

        $suggestions = $this->parse($response->text);

        return response()->json([
            'title' => $suggestions['title'] ?? null,
            'meta_description' => $suggestions['meta_description'] ?? null,
            'tags' => $suggestions['tags'] ?? [],
        ]);
    }

    /**
     * Parse the agent's JSON response defensively.
     *
     * @return array<string, mixed>
     */
    private function parse(string $text): array
    {
        $text = trim($text);
        $text = preg_replace('/^```json\s*/i', '', $text);
        $text = preg_replace('/```\s*$/', '', $text);

        $decoded = json_decode($text, true);

        if (! is_array($decoded)) {
            Log::warning('Unparseable SEO suggestions from AI', ['response' => $text]);

            return [];
        }

        return array_filter([
            'title' => is_string($decoded['title'] ?? null) ? trim($decoded['title']) : null,
            'meta_description' => is_string($decoded['meta_description'] ?? null) ? trim($decoded['meta_description']) : null,
            'tags' => collect($decoded['tags'] ?? [])
                ->map(fn ($tag) => is_string($tag) ? trim($tag) : null)
                ->filter()
                ->values()
                ->all(),
        ], fn ($value) => $value !== null && $value !== []);
    }
}
