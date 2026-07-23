<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class MentorController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'mentor')->where('is_verified_mentor', true);

        // NOTE: topic filter could be added here if mentors had a topics field.
        if ($request->filled('topic')) {
            // No topics field exists; skipping filter (future enhancement).
        }

        return response()->json($query->get(['id', 'full_name', 'email']));
    }
}
