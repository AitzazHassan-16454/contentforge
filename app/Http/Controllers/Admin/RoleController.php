<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'users_count' => $role->users()->count(),
                'permissions' => $role->permissions->pluck('name'),
            ]);

        return inertia('Admin/Roles', [
            'roles' => $roles,
            'permissions' => Permission::query()->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        $this->forgetCachedPermissions();

        return back()->with('success', "Role \"{$role->name}\" created.");
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', "unique:roles,name,{$role->id}"],
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->update(['name' => $validated['name']]);
        $role->syncPermissions($validated['permissions'] ?? []);

        $this->forgetCachedPermissions();

        return back()->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (in_array($role->name, ['admin', 'moderator', 'author'])) {
            return back()->with('error', "The \"{$role->name}\" role cannot be deleted.");
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete a role that is still assigned to users.');
        }

        $role->delete();

        $this->forgetCachedPermissions();

        return back()->with('success', 'Role deleted.');
    }

    private function forgetCachedPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
