<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Ai\Audio;

class PostAudio
{
    /**
     * How many characters of text to send to the AI in one go.
     */
    protected const CHUNK_SIZE = 2000;

    /**
     * The name of the public disk used to store the audio files.
     */
    protected const DISK = 'public';

    /**
     * The Gemini TTS voices readers may choose from.
     *
     * @var array<int, string>
     */
    public const VOICES = ['Kore', 'Puck', 'Zephyr', 'Aoede', 'Fenrir', 'Leda', 'Orus', 'Perseus', 'Charon'];

    /**
     * The voice used when none is chosen (matches the old female default).
     */
    public const DEFAULT_VOICE = 'Kore';

    /**
     * Get the public URL for the post's audio, or null when no audio exists.
     */
    public function urlFor(Post $post): ?string
    {
        if ($post->audio_path === null) {
            return null;
        }

        if (! Storage::disk(self::DISK)->exists($post->audio_path)) {
            return null;
        }

        return Storage::disk(self::DISK)->url($post->audio_path);
    }

    /**
     * Generate audio for the post in the given voice and save it, then return its public URL.
     */
    public function generate(Post $post, string $voice = self::DEFAULT_VOICE): string
    {
        $text = $this->textFromMarkdown($post->content);
        $chunks = $this->chunkText($text);

        if ($chunks === []) {
            throw new \RuntimeException('The post has no content to read aloud.');
        }

        $audioPieces = [];

        foreach ($chunks as $chunk) {
            $audioPieces[] = Audio::of($chunk)
                ->voice($voice)
                ->timeout(120)
                ->generate('gemini')
                ->content();
        }

        $wav = $this->concatenateWavs($audioPieces);

        if ($post->audio_path !== null && Storage::disk(self::DISK)->exists($post->audio_path)) {
            Storage::disk(self::DISK)->delete($post->audio_path);
        }

        $path = 'audio/posts/'.$post->slug.'-'.Str::random(8).'.wav';

        Storage::disk(self::DISK)->put($path, $wav, ['visibility' => 'public']);

        $post->forceFill([
            'audio_path' => $path,
            'audio_voice' => $voice,
            'audio_generated_at' => now(),
        ])->save();

        return Storage::disk(self::DISK)->url($path);
    }

    /**
     * Turn markdown text into clean, speakable plain text.
     */
    protected function textFromMarkdown(string $markdown): string
    {
        $text = $markdown;

        // Remove code blocks.
        $text = preg_replace('/```.*?```/s', ' ', $text) ?? $text;

        // Turn links and images into their visible text.
        $text = preg_replace('/!\[([^\]]*)\]\([^)]*\)/', '$1', $text) ?? $text;
        $text = preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $text) ?? $text;

        // Remove heading markers, bold, italics, strikethrough and code ticks.
        $text = preg_replace('/[#*_>`~]/', '', $text) ?? $text;

        // Remove blockquote markers.
        $text = preg_replace('/^\s*>\s?/m', '', $text) ?? $text;

        // Remove HTML tags.
        $text = strip_tags($text);

        // Collapse extra blank lines and spaces into single spaces.
        $text = preg_replace('/\n\s*\n/', "\n", $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * Split long text into smaller pieces so the AI can handle each one.
     *
     * @return array<int, string>
     */
    protected function chunkText(string $text, int $size = self::CHUNK_SIZE): array
    {
        $text = trim($text);

        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= $size) {
            return [$text];
        }

        $sentences = preg_split('/(?<=[.!?])\s+/', $text) ?? [$text];

        $chunks = [];
        $current = '';

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            if ($sentence === '') {
                continue;
            }

            if ($current !== '' && mb_strlen($current) + 1 + mb_strlen($sentence) > $size) {
                $chunks[] = $current;
                $current = $sentence;
            } else {
                $current = $current === '' ? $sentence : $current.' '.$sentence;
            }
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }

    /**
     * Combine several WAV files into one single WAV file.
     *
     * Every chunk comes from the same AI provider, so they all use the same
     * format (24kHz, mono, 16-bit). We keep the first file's header and just
     * glue the audio data together, then fix the sizes in the header.
     *
     * @param  array<int, string>  $wavs
     */
    protected function concatenateWavs(array $wavs): string
    {
        if (count($wavs) === 1) {
            return $wavs[0];
        }

        $header = substr($wavs[0], 0, 44);
        $pcm = substr($wavs[0], 44);

        foreach (array_slice($wavs, 1) as $wav) {
            $pcm .= substr($wav, 44);
        }

        // Rebuild the header with the correct total sizes.
        return substr($header, 0, 4)
            .pack('V', 36 + strlen($pcm))
            .substr($header, 8, 32)
            .pack('V', strlen($pcm))
            .$pcm;
    }
}
