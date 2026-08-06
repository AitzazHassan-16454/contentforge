<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;

class PageController extends Controller
{
    public function about()
    {
        return inertia('Marketing/About', [
            'stats' => [
                'posts' => Post::query()->published()->count(),
                'authors' => User::query()->whereHas('posts', fn ($query) => $query->published())->count(),
            ],
        ]);
    }

    public function pricing()
    {
        return inertia('Marketing/Pricing', [
            'plans' => config('plans'),
        ]);
    }

    public function privacy()
    {
        return inertia('Marketing/Privacy');
    }

    public function terms()
    {
        return inertia('Marketing/Terms');
    }
}
