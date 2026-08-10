<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Audio;
use Tests\TestCase;

class TtsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stop the post observer's embedding job from calling the real API.
        Queue::fake();
    }

    public function test_guest_can_get_audio_for_a_published_post(): void
    {
        Storage::fake('public');
        Audio::fake();

        $post = Post::factory()->published()->create([
            'content' => "# Hello World\n\nThis is the body of the post.",
        ]);

        $response = $this->getJson(route('posts.audio', $post->slug));

        $response->assertOk();
        $response->assertJsonStructure(['audio_url']);

        $this->assertNotNull($post->fresh()->audio_path);
        Storage::disk('public')->assertExists($post->fresh()->audio_path);

        Audio::assertGenerated(fn ($prompt) => $prompt->contains('Hello World') && $prompt->voice === 'Kore');
    }

    public function test_audio_is_reused_when_already_generated(): void
    {
        Storage::fake('public');
        Audio::fake();

        $post = Post::factory()->published()->create();

        $this->getJson(route('posts.audio', $post->slug))->assertOk();
        $firstPath = $post->fresh()->audio_path;

        $this->getJson(route('posts.audio', $post->slug))->assertOk();

        // The same audio file is reused - nothing new was generated.
        $this->assertSame($firstPath, $post->fresh()->audio_path);
        Storage::disk('public')->assertExists($firstPath);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_draft_post_audio_is_not_available(): void
    {
        $post = Post::factory()->draft()->create();

        $this->getJson(route('posts.audio', $post->slug))->assertNotFound();
    }

    public function test_audio_is_regenerated_when_the_voice_changes(): void
    {
        Storage::fake('public');
        Audio::fake();

        $post = Post::factory()->published()->create();

        $this->getJson(route('posts.audio', $post->slug))->assertOk();
        $firstPath = $post->fresh()->audio_path;

        $this->getJson(route('posts.audio', ['post' => $post->slug, 'voice' => 'Puck']))->assertOk();

        $fresh = $post->fresh();

        $this->assertNotSame($firstPath, $fresh->audio_path);
        $this->assertSame('Puck', $fresh->audio_voice);
        Storage::disk('public')->assertExists($fresh->audio_path);

        Audio::assertGenerated(fn ($prompt) => $prompt->voice === 'Puck');
    }

    public function test_audio_is_reused_for_the_same_voice(): void
    {
        Storage::fake('public');
        Audio::fake();

        $post = Post::factory()->published()->create();

        $this->getJson(route('posts.audio', ['post' => $post->slug, 'voice' => 'Puck']))->assertOk();
        $firstPath = $post->fresh()->audio_path;

        $this->getJson(route('posts.audio', ['post' => $post->slug, 'voice' => 'Puck']))->assertOk();

        $this->assertSame($firstPath, $post->fresh()->audio_path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_invalid_voice_is_rejected(): void
    {
        $post = Post::factory()->published()->create();

        $this->getJson(route('posts.audio', ['post' => $post->slug, 'voice' => 'Not-A-Real-Voice']))
            ->assertUnprocessable();
    }
}
