<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorshipRequestResource;
use App\Models\MentorshipRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MentorshipRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MentorshipRequest::class, 'mentorship_request');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = MentorshipRequest::with(['student', 'mentor', 'assignedBy'])->newQuery();

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            // admin and super admin see all requests
        } elseif ($user->role === 'mentor') {
            $query->where(function ($sub) use ($user) {
                $sub->where('mentor_id', $user->id)
                    ->orWhere('status', 'pending');
            });
        } else {
            $query->where('student_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('topic')) {
            $query->where('topic_of_interest', 'like', '%'.$request->query('topic').'%');
        }

        return MentorshipRequestResource::collection($query->paginate(12));
    }

    public function store(Request $request)
    {
        $this->authorize('create', MentorshipRequest::class);

        $data = $request->validate([
            'topic_of_interest' => ['required', 'string', 'max:255'],
        ]);

        $data['student_id'] = $request->user()->id;
        $data['mentor_id'] = null;
        $data['status'] = 'pending';

        $mr = MentorshipRequest::create($data);

        return new MentorshipRequestResource($mr->load(['student', 'mentor']));
    }

    public function show(MentorshipRequest $mentorship_request)
    {
        return new MentorshipRequestResource($mentorship_request->load(['student', 'mentor', 'assignedBy']));
    }

    public function update(Request $request, MentorshipRequest $mentorship_request)
    {
        $this->authorize('update', $mentorship_request);

        $user = $request->user();

        $data = $request->validate([
            'status' => ['sometimes', 'required', 'in:pending,matched,completed'],
            'mentor_id' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            if (array_key_exists('mentor_id', $data)) {
                if (! is_null($data['mentor_id'])) {
                    $mentor = User::find($data['mentor_id']);

                    if (! $mentor || $mentor->role !== 'mentor' || ! $mentor->is_verified_mentor) {
                        return response()->json(['message' => 'Assigned user must be a verified mentor.'], 422);
                    }

                    $data['assigned_by_admin_id'] = $user->id;
                    $data['assigned_at'] = now();

                    if (! isset($data['status'])) {
                        $data['status'] = 'matched';
                    }
                }
            }

            $mentorship_request->update($data);

            if (array_key_exists('mentor_id', $data) && ! is_null($data['mentor_id'])) {
                Log::info('Mentorship request assigned by admin', [
                    'request_id' => $mentorship_request->id,
                    'mentor_id' => $mentorship_request->mentor_id,
                    'assigned_by_admin_id' => $user->id,
                ]);
            }

            return new MentorshipRequestResource($mentorship_request->fresh()->load(['student', 'mentor', 'assignedBy']));
        }

        if ($user->role === 'mentor') {
            // Claim an unassigned request and match it to self.
            if (is_null($mentorship_request->mentor_id)) {
                $mentorship_request->mentor_id = $user->id;
                $mentorship_request->status = 'matched';
                $mentorship_request->save();

                return new MentorshipRequestResource($mentorship_request->fresh()->load(['student', 'mentor']));
            }

            // Assigned mentor can update status.
            if ($mentorship_request->mentor_id === $user->id) {
                if (isset($data['status'])) {
                    $mentorship_request->status = $data['status'];
                    $mentorship_request->save();
                }

                return new MentorshipRequestResource($mentorship_request->fresh()->load(['student', 'mentor']));
            }
        }

        return response()->json(['message' => 'Unauthorized to update this mentorship request.'], 403);
    }

    public function destroy(MentorshipRequest $mentorship_request)
    {
        $this->authorize('delete', $mentorship_request);

        $mentorship_request->delete();

        return response()->json(['message' => 'Mentorship request deleted successfully.']);
    }
}
