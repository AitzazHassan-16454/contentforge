<?php

namespace App\Observers;

use App\Jobs\EmbedPost;
use App\Models\Post;

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
    }

    /**
     * Remove a post's chunks from the semantic index when it is deleted.
     */
    public function deleted(Post $post): void
    {
        $post->chunks()->delete();
    }
}
