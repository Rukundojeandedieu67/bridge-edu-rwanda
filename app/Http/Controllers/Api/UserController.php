<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    public function profile(Request $request)
    {
        return new UserResource($request->user());
    }

    public function updateProfile(Request $request)
    {
        if ($request->has('email')) {
            return response()->json(['message' => 'Email cannot be updated.'], 422);
        }

        $user = $request->user();

        $data = $request->validate([
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sector' => ['sometimes', 'nullable', 'string', 'max:100'],
            'education_level' => ['sometimes', 'nullable', 'string', 'max:100'],
        ]);

        $user->update($data);

        return new UserResource($user->fresh());
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->query('role'));
        }

        if ($request->filled('district')) {
            $query->where('district', 'like', '%'.$request->query('district').'%');
        }

        if ($request->filled('sector')) {
            $query->where('sector', 'like', '%'.$request->query('sector').'%');
        }

        if ($request->filled('search')) {
            $term = $request->query('search');
            $query->where(function ($sub) use ($term) {
                $sub->where('full_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if (! in_array($request->user()->role, ['admin', 'super_admin'], true)) {
            $query->where('id', $request->user()->id);
        }

        return UserResource::collection($query->paginate(12));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $rules = [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sector' => ['sometimes', 'nullable', 'string', 'max:100'],
            'education_level' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];

        if (in_array($request->user()->role, ['admin', 'super_admin'], true)) {
            $rules['role'] = ['sometimes', 'required', 'in:student,mentor,admin,super_admin'];
            $rules['is_verified_mentor'] = ['sometimes', 'nullable', 'boolean'];
        }

        $data = $request->validate($rules);
        $data['name'] = $data['full_name'];
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json([
            'message' => 'User created successfully.',
            'user' => new UserResource($user->fresh()),
        ], 201);
    }

    public function show(User $user)
    {
        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $rules = [
            'full_name' => ['sometimes', 'required', 'string', 'max:255'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'district' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sector' => ['sometimes', 'nullable', 'string', 'max:100'],
            'education_level' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];

        if (in_array($request->user()->role, ['admin', 'super_admin'], true)) {
            $rules['role'] = ['sometimes', 'required', 'in:student,mentor,admin,super_admin'];
            $rules['is_verified_mentor'] = ['sometimes', 'nullable', 'boolean'];
        }

        if ($request->has('email')) {
            return response()->json(['message' => 'Email cannot be updated.'], 422);
        }

        $data = $request->validate($rules);
        $user->update($data);

        return new UserResource($user->fresh());
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
