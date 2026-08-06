<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostReactionController extends Controller
{
    /**
     * Toggle the authenticated user's reaction of the given type on a post.
     */
    public function toggle(Request $request, Post $post): JsonResponse
    {
        abort_unless($post->isPublished(), 404);

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', PostReaction::TYPES)],
        ]);

        $reaction = PostReaction::query()
            ->where('post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->where('type', $validated['type'])
            ->first();

        if ($reaction) {
            $reaction->delete();
        } else {
            $post->reactions()->create([
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
            ]);
        }

        return response()->json([
            'reactions' => $post->reactionCounts(),
            'user_reactions' => $post->reactions()
                ->where('user_id', $request->user()->id)
                ->pluck('type')
                ->all(),
        ]);
    }
}
