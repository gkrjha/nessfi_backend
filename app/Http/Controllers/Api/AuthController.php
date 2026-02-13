<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResendVerificationRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Http\Resources\AuthResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user'
        ]);

        event(new Registered($user));

        return (new AuthResource($user, null, 'Registration successful! A verification link has been sent to your email.'))
            ->response()
            ->setStatusCode(201);
    }

    public function login(LoginRequest $request)
    {

        $key = 'login-attempts:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->errorResponse(
                "Too many login attempts. Please try again in {$seconds} seconds.",
                429
            );
        }



        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 60);
            return $this->unauthorizedResponse('Invalid email or password.');
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return $this->forbiddenResponse('Email not verified. Please verify your email first.');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return new AuthResource($user, $token, 'Login successful');
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            $token = $user->createToken('auth_token')->plainTextToken;
            return new AuthResource($user, $token, 'Email already verified.');
        }

        $user->markEmailAsVerified();

        $token = $user->createToken('auth_token')->plainTextToken;

        return new AuthResource($user, $token, 'Email verified successfully!');
    }

    public function resendVerification(ResendVerificationRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return $this->errorResponse('Email already verified.', 400);
        }

        $user->sendEmailVerificationNotification();

        return $this->successResponse(null, 'Verification link has been sent to your email.');
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return $this->successResponse(null, 'Successfully logged out');
    }
}
