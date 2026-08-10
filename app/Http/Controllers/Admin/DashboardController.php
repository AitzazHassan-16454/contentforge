<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $pendingComments = Comment::pending()->count();

        return inertia('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'posts' => Post::count(),
                'published_posts' => Post::published()->count(),
                'comments' => Comment::count(),
                'pending_comments' => $pendingComments,
                'reactions' => PostReaction::count(),
                'contact_messages' => ContactMessage::count(),
            ],
            'latestUsers' => User::query()
                ->latest()
                ->take(5)
                ->get(['id', 'name', 'username', 'email', 'created_at'])
                ->map(fn (User $user) => [
                    ...$user->only(['id', 'name', 'username', 'email']),
                    'created_at' => $user->created_at->diffForHumans(),
                    'roles' => $user->getRoleNames(),
                ]),
            'latestPosts' => Post::query()
                ->with('author:id,name,username')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn (Post $post) => [
                    'id' => $post->id,
                    'title' => $post->title,
                    'status' => $post->status,
                    'views' => $post->view_count,
                    'author' => $post->author?->only(['name', 'username']),
                    'updated_at' => $post->updated_at->diffForHumans(),
                ]),
            'latestComments' => Comment::query()
                ->with(['user:id,name,username', 'post:id,title,slug'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn (Comment $comment) => [
                    'id' => $comment->id,
                    'body' => Str::limit($comment->body, 80),
                    'is_approved' => $comment->is_approved,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'author' => $comment->user?->only(['name', 'username']),
                    'post' => $comment->post?->only(['title', 'slug']),
                ]),
        ]);
    }
}
