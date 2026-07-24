<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpportunityApplicationRequest;
use App\Http\Requests\UpdateOpportunityApplicationRequest;
use App\Http\Resources\OpportunityApplicationResource;
use App\Models\OpportunityApplication;
use Illuminate\Http\Request;

class OpportunityApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(OpportunityApplication::class, 'opportunity_application');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = OpportunityApplication::query()->with(['opportunity', 'student']);

        if (in_array($user->role, ['student', 'mentor'], true)) {
            $query->where('student_id', $user->id);
        } elseif (! in_array($user->role, ['admin', 'super_admin'], true)) {
            $query->where('student_id', -1);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return OpportunityApplicationResource::collection($query->paginate(12));
    }

    public function store(StoreOpportunityApplicationRequest $request)
    {
        $this->authorize('create', OpportunityApplication::class);

        $existing = OpportunityApplication::where('student_id', $request->user()->id)
            ->where('opportunity_id', $request->input('opportunity_id'))
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'You have already applied to this opportunity.',
            ], 409);
        }

        $application = OpportunityApplication::create([
            'opportunity_id' => $request->input('opportunity_id'),
            'student_id' => $request->user()->id,
            'cover_letter' => $request->input('cover_letter'),
            'status' => 'pending',
        ]);

        return new OpportunityApplicationResource($application->load(['opportunity', 'student']));
    }

    public function show(OpportunityApplication $opportunity_application)
    {
        return new OpportunityApplicationResource($opportunity_application->load(['opportunity', 'student']));
    }

    public function update(UpdateOpportunityApplicationRequest $request, OpportunityApplication $opportunity_application)
    {
        $this->authorize('update', $opportunity_application);

        $opportunity_application->update($request->validated());

        return new OpportunityApplicationResource($opportunity_application->fresh()->load(['opportunity', 'student']));
    }

    public function destroy(OpportunityApplication $opportunity_application)
    {
        $this->authorize('delete', $opportunity_application);

        $opportunity_application->delete();

        return response()->json(['message' => 'Application deleted successfully.']);
    }
}
