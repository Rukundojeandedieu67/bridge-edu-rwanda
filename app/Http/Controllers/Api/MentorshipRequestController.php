<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MentorshipRequestResource;
use App\Models\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorshipRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(MentorshipRequest::class, 'mentorship_request');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $query = MentorshipRequest::with(['student', 'mentor'])->newQuery();

        if ($user->role === 'admin') {
            // admin sees all requests
        } elseif ($user->role === 'mentor') {
            // Mentor sees assigned requests and open pending requests.
            $query->where(function ($sub) use ($user) {
                $sub->where('mentor_id', $user->id)
                    ->orWhere('status', 'pending');
            });
        } else {
            $query->where('student_id', $user->id);
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
        return new MentorshipRequestResource($mentorship_request->load(['student', 'mentor']));
    }

    public function update(Request $request, MentorshipRequest $mentorship_request)
    {
        $this->authorize('update', $mentorship_request);

        $user = $request->user();

        $data = $request->validate([
            'status' => ['sometimes', 'required', 'in:pending,matched,completed'],
            'mentor_id' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        if ($user->role === 'admin') {
            $mentorship_request->update($data);
            return new MentorshipRequestResource($mentorship_request->fresh()->load(['student', 'mentor']));
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
}
