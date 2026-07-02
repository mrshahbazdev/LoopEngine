<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('permissions')
            ->where('company_id', $request->user()->company_id)
            ->where('role', '!=', 'admin')
            ->orderBy('name')
            ->paginate(20);

        $availablePermissions = Permission::AVAILABLE;

        return view('admin.permissions', compact('users', 'availablePermissions'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->company_id !== $request->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'in:' . implode(',', array_keys(Permission::AVAILABLE))],
        ]);

        $user->permissions()->delete();

        foreach ($validated['permissions'] ?? [] as $permission) {
            $user->grantPermission($permission);
        }

        return back()->with('success', __('app.permissions_updated'));
    }
}
