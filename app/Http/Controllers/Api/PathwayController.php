<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PathwayResource;
use App\Models\Pathway;
use Illuminate\Http\Request;

class PathwayController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Pathway::class, 'pathway');
    }

    public function index(Request $request)
    {
        $query = Pathway::with('steps');

        return PathwayResource::collection($query->paginate(12));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Pathway::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'target_role' => ['required', 'string', 'max:255'],
        ]);

        $pathway = Pathway::create($data);

        return new PathwayResource($pathway->load('steps'));
    }

    public function show(Pathway $pathway)
    {
        return new PathwayResource($pathway->load('steps'));
    }

    public function update(Request $request, Pathway $pathway)
    {
        $this->authorize('update', $pathway);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'target_role' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $pathway->update($data);

        return new PathwayResource($pathway->load('steps'));
    }

    public function destroy(Pathway $pathway)
    {
        $this->authorize('delete', $pathway);

        $pathway->delete();

        return response()->json(['message' => 'Pathway deleted successfully.']);
    }
}
