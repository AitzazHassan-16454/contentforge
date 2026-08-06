<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\PostChunk;
use App\Services\SemanticSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Embeddings;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_renders_for_guests(): void
    {
        $this->get(route('posts.search'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Posts/Search')
                ->where('query', '')
                ->where('results', []));
    }

    public function test_semantic_search_ranks_seeded_chunks_by_similarity(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Side Project Handbook']);

        PostChunk::create([
            'post_id' => $post->id,
            'chunk_index' => 0,
            'content' => 'Launching a side project while working full time.',
            'embedding' => [1.0, 0.0, 0.0],
        ]);

        Embeddings::fake([[[1.0, 0.0, 0.0]]]);

        $this->get(route('posts.search', ['q' => 'side project']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Posts/Search')
                ->where('query', 'side project')
                ->has('results', 1)
                ->where('results.0.title', 'Side Project Handbook'));

        Embeddings::assertGenerated(fn ($prompt) => in_array('side project', $prompt->inputs, true));
    }

    public function test_draft_posts_are_not_searchable(): void
    {
        $draft = Post::factory()->draft()->create(['title' => 'Secret Draft Post']);

        PostChunk::create([
            'post_id' => $draft->id,
            'chunk_index' => 0,
            'content' => 'hidden draft content',
            'embedding' => [1.0, 0.0, 0.0],
        ]);

        Embeddings::fake([[[1.0, 0.0, 0.0]]]);

        $this->get(route('posts.search', ['q' => 'hidden draft content']))
            ->assertOk()
            ->assertDontSee('Secret Draft Post');
    }

    public function test_reindex_embeds_published_posts_and_clears_drafts(): void
    {
        $post = Post::factory()->published()->create([
            'title' => 'Original',
            'content' => 'A paragraph about Laravel.',
        ]);

        Embeddings::fake([[[0.9, 0.1]]]);

        app(SemanticSearch::class)->reindex($post);

        $this->assertSame(1, $post->chunks()->count());
        $this->assertSame([0.9, 0.1], $post->chunks()->first()->embedding);

        $post->unpublish();
        app(SemanticSearch::class)->reindex($post);

        $this->assertSame(0, $post->chunks()->count());
    }
}
