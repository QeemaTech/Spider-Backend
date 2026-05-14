<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Verification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $referredById = null;
        if (! empty($validated['referred_by_code'] ?? null)) {
            $referredBy = User::where('referral_code', $validated['referred_by_code'])->first();
            if ($referredBy) {
                $referredById = $referredBy->id;
                $referredBy->increment('points', setting('referral_points', 0));
            }
        }
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'is_verified' => false,
            'referred_by_id' => $referredById,
        ]);
        $user->assignRole('user');
        if ($validated['email']) {
            // Send verification email
            $user->sendEmailVerificationNotification();
        } else {
            // Send verification code to phone
            $user->sendVerificationCode();
        }
        event(new Registered($user));

        // Load roles and permissions for the response
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => __('Registration successful. Please verify your account before logging in.'),
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function vedorRegister(Request $request): JsonResponse
    {
        $request->validate([
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class . ',email'],
            'owner_password' => ['required', 'confirmed', Rules\Password::defaults()],
            'owner_phone' => ['required', 'string', 'max:255'],
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:3072'],
        ]);
        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->owner_name,
                'email' => $request->owner_email,
                'phone' => $request->owner_phone,
                'password' => Hash::make($request->owner_password),
                'role' => 'vendor',
                'is_active' => true,
                'is_verified' => false,
            ]);

            $user->assignRole('vendor');

            $vendor = Vendor::create([
                'slug' => Str::slug($request->name['en']),
                'owner_id' => $user->id,
                'name' => $request->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'image' => $request->image,
                'commission_percentage' => setting('commission_percentage'),
            ]);
            DB::commit();

            $user->sendEmailVerificationNotification();
            event(new Registered($user));

            return response()->json([
                'success' => true,
                'message' => __('Registration successful. Please verify your account before logging in.'),
                'data' => [
                    'user' => new UserResource($user),
                ],
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Handle a login request to the application.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();

        if (! $user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => __('Account not verified. Please verify your account before logging in.'),
            ], 401);
        }

        // Load roles and permissions for the response
        $user->load('roles', 'permissions');

        // Revoke all existing tokens (optional - for single device login)
        // $user->tokens()->delete();

        // Create a new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Login successful.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => __('Logged out successfully.'),
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    /**
     * Resend verification code.
     */
    public function resendVerificationCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['sometimes', 'nullable', 'email', 'exists:users,email'],
            'phone' => ['sometimes', 'nullable', 'string', 'exists:users,phone'],
        ]);
        if ($data['email']) {
            $user = User::where('email', $data['email'])->first();
        } elseif ($data['phone']) {
            $user = User::where('phone', $data['phone'])->first();
        }

        // $user = User::where('email', $data['email'])->orWhere('phone', $data['phone'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }
        if ($data['email']) {
            $user->sendEmailVerificationNotification();
        } elseif ($data['phone']) {
            $user->sendVerificationCode();
        }

        return response()->json([
            'success' => true,
            'message' => __('Verification code resent successfully.'),
        ]);
    }

    /**
     * Verify the user's account.
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => __('Account already verified.'),
            ], 400);
        }

        $verification = Verification::where('user_id', $user->id)->where('type', 'email')->where('code', $data['code'])->first();
        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid verification code.'),
            ], 400);
        }

        $user->is_verified = true;
        $user->email_verified_at = now();
        $user->save();

        $verification->delete();

        // Create a new token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Account verified successfully.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Verify the user's phone number.
     */
    public function verifyPhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);
        $user = User::where('phone', $data['phone'])->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('User not found.'),
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => __('Account already verified.'),
            ], 400);
        }

        $verification = Verification::where('user_id', $user->id)->where('type', 'phone')->where('code', $data['code'])->first();
        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid verification code.'),
            ], 400);
        }

        $user->is_verified = true;
        $user->phone_verified_at = now();
        $user->save();

        $verification->delete();

        // Create a new token
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('Account verified successfully.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ], 200);
    }
}
