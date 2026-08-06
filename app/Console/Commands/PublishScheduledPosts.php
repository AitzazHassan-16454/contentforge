<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';

    protected $description = 'Publish posts whose scheduled publish time has arrived';

    public function handle(): int
    {
        $posts = Post::query()
            ->scheduled()
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        foreach ($posts as $post) {
            $post->publish();
        }

        $this->info("Published {$posts->count()} scheduled post(s).");

        return self::SUCCESS;
    }
}
