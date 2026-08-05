<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_lists_published_posts(): void
    {
        $published = Post::factory()->published()->create();
        Post::factory()->draft()->create();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($published->title);
        $response->assertDontSee(Post::where('status', Post::STATUS_DRAFT)->first()->title);
    }

    public function test_guest_can_view_a_published_post(): void
    {
        $post = Post::factory()->published()->create();

        $this->get(route('posts.show', $post->slug))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_draft_posts_are_not_publicly_visible(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get(route('posts.show', $post->slug))->assertNotFound();
    }

    public function test_homepage_is_paginated(): void
    {
        Post::factory()->published()->count(12)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Next');
    }
}
