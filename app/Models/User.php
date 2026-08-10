<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password', 'bio'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function publishedPosts(): HasMany
    {
        return $this->posts()->published();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->username ??= self::uniqueUsername($user->name);
        });
    }

    /**
     * Whether the user holds the admin role.
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Whether the user holds an administrative (admin or moderator) role.
     */
    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'moderator']);
    }

    /**
     * Generate a unique username from the given name.
     */
    protected static function uniqueUsername(string $name): string
    {
        $base = Str::slug($name) ?: 'user';
        $username = $base;

        for ($i = 2; User::query()->where('username', $username)->exists(); $i++) {
            $username = $base.'-'.$i;
        }

        return $username;
    }
}
