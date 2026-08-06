<?php

namespace Tests\Unit;

use App\Services\SemanticSearch;
use Tests\TestCase;

class SemanticSearchTest extends TestCase
{
    public function test_short_content_stays_a_single_chunk(): void
    {
        $chunks = app(SemanticSearch::class)->chunkContent('Just a short post.');

        $this->assertCount(1, $chunks);
        $this->assertSame('Just a short post.', $chunks[0]);
    }

    public function test_blank_content_produces_no_chunks(): void
    {
        $this->assertSame([], app(SemanticSearch::class)->chunkContent('   '));
    }

    public function test_long_content_is_split_into_bounded_chunks(): void
    {
        $content = str_repeat('The quick brown fox jumps over the lazy dog. ', 40);

        $chunks = app(SemanticSearch::class)->chunkContent($content, size: 200, overlap: 40);

        $this->assertGreaterThan(1, count($chunks));

        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(200, mb_strlen($chunk));
        }
    }

    public function test_cosine_similarity_of_identical_vectors_is_one(): void
    {
        $this->assertEqualsWithDelta(1.0, app(SemanticSearch::class)->cosineSimilarity([1, 2, 3], [1, 2, 3]), 0.0001);
    }

    public function test_cosine_similarity_of_orthogonal_vectors_is_zero(): void
    {
        $this->assertEqualsWithDelta(0.0, app(SemanticSearch::class)->cosineSimilarity([1, 0, 0], [0, 1, 0]), 0.0001);
    }

    public function test_cosine_similarity_of_empty_vectors_is_zero(): void
    {
        $this->assertSame(0.0, app(SemanticSearch::class)->cosineSimilarity([], []));
    }
}
