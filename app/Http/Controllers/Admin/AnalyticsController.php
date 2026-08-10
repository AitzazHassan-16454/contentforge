<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\PostView;
use App\Models\User;
use Carbon\CarbonImmutable;

class AnalyticsController extends Controller
{
    public function index()
    {
        $range = collect(range(29, 0))->map(function (int $offset) {
            return now()->subDays($offset)->toImmutable();
        });

        $start = $range->first();
        $end = $range->last();

        $viewsByDate = PostView::query()
            ->between($start, $end)
            ->selectRaw('view_date, sum(views) as total')
            ->groupBy('view_date')
            ->pluck('total', 'view_date');

        $usersByDate = User::query()
            ->whereBetween('created_at', [$start->startOfDay()->toDateTimeString(), $end->endOfDay()->toDateTimeString()])
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $topPosts = Post::query()
            ->with('author:id,name,username')
            ->withCount('comments')
            ->withCount('reactions')
            ->orderByDesc('view_count')
            ->take(10)
            ->get(['id', 'title', 'slug', 'status', 'view_count'])
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'views' => $post->view_count,
                'comments' => $post->comments_count,
                'reactions' => $post->reactions_count,
                'author' => $post->author?->only(['name', 'username']),
            ]);

        $topAuthors = User::query()
            ->withCount('posts')
            ->withCount('comments')
            ->withCount(['posts as total_views' => fn ($query) => $query->selectRaw('coalesce(sum(view_count), 0)')])
            ->orderByDesc('total_views')
            ->take(10)
            ->get(['id', 'name', 'username'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'posts' => $user->posts_count,
                'comments' => $user->comments_count,
                'views' => $user->total_views,
            ]);

        return inertia('Admin/Analytics', [
            'stats' => [
                'users' => User::count(),
                'posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'comments' => Comment::count(),
                'reactions' => PostReaction::count(),
                'contact_messages' => ContactMessage::count(),
                'views' => (int) Post::query()->sum('view_count'),
            ],
            'views_series' => [
                'labels' => $range->map(fn (CarbonImmutable $date) => $date->toDateString())->all(),
                'values' => $range->map(fn (CarbonImmutable $date) => (int) ($viewsByDate[$date->toDateString()] ?? 0))->all(),
            ],
            'users_series' => [
                'labels' => $range->map(fn (CarbonImmutable $date) => $date->toDateString())->all(),
                'values' => $range->map(fn (CarbonImmutable $date) => (int) ($usersByDate[$date->toDateString()] ?? 0))->all(),
            ],
            'top_posts' => $topPosts,
            'top_authors' => $topAuthors,
        ]);
    }
}
