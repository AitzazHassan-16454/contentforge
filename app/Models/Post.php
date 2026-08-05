<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'title', 'slug', 'content', 'excerpt', 'status', 'published_at'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PUBLISHED = 'published';

    public function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED)
            ->whereNotNull('published_at');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && $this->published_at !== null;
    }

    public function publish(): self
    {
        $this->forceFill([
            'status' => self::STATUS_PUBLISHED,
            'published_at' => $this->published_at ?? now(),
        ])->save();

        return $this;
    }

    public function unpublish(): self
    {
        $this->forceFill([
            'status' => self::STATUS_DRAFT,
            'published_at' => null,
        ])->save();

        return $this;
    }

    /**
     * Sync the post's categories and tags from editor input.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array<int, string>  $tagNames
     */
    public function syncTaxonomy(array $categoryIds, array $tagNames): self
    {
        $this->categories()->sync(array_values(array_filter($categoryIds)));

        $tagIds = collect($tagNames)
            ->map(fn (string $name) => trim($name))
            ->filter()
            ->unique(fn (string $name) => Str::lower($name))
            ->map(function (string $name) {
                return Tag::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => trim($name)],
                )->id;
            })
            ->values()
            ->all();

        $this->tags()->sync($tagIds);

        return $this;
    }

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            $post->slug = self::uniqueSlug($post->title);
        });

        static::updating(function (Post $post) {
            if ($post->isDirty('title')) {
                $post->slug = self::uniqueSlug($post->title, $post);
            }
        });
    }

    /**
     * Generate a unique slug for the given title.
     */
    protected static function uniqueSlug(string $title, ?Post $ignore = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;

        $query = Post::query()->where('slug', $slug);

        if ($ignore !== null) {
            $query->whereKeyNot($ignore->getKey());
        }

        for ($i = 2; $query->exists(); $i++) {
            $slug = $base.'-'.$i;
            $query = Post::query()->where('slug', $slug);

            if ($ignore !== null) {
                $query->whereKeyNot($ignore->getKey());
            }
        }

        return $slug;
    }
}
