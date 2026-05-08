<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Cart::firstOrCreate(['user_id' => $user->id]);

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid login credentials.'],
            ]);
        }

        Cart::firstOrCreate(['user_id' => $user->id]);

        $token = $user->createToken('flutter-app')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'token' => $token,
            'user' => $this->formatUser($user),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(
            $this->formatUser($request->user())
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'google_id' => $user->google_id,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : null,
        ];
    }
}