<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'site_name' => Setting::get('site_name', config('app.name', 'ContentForge')),
            'tagline' => Setting::get('tagline'),
            'twitter_url' => Setting::get('twitter_url'),
            'github_url' => Setting::get('github_url'),
            'linkedin_url' => Setting::get('linkedin_url'),
        ];

        return inertia('Admin/Settings', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return back()->with('success', 'Settings saved.');
    }
}
