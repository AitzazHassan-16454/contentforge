<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['post_id', 'view_date', 'views'])]
class PostView extends Model
{
    /**
     * Record a single view for the given post on the given date,
     * upserting the daily aggregate counter.
     */
    public static function recordFor(Post $post, CarbonImmutable $date): void
    {
        static::query()
            ->updateOrCreate(
                ['post_id' => $post->getKey(), 'view_date' => $date->toDateString()],
            )
            ->increment('views');
    }

    public function casts(): array
    {
        return [
            'view_date' => 'date',
            'views' => 'integer',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function scopeBetween(Builder $query, CarbonImmutable $start, CarbonImmutable $end): Builder
    {
        return $query->whereBetween('view_date', [$start->toDateString(), $end->toDateString()]);
    }
}
