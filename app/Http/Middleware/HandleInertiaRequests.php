<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    ...$user->only(['id', 'name', 'username', 'email']),
                    'roles' => $user->getRoleNames(),
                    'is_staff' => $user->isStaff(),
                    'is_admin' => $user->isAdmin(),
                ] : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'settings' => [
                'site_name' => Setting::get('site_name', config('app.name', 'ContentForge')),
                'tagline' => Setting::get('tagline'),
                'twitter_url' => Setting::get('twitter_url'),
                'github_url' => Setting::get('github_url'),
                'linkedin_url' => Setting::get('linkedin_url'),
            ],
        ];
    }
}
