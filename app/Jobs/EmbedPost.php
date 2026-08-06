<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\SemanticSearch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class EmbedPost implements ShouldQueue
{
    use Queueable;

    public function __construct(public Post $post)
    {
        $this->afterCommit = true;
    }

    public function handle(SemanticSearch $search): void
    {
        $search->reindex($this->post);
    }
}
