<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PathwayStepResource;
use App\Models\Pathway;
use App\Models\PathwayStep;
use Illuminate\Http\Request;

class PathwayStepController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PathwayStep::class, 'step');
    }

    public function index(Pathway $pathway)
    {
        return PathwayStepResource::collection($pathway->steps()->paginate(12));
    }

    public function store(Request $request, Pathway $pathway)
    {
        $this->authorize('create', PathwayStep::class);

        $data = $request->validate([
            'position' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'resource_link' => ['nullable', 'url', 'max:255'],
            'estimated_hours' => ['nullable', 'integer'],
        ]);

        $data['pathway_id'] = $pathway->id;

        $step = PathwayStep::create($data);

        return new PathwayStepResource($step);
    }

    public function show(Pathway $pathway, PathwayStep $step)
    {
        return new PathwayStepResource($step);
    }

    public function update(Request $request, Pathway $pathway, PathwayStep $step)
    {
        $this->authorize('update', $step);

        $data = $request->validate([
            'position' => ['sometimes', 'required', 'integer'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'resource_link' => ['sometimes', 'nullable', 'url', 'max:255'],
            'estimated_hours' => ['sometimes', 'nullable', 'integer'],
        ]);

        $step->update($data);

        return new PathwayStepResource($step);
    }

    public function destroy(Pathway $pathway, PathwayStep $step)
    {
        $this->authorize('delete', $step);

        $step->delete();

        return response()->json(['message' => 'Pathway step deleted successfully.']);
    }
}
