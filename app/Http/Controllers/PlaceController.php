<?php

namespace App\Http\Controllers;

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
    public function store(Request $request)
    {
        $validated = $request->validate([
            'environment_id' => 'required|exists:environments,id',
            'nom' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'description' => 'nullable|string|max:500',
            'rayon_validation' => 'required|integer|min:1',
        ]);

        Place::create($validated);

        return redirect()->route('places.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(Place $place)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Place $place)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Place $place)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Place $place)
    {
        //
    }
}
