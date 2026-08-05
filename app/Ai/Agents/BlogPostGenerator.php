<?php

namespace App\Ai\Agents;

use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class BlogPostGenerator implements Agent
{
    use Promptable;

    /**
     * The supported tones a post may be written in.
     *
     * @var array<int, string>
     */
    public const TONES = ['professional', 'conversational', 'inspirational', 'technical'];

    /**
     * The supported target lengths for a generated post.
     *
     * @var array<int, string>
     */
    public const LENGTHS = ['short', 'medium', 'long'];

    public function __construct(
        public readonly string $topic,
        public readonly string $tone = 'professional',
        public readonly ?string $keywords = null,
        public readonly string $length = 'medium',
        public readonly ?string $title = null,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return <<<PROMPT
            You are an expert blog writer and SEO strategist for a professional tech publication.

            Write a complete, publication-ready blog post in Markdown based on the topic and constraints
            provided by the user. Follow these rules strictly:

            1. Begin with a single H1 (#) heading as the post title.
            2. Write in a {$this->tone} tone.
            3. Target approximately {$this->wordTarget()} words.
            4. Use Markdown formatting: ## subheadings, **bold**, - bullet lists, and > blockquotes where appropriate.
            5. Open with an engaging introduction that hooks the reader.
            6. Include concrete, actionable advice and real-world examples.
            7. End with a clear conclusion and a call to action.
            8. Output ONLY the Markdown post. Do not include front matter, code fences, or any commentary.
            PROMPT;
    }

    /**
     * The human-readable prompt describing the post to generate.
     */
    public function userPrompt(): string
    {
        return Str::of('Write a blog post about: ')
            ->append($this->topic)
            ->when($this->title, fn (Stringable $value, string $title) => $value->append("\nSuggested title: {$title}"))
            ->when($this->keywords, fn (Stringable $value, string $keywords) => $value->append("\nIncorporate these keywords naturally: {$keywords}"))
            ->toString();
    }

    /**
     * The approximate word count for the configured length.
     */
    private function wordTarget(): int
    {
        return match ($this->length) {
            'short' => 500,
            'long' => 1500,
            default => 1000,
        };
    }
}
