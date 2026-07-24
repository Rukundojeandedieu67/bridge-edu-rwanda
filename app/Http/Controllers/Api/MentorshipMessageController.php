<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorshipMessageResource;
use App\Models\MentorshipMessage;
use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Http\Request;

class MentorshipMessageController extends Controller
{
    public function index(Request $request, MentorshipRequest $mentorship_request)
    {
        $user = $request->user();

        if (! $this->canAccessConversation($user, $mentorship_request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return MentorshipMessageResource::collection(
            $mentorship_request->messages()->with('sender')->latest()->paginate(25)
        );
    }

    public function store(Request $request, MentorshipRequest $mentorship_request)
    {
        $user = $request->user();

        if (! $this->canAccessConversation($user, $mentorship_request)) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if (! in_array($mentorship_request->status, ['matched', 'completed'], true)
            && ! in_array($user->role, ['admin', 'super_admin'], true)) {
            return response()->json([
                'message' => 'Conversation is only available after mentorship is matched.',
            ], 403);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $mentorship_request->messages()->create([
            'sender_id' => $user->id,
            'body' => $data['body'],
        ]);

        return new MentorshipMessageResource($message->load('sender'));
    }

    protected function canAccessConversation(User $user, MentorshipRequest $mentorship_request): bool
    {
        return in_array($user->role, ['admin', 'super_admin'], true)
            || $user->id === $mentorship_request->student_id
            || $user->id === $mentorship_request->mentor_id;
    }
}
