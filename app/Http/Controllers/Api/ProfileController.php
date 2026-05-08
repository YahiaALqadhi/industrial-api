<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update([
            'name' => $data['name'],
            'avatar' => $data['avatar'] ?? $user->avatar,
        ]);

        $user->refresh();

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update([
            'email' => $data['email'],
        ]);

        $user->refresh();

        return response()->json([
            'message' => 'Email updated successfully.',
            'user' => $this->formatUser($user),
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    private function formatUser($user): array
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