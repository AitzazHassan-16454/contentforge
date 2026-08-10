<?php

namespace App\Observers;

use App\Jobs\EmbedPost;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;

class PostObserver
{
    /**
     * Keep the semantic index in sync whenever a post's content or status changes.
     */
    public function saved(Post $post): void
    {
        if ($post->wasRecentlyCreated || $post->wasChanged('content') || $post->wasChanged('status')) {
            EmbedPost::dispatch($post);
        }

        if ($post->wasChanged('content')) {
            $this->clearAudio($post);
        }
    }

    /**
     * Remove a post's chunks from the semantic index when it is deleted.
     */
    public function deleted(Post $post): void
    {
        $post->chunks()->delete();

        $this->clearAudio($post);
    }

    /**
     * Delete a post's audio file and forget where it was stored.
     */
    protected function clearAudio(Post $post): void
    {
        if ($post->audio_path !== null && Storage::disk('public')->exists($post->audio_path)) {
            Storage::disk('public')->delete($post->audio_path);
        }

        $post->forceFill([
            'audio_path' => null,
            'audio_voice' => null,
            'audio_generated_at' => null,
        ])->save();
    }
}
