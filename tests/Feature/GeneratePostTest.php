<?php

namespace Tests\Feature;

use App\Ai\Agents\BlogPostGenerator;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneratePostTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_use_the_ai_generator(): void
    {
        $this->get(route('dashboard.posts.ai.generate', [
            'topic' => 'Laravel',
            'tone' => 'professional',
            'length' => 'short',
        ]))->assertRedirect(route('login'));
    }

    public function test_generation_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard.posts.ai.generate', [
                'topic' => '',
                'tone' => 'invalid-tone',
                'length' => 'short',
            ]))
            ->assertSessionHasErrors(['topic', 'tone']);
    }

    public function test_generation_streams_a_markdown_post(): void
    {
        BlogPostGenerator::fake(["# Fake AI Title\n\n## Section\n\nGenerated body."]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard.posts.ai.generate', [
            'topic' => 'Laravel & AI',
            'title' => 'Suggested',
            'tone' => 'technical',
            'keywords' => 'laravel, php',
            'length' => 'short',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/event-stream; charset=utf-8');
        $this->assertStringContainsString('Title', $response->streamedContent());
        $this->assertStringContainsString('[DONE]', $response->streamedContent());

        BlogPostGenerator::assertPrompted(fn ($prompt) => $prompt->contains('Laravel & AI'));
    }
}
