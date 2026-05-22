<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlaceRequest;
use App\Models\Environment;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $places = Place::with('environment')->get();

    return Inertia::render('Admin/Places/Index', [
        'places' => $places
    ]);
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $environments = Environment::select('id', 'nom')->get();
        return Inertia::render('Admin/Places/Create', [
            'environments' => $environments
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePlaceRequest $request)
    {

        Place::create($request->validated());

        return redirect()->route('places.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Place $place)
    {
        $place->load('environment', 'enigmes');

        return Inertia::render('Admin/Places/Show', [
            'place' => $place
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Place $place)
    {
        $environments = Environment::select('id', 'nom')->get();

        return Inertia::render('Admin/Places/Edit', [
            'place' => $place,
            'environments' => $environments
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Place $place)
    {
        $validated = $request->validate([
            'environment_id' => 'required|exists:environments,id',
            'nom' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:500',
            'rayon_validation' => 'required|integer|min:1',
            'recommandation' => ['nullable', 'array'],
            'recommandation.*.nom' => ['required_with:recommandation', 'string', 'max:150'],
            'recommandation.*.description' => ['nullable', 'string', 'max:255'],
            'ressource' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['recommandation'] = collect($validated['recommandation'] ?? [])
            ->filter(fn ($item) => filled($item['nom'] ?? null))
            ->values()
            ->all() ?: null;
        $validated['ressource'] = filled($validated['ressource'] ?? null) ? $validated['ressource'] : null;

        $place->update($validated);

        return to_route('places.index')->with('success', 'Lieu mis à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Place $place)
    {
        $place->delete();

        return to_route('places.index')->with('success', 'Lieu supprimé avec succès !');
    }
}
