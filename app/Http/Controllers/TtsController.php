<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostAudio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class TtsController extends Controller
{
    public function __construct(
        private readonly PostAudio $audio,
    ) {}

    /**
     * Return the audio for a post, generating it in the requested voice if needed.
     */
    public function show(Post $post, Request $request): JsonResponse
    {
        abort_unless($post->isPublished(), 404);

        $voice = $request->validate([
            'voice' => ['sometimes', 'string', Rule::in(PostAudio::VOICES)],
        ])['voice'] ?? PostAudio::DEFAULT_VOICE;

        $url = $this->audio->urlFor($post);

        if ($url === null || $post->audio_voice !== $voice) {
            try {
                $url = $this->audio->generate($post, $voice);
            } catch (Throwable $e) {
                Log::error('Audio generation failed', [
                    'post_id' => $post->id,
                    'voice' => $voice,
                    'error' => $e->getMessage(),
                ]);

                return response()->json([
                    'message' => 'Sorry, the audio could not be generated right now.',
                ], 500);
            }
        }

        return response()->json(['audio_url' => $url]);
    }
}
