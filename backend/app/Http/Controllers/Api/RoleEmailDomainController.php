<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleEmailDomain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleEmailDomainController extends Controller
{
    public function index()
    {
        return RoleEmailDomain::with('role')->get();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:role_email_domains,domain',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $domain = RoleEmailDomain::create($validated);

        return response()->json($domain->load('role'), 201);
    }

    public function update(Request $request, RoleEmailDomain $domain): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255|unique:role_email_domains,domain,' . $domain->id,
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $domain->update($validated);

        return response()->json($domain->load('role'));
    }

    public function destroy(RoleEmailDomain $domain): JsonResponse
    {
        $domain->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
