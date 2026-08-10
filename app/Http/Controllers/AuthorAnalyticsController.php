<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\PostView;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AuthorAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $postIds = $user->posts()->pluck('id');

        $published = $user->posts()->published()->count();

        $totalViews = (int) Post::query()
            ->whereIn('id', $postIds)
            ->sum('view_count');

        $comments = (int) Comment::query()->whereIn('post_id', $postIds)->count();
        $reactions = (int) PostReaction::query()->whereIn('post_id', $postIds)->count();

        $range = collect(range(29, 0))->map(function (int $offset) {
            return now()->subDays($offset)->toImmutable();
        });

        $viewsByDate = PostView::query()
            ->whereIn('post_id', $postIds)
            ->between($range->first(), $range->last())
            ->selectRaw('view_date, sum(views) as total')
            ->groupBy('view_date')
            ->pluck('total', 'view_date');

        $topPosts = Post::query()
            ->whereIn('id', $postIds)
            ->withCount('comments')
            ->withCount('reactions')
            ->orderByDesc('view_count')
            ->take(5)
            ->get(['id', 'title', 'slug', 'status', 'view_count'])
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'views' => $post->view_count,
                'comments' => $post->comments_count,
                'reactions' => $post->reactions_count,
            ]);

        return inertia('Dashboard/Analytics', [
            'stats' => [
                'posts' => $postIds->count(),
                'published_posts' => $published,
                'views' => $totalViews,
                'comments' => $comments,
                'reactions' => $reactions,
            ],
            'views_series' => [
                'labels' => $range->map(fn (CarbonImmutable $date) => $date->toDateString())->all(),
                'values' => $range->map(fn (CarbonImmutable $date) => (int) ($viewsByDate[$date->toDateString()] ?? 0))->all(),
            ],
            'top_posts' => $topPosts,
        ]);
    }
}
