<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->where('role', 'mentor')
            ->where('is_verified_mentor', true);

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

        return UserResource::collection($query->paginate(12));
    }
}
