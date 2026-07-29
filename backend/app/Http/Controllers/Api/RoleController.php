<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    public function index()
    {
        return Role::withCount('users')->get();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'guard_name' => 'nullable|string|max:255',
        ]);

        $role = Role::create($validated);

        return response()->json($role->loadCount('users'), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->loadCount('users'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'guard_name' => 'nullable|string|max:255',
        ]);

        $role->update($validated);

        return response()->json($role->loadCount('users'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Role masih memiliki pengguna. Tidak bisa dihapus.',
            ]);
        }

        $role->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
