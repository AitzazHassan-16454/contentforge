<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_generated_from_title(): void
    {
        $post = Post::factory()->create(['title' => 'Hello World Post']);

        $this->assertSame('hello-world-post', $post->slug);
    }

    public function test_slug_is_unique_across_posts(): void
    {
        Post::factory()->create(['title' => 'Same Title']);
        $post = Post::factory()->create(['title' => 'Same Title']);

        $this->assertSame('same-title-2', $post->slug);
    }

    public function test_slug_updates_with_title_change(): void
    {
        $post = Post::factory()->create(['title' => 'Original Title']);

        $post->update(['title' => 'New Title']);

        $this->assertSame('new-title', $post->fresh()->slug);
    }

    public function test_publish_sets_status_and_published_at(): void
    {
        $post = Post::factory()->draft()->create();

        $post->publish();

        $this->assertTrue($post->fresh()->isPublished());
        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_unpublish_resets_status_and_published_at(): void
    {
        $post = Post::factory()->published()->create();

        $post->unpublish();

        $this->assertFalse($post->fresh()->isPublished());
        $this->assertNull($post->fresh()->published_at);
    }

    public function test_published_scope_returns_only_published_posts(): void
    {
        $published = Post::factory()->published()->create();
        Post::factory()->draft()->create();

        $result = Post::published()->get();

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->is($published));
    }

    public function test_post_belongs_to_an_author(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($post->author->is($user));
    }
}
