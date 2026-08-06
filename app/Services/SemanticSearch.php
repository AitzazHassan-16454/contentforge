<?php

namespace App\Services;

use App\Models\Post;
use App\Models\PostChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Throwable;

class SemanticSearch
{
    protected const CHUNK_SIZE = 700;

    protected const CHUNK_OVERLAP = 120;

    /**
     * Split a post's content into overlapping text chunks for embedding.
     *
     * @return array<int, string>
     */
    public function chunkContent(string $content, int $size = self::CHUNK_SIZE, int $overlap = self::CHUNK_OVERLAP): array
    {
        $content = trim(preg_replace('/\s+/u', ' ', $content) ?? '');
        $length = mb_strlen($content);

        if ($length === 0) {
            return [];
        }

        if ($length <= $size) {
            return [$content];
        }

        $chunks = [];
        $offset = 0;

        while ($offset < $length) {
            $chunk = mb_substr($content, $offset, $size);
            $chunkLength = mb_strlen($chunk);

            if ($offset + $chunkLength < $length) {
                $lastSpace = mb_strrpos($chunk, ' ');

                if ($lastSpace !== false && $lastSpace > $size / 2) {
                    $chunk = mb_substr($chunk, 0, $lastSpace);
                    $chunkLength = mb_strlen($chunk);
                }
            }

            $chunks[] = $chunk;

            $step = $chunkLength - $overlap;

            $offset += max(1, $step);
        }

        return $chunks;
    }

    /**
     * Replace a post's stored chunks with freshly generated embeddings.
     *
     * Non-published posts have their chunks cleared so only live content is searchable.
     */
    public function reindex(Post $post): void
    {
        $post->chunks()->delete();

        if (! $post->isPublished() || blank($post->content)) {
            return;
        }

        $chunks = $this->chunkContent($post->content);

        if ($chunks === []) {
            return;
        }

        $embeddings = $this->embed($chunks, $post);

        if ($embeddings === null) {
            return;
        }

        $rows = [];

        foreach ($chunks as $index => $content) {
            $rows[] = [
                'post_id' => $post->id,
                'chunk_index' => $index,
                'content' => $content,
                'embedding' => $embeddings[$index] ?? [],
            ];
        }

        $post->chunks()->createMany($rows);
    }

    /**
     * Search published posts by semantic similarity to the given query.
     *
     * @return array<int, array{post: Post, score: float, snippet: string}>
     */
    public function search(string $query, int $limit = 5): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $queryEmbedding = $this->embed([$query]);

        if ($queryEmbedding === null) {
            return [];
        }

        $queryVector = $queryEmbedding[0] ?? null;

        if ($queryVector === null) {
            return [];
        }

        $chunks = PostChunk::query()
            ->join('posts', 'posts.id', '=', 'post_chunks.post_id')
            ->where('posts.status', Post::STATUS_PUBLISHED)
            ->whereNotNull('posts.published_at')
            ->get(['post_chunks.*']);

        $bestScore = [];
        $bestChunk = [];

        foreach ($chunks as $chunk) {
            $vector = $chunk->embedding;

            if ($vector === []) {
                continue;
            }

            $score = $this->cosineSimilarity($queryVector, $vector);

            if (($bestScore[$chunk->post_id] ?? -INF) < $score) {
                $bestScore[$chunk->post_id] = $score;
                $bestChunk[$chunk->post_id] = $chunk;
            }
        }

        if ($bestChunk === []) {
            return [];
        }

        arsort($bestScore);
        $bestScore = array_slice($bestScore, 0, $limit, true);

        $posts = Post::query()
            ->with(['author:id,name', 'categories', 'tags'])
            ->whereIn('id', array_keys($bestScore))
            ->get()
            ->keyBy('id');

        return collect($bestScore)
            ->map(fn (float $score, int $postId): ?array => $this->buildResult(
                $posts->get($postId),
                $bestChunk[$postId] ?? null,
                $score,
                $query,
            ))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Cosine similarity between two embedding vectors.
     *
     * @param  array<float>  $a
     * @param  array<float>  $b
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $count = min(count($a), count($b));

        for ($i = 0; $i < $count; $i++) {
            $dot += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / ($normA * $normB);
    }

    /**
     * Generate embeddings for the given inputs, degrading gracefully on failure.
     *
     * @param  array<int, string>  $inputs
     *
     * @return array<int, array<float>>|null
     */
    protected function embed(array $inputs, ?Post $post = null): ?array
    {
        try {
            return Embeddings::for($inputs)->generate('gemini')->embeddings;
        } catch (Throwable $e) {
            Log::warning('Embeddings generation failed', [
                'post_id' => $post?->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Assemble a single search result for a post.
     *
     * @return array{post: Post, score: float, snippet: string}|null
     */
    protected function buildResult(?Post $post, ?PostChunk $chunk, float $score, string $query): ?array
    {
        if ($post === null || $chunk === null) {
            return null;
        }

        return [
            'post' => $post,
            'score' => round($score, 4),
            'snippet' => $this->snippet($chunk->content, $query),
        ];
    }

    /**
     * Build a short snippet centered on the first query term match.
     */
    protected function snippet(string $content, string $query): string
    {
        $content = trim(preg_replace('/\s+/u', ' ', $content) ?? '');
        $words = preg_split('/\s+/u', trim($query)) ?? [];

        $position = null;

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $found = mb_stripos($content, $word);

            if ($found !== false && ($position === null || $found < $position)) {
                $position = $found;
            }
        }

        if ($position === null || mb_strlen($content) <= 240) {
            return mb_substr($content, 0, 240);
        }

        $start = max(0, $position - 90);

        return ($start > 0 ? '…' : '').trim(mb_substr($content, $start, 240));
    }
}
