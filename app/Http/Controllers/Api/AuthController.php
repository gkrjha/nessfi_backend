<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role'=>'user'
        ]);


        $verificationCode = random_int(100000, 999999);

        $user->email_verification_code = $verificationCode;
        $user->email_verification_expires_at = now()->addMinutes(10);
        $user->save();

        $user->notify(new RegistrationNotification($user, $verificationCode));

        return response()->json([
            'message' => 'Registration successful! A verification code has been sent to your email.',
        ]);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $key = 'login-attempts:' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json([
                'message' => "Too many login attempts. Please try again in {$seconds} seconds."
            ], 429);
        }

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);
            
            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        RateLimiter::clear($key);

        $user = auth()->user();

        if (!$user->email_verified) {
            return response()->json([
                'message' => 'Email not verified. Please check your inbox for the verification code.'
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }


    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email_verified) {
            $user->email_verification_code = null;
            $user->email_verification_expires_at = null;
            $user->save();
            
            $token = $user->createToken('auth_token')->plainTextToken;
            
            return response()->json([
                'message' => 'Email already verified.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        }

        if ($user->email_verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        if ($user->email_verification_expires_at->isPast()) {
            return response()->json(['message' => 'Verification code has expired.'], 422);
        }

        $user->email_verified = true;
        $user->email_verification_code = null;
        $user->email_verification_expires_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function resendVerificationCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->email_verified) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $verificationCode = random_int(100000, 999999);

        $user->email_verification_code = $verificationCode;
        $user->email_verification_expires_at = now()->addMinutes(10);
        $user->save();

        $user->notify(new RegistrationNotification($user, $verificationCode));

        return response()->json([
            'message' => 'Verification code has been resent to your email.',
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }
}
