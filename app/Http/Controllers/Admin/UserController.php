<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), fn ($query) => $query
                ->where('name', 'like', '%'.$request->string('search').'%')
                ->orWhere('email', 'like', '%'.$request->string('search').'%')
                ->orWhere('username', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('role'), fn ($query) => $query->role($request->input('role')))
            ->withCount(['posts', 'comments'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return inertia('Admin/Users', [
            'users' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'posts_count' => $user->posts_count,
                'comments_count' => $user->comments_count,
                'created_at' => $user->created_at->diffForHumans(),
            ]),
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'filters' => $request->only('search', 'role'),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'exists:roles,name'],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', "{$user->name} is now {$validated['role']}.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}
