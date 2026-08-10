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

            Write a complete, publication-ready blog post based on the topic and constraints provided by
            the user. Your response must begin with a YAML front matter block containing the post's title,
            a short excerpt, and SEO-friendly tags, followed by the Markdown post itself. Use exactly this
            structure:

            ---
            title: "A compelling, SEO-optimized title"
            excerpt: "A one-sentence summary of the post, roughly 150-160 characters."
            tags:
              - tag-one
              - tag-two
              - tag-three
            ---

            Follow these rules strictly:

            1. Output the front matter block first, containing the three fields above and nothing else inside it.
            2. The excerpt must be a single complete sentence with no quotes or newlines.
            3. Provide three to five tags, all lowercase, as single words or short phrases.
            4. After the front matter, write the post in a {$this->tone} tone.
            5. Target approximately {$this->wordTarget()} words.
            6. Begin the body with a single H1 (#) heading that matches the front matter title.
            7. Use Markdown formatting: ## subheadings, **bold**, - bullet lists, and > blockquotes where appropriate.
            8. Open with an engaging introduction that hooks the reader.
            9. Include concrete, actionable advice and real-world examples.
            10. End with a clear conclusion and a call to action.
            11. Output ONLY the front matter and the Markdown post. Do not include code fences, comments, or any other text.
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
