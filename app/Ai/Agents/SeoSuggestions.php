<?php

namespace App\Ai\Agents;

use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class SeoSuggestions implements Agent
{
    use Promptable;

    public function __construct(
        public readonly string $title,
        public readonly string $content,
        /** @var array<int, string> */
        public readonly array $currentTags = [],
        public readonly ?string $currentExcerpt = null,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return <<<'PROMPT'
            You are an expert SEO strategist for a professional blog.

            Analyze the draft post provided by the user and return suggestions in the exact JSON
            shape below. Do not include markdown, code fences, or any commentary — output the JSON only.

            {
              "title": "A compelling, SEO-optimized replacement for the title, or null if the current title is already strong",
              "meta_description": "A single meta description of 150-160 characters that summarizes the post and includes the primary keyword",
              "tags": ["three to five lowercase SEO-friendly tags"]
            }

            Rules:
            1. The title must be under 200 characters.
            2. The meta description must be 150-160 characters, a complete sentence, no quotes or newlines.
            3. Tags must be lowercase, single words or short phrases, and must NOT duplicate the user's existing tags.
            4. Base every suggestion strictly on the provided content. Do not invent facts.
            PROMPT;
    }

    /**
     * The human-readable prompt describing the draft to analyze.
     */
    public function userPrompt(): string
    {
        $prompt = Str::of("Analyze this blog post draft and suggest SEO improvements.\n\n")
            ->append("Current title: {$this->title}\n")
            ->append("Current excerpt (if any): ".($this->currentExcerpt ?: '(none)')."\n")
            ->append('Existing tags: '.(count($this->currentTags) ? implode(', ', $this->currentTags) : '(none)')."\n\n")
            ->append("Post content:\n")
            ->append(Str::limit($this->content, 8000))
            ->append("\n\nReturn only the JSON object described in your instructions.");

        return $prompt->toString();
    }
}
