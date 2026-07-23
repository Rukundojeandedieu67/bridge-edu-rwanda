<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpportunityRequest;
use App\Http\Requests\UpdateOpportunityRequest;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Opportunity::class, 'opportunity');
    }

    public function index(Request $request)
    {
        $query = Opportunity::query();

        if ($request->filled('category')) {
            $query->category($request->query('category'));
        }

        if ($request->filled('region')) {
            $query->region($request->query('region'));
        }

        if ($request->filled('search')) {
            $query->search($request->query('search'));
        }

        if ($request->boolean('upcoming')) {
            $query->upcoming();
        }

        return OpportunityResource::collection($query->paginate(12));
    }

    public function store(StoreOpportunityRequest $request)
    {
        $this->authorize('create', Opportunity::class);

        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;

        $opportunity = Opportunity::create($validated);

        return new OpportunityResource($opportunity);
    }

    public function show(Opportunity $opportunity)
    {
        return new OpportunityResource($opportunity);
    }

    public function update(UpdateOpportunityRequest $request, Opportunity $opportunity)
    {
        $this->authorize('update', $opportunity);

        $opportunity->update($request->validated());

        return new OpportunityResource($opportunity);
    }

    public function destroy(Opportunity $opportunity)
    {
        $this->authorize('delete', $opportunity);

        $opportunity->delete();

        return response()->json(['message' => 'Opportunity deleted successfully.']);
    }
}
