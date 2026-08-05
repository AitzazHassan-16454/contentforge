<?php

namespace Tests\Feature;

use App\Ai\Agents\SeoSuggestions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_request_seo_suggestions(): void
    {
        $this->postJson(route('dashboard.posts.seo-suggestions'), [
            'title' => 'My Draft',
            'content' => str_repeat('lorem ipsum ', 30),
        ])->assertRedirect(route('login'));
    }

    public function test_request_is_validated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('dashboard.posts.seo-suggestions'), [
                'title' => '',
                'content' => 'too short',
                'tags' => 'not-an-array',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content', 'tags']);
    }

    public function test_returns_parsed_suggestions(): void
    {
        SeoSuggestions::fake(['{"title":"A Better Title","meta_description":"A concise meta description for search engines.","tags":["laravel","ai","seo"]}']);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('dashboard.posts.seo-suggestions'), [
            'title' => 'My Draft',
            'content' => '# Hello world'.str_repeat("\n\nParagraph of meaningful body copy.", 20),
            'excerpt' => 'Old excerpt',
            'tags' => ['draft'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'title' => 'A Better Title',
            'meta_description' => 'A concise meta description for search engines.',
            'tags' => ['laravel', 'ai', 'seo'],
        ]);

        SeoSuggestions::assertPrompted(fn ($prompt) => $prompt->contains('My Draft') && $prompt->contains('Paragraph of meaningful body copy.'));
    }

    public function test_unparseable_ai_output_returns_empty_suggestions(): void
    {
        SeoSuggestions::fake(['This is not valid JSON']);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('dashboard.posts.seo-suggestions'), [
                'title' => 'My Draft',
                'content' => '# Hello world'.str_repeat("\n\nParagraph of meaningful body copy.", 20),
            ])
            ->assertOk()
            ->assertExactJson([
                'title' => null,
                'meta_description' => null,
                'tags' => [],
            ]);
    }
}
