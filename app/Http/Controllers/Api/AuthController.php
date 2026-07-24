<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private function ensureConfiguredSuperAdminUser(string $email): ?User
    {
        $configuredEmail = env('SUPER_ADMIN_EMAIL');

        if (blank($configuredEmail) || strtolower($email) !== strtolower($configuredEmail)) {
            return null;
        }

        $password = env('SUPER_ADMIN_PASSWORD', 'password123');
        $name = env('SUPER_ADMIN_NAME', 'System Owner');
        $fullName = env('SUPER_ADMIN_FULL_NAME', $name);

        $user = User::where('email', $configuredEmail)->first();

        $data = [
            'name' => $name,
            'full_name' => $fullName,
            'email' => $configuredEmail,
            'password' => Hash::make($password),
            'role' => 'super_admin',
        ];

        if ($user) {
            $user->forceFill($data);
            $user->save();

            return $user;
        }

        return User::create($data);
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->input('full_name'),
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'phone_number' => $request->input('phone_number'),
            'district' => $request->input('district'),
            'sector' => $request->input('sector'),
            'education_level' => $request->input('education_level'),
            'role' => $request->input('role', 'student'),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registered successfully.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $configuredEmail = env('SUPER_ADMIN_EMAIL');
        $configuredPassword = env('SUPER_ADMIN_PASSWORD');

        $user = $this->ensureConfiguredSuperAdminUser($email);

        if ($configuredEmail && strtolower($email) === strtolower($configuredEmail) && $configuredPassword && $password === $configuredPassword) {
            $user = $user ?: User::where('email', $configuredEmail)->first();

            if (! $user) {
                $user = User::create([
                    'name' => env('SUPER_ADMIN_NAME', 'System Owner'),
                    'full_name' => env('SUPER_ADMIN_FULL_NAME', env('SUPER_ADMIN_NAME', 'System Owner')),
                    'email' => $configuredEmail,
                    'password' => Hash::make($configuredPassword),
                    'role' => 'super_admin',
                ]);
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Logged in successfully.',
                'user' => new UserResource($user),
                'token' => $token,
            ]);
        }

        if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = Auth::guard('web')->user();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => new UserResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user()),
        ]);
    }
}
