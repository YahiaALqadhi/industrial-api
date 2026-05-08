<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'auth_url' => $url,
        ]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'name' => $googleUser->name ?: $user->name,
                    'email' => $googleUser->email ?: $user->email,
                    'avatar' => $user->avatar,
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->name ?: 'Google User',
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Str::random(32),
                ]);
            }

            Cart::firstOrCreate([
                'user_id' => $user->id,
            ]);

            $token = $user->createToken('flutter-google-login')->plainTextToken;

            $scheme = env('MOBILE_APP_SCHEME', 'industrialstore');

            return redirect()->away(
                $scheme . '://auth/google?status=success&token=' . urlencode($token)
            );
        } catch (\Throwable $e) {
            $scheme = env('MOBILE_APP_SCHEME', 'industrialstore');

            return redirect()->away(
                $scheme . '://auth/google?status=error&message=' . urlencode('Google login failed')
            );
        }
    }
}