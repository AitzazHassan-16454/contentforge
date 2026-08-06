<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\SemanticSearch;
use Illuminate\Console\Command;

class EmbedPosts extends Command
{
    protected $signature = 'posts:embed {--all : Reindex posts that already have embeddings}';

    protected $description = 'Generate semantic embeddings for all published posts';

    public function handle(SemanticSearch $search): int
    {
        $posts = Post::query()->published()->with('chunks')->get();

        if ($posts->isEmpty()) {
            $this->info('No published posts to embed.');

            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($posts->count());
        $bar->start();

        foreach ($posts as $post) {
            if (! $this->option('all') && $post->chunks->isNotEmpty()) {
                $bar->advance();

                continue;
            }

            $search->reindex($post);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Semantic index updated.');

        return self::SUCCESS;
    }
}
