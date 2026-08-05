<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $demo = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo Author',
                'password' => 'password',
            ],
        );

        Post::where('user_id', $demo->id)->delete();

        $posts = [
            [
                'title' => 'Welcome to ContentForge: AI-Powered Blogging',
                'excerpt' => 'A behind-the-scenes look at how ContentForge uses the Laravel AI SDK to turn a single topic into a full, publication-ready draft.',
                'content' => "# Welcome to ContentForge: AI-Powered Blogging\n\nContentForge pairs the Laravel AI SDK with a clean Inertia + Vue editor so that writing a post starts with an idea, not a blank page.\n\n## How it works\n\nDescribe a topic, pick a tone and length, and the AI assistant streams a complete Markdown draft into the editor in real time. From there, everything is yours: rewrite sections, tighten the intro, or scrap it and start over.\n\n## Draft, review, publish\n\nGenerated content is never published automatically. Every post lives as a draft until *you* hit publish, keeping quality firmly in human hands.\n\n> Great writing is editing. ContentForge just removes the fear of the first sentence.",
                'category' => 'Getting Started',
                'tags' => ['laravel', 'ai', 'writing'],
            ],
            [
                'title' => 'Streaming AI Responses with Server-Sent Events',
                'excerpt' => 'Why we stream generated content token-by-token instead of making readers wait for the whole response.',
                'content' => "# Streaming AI Responses with Server-Sent Events\n\nWaiting for an entire AI response before showing anything is a poor experience. ContentForge streams each token to the browser as it arrives, delivering that satisfying typewriter effect.\n\n## The stack\n\n- **Laravel AI SDK** exposes a `stream()` method that yields generation events\n- **SSE** (`text/event-stream`) carries those events over a single HTTP response\n- **Vue** consumes the stream with a `fetch` reader and appends deltas to the editor\n\n## Why it matters\n\nUsers see progress immediately, which makes generation feel fast — and lets them stop a draft the moment it goes off the rails.",
                'category' => 'Engineering',
                'tags' => ['laravel', 'sse', 'streaming'],
            ],
            [
                'title' => 'Why Provider-Agnostic AI Matters',
                'excerpt' => 'Locking your app to one AI vendor is a gamble. Here is how the Laravel AI SDK keeps providers swappable.',
                'content' => "# Why Provider-Agnostic AI Matters\n\nAI is moving fast, and the model that's best today may not be tomorrow. That's why ContentForge builds on the official Laravel AI SDK rather than a provider SDK.\n\n## One line to swap providers\n\nChanging providers is a single environment variable:\n\n```\nAI_PROVIDER=anthropic\nANTHROPIC_API_KEY=...\n```\n\nNo feature code changes. Your agent definitions stay identical regardless of whether the backing model is OpenAI, Anthropic, or Gemini.\n\n## Failover built in\n\nThe SDK can fall back to a secondary provider when the primary hits a rate limit, so generation keeps flowing.",
                'category' => 'Engineering',
                'tags' => ['laravel', 'ai', 'architecture'],
            ],
        ];

        $categories = collect($posts)->pluck('category')->unique()->map(function (string $name) {
            return Category::firstOrCreate(['name' => $name]);
        });

        foreach ($posts as $post) {
            $category = $categories->firstWhere('name', $post['category']);
            $tags = collect($post['tags'])->map(fn (string $name) => Tag::firstOrCreate(['name' => $name]));

            $created = Post::factory()->published()->create([
                'user_id' => $demo->id,
                ...Arr::except($post, ['category', 'tags']),
            ]);

            $created->categories()->sync($category->id);
            $created->tags()->sync($tags->pluck('id'));
        }
    }
}
