<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleEmailDomain;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users|regex:/@(?:[a-z0-9-]+\.)*pemkomedan\.go\.id$/i',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $this->roleFromEmail($validated['email']),
        ]);

        $user->load('role');

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->load('role');
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user()->load('role'));
    }

    private function roleFromEmail(string $email): ?int
    {
        $domain = strtolower(strrchr($email, '@'));

        return RoleEmailDomain::query()
            ->orderByRaw('CHAR_LENGTH(domain) DESC')
            ->get()
            ->first(fn ($m) => str_ends_with($domain, $m->domain))
            ?->role_id
            ?? Role::where('name', 'Petugas')->value('id');
    }
}
