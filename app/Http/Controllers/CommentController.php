<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Store a new comment on a published post.
     */
    public function store(Request $request, Post $post): JsonResponse
    {
        abort_unless($post->isPublished(), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => trim($validated['body']),
        ]);

        return response()->json([
            'comment' => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => $comment->created_at->diffForHumans(),
                'author' => $request->user()->name,
                'user_id' => $request->user()->id,
            ],
        ], 201);
    }

    /**
     * Delete one of the user's own comments.
     */
    public function destroy(Request $request, Post $post, Comment $comment): JsonResponse
    {
        abort_unless($comment->post_id === $post->id, 404);

        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['ok' => true]);
    }
}
