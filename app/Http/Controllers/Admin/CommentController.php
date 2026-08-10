<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::query()
            ->with(['user:id,name,username', 'post:id,title,slug'])
            ->when($request->filled('filter') && $request->input('filter') === 'pending', fn ($query) => $query->pending())
            ->when($request->filled('search'), fn ($query) => $query
                ->where('body', 'like', '%'.$request->string('search').'%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return inertia('Admin/Comments', [
            'comments' => $comments->through(fn (Comment $comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'is_approved' => $comment->is_approved,
                'created_at' => $comment->created_at->diffForHumans(),
                'author' => $comment->user?->only(['name', 'username']),
                'post' => $comment->post?->only(['title', 'slug']),
            ]),
            'filters' => $request->only('filter', 'search'),
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => true]);

        return back()->with('success', 'Comment approved.');
    }

    public function unapprove(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => false]);

        return back()->with('success', 'Comment unapproved.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
