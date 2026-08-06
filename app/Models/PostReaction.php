<?php

namespace App\Models;

use Database\Factories\PostReactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'user_id', 'type'])]
class PostReaction extends Model
{
    /** @use HasFactory<PostReactionFactory> */
    use HasFactory;

    public const TYPE_LIKE = 'like';

    public const TYPE_FIRE = 'fire';

    public const TYPE_STAR = 'star';

    public const TYPES = [self::TYPE_LIKE, self::TYPE_FIRE, self::TYPE_STAR];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
