<?php

namespace App\Http\Controllers;

use App\Models\Enigme;
use App\Models\Place;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnigmeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $enigmes = Enigme::with('place')->get();

        return Inertia::render('Admin/Enigmes/Index', [
            'enigmes' => $enigmes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $places = Place::select('id', 'nom')->get();

        return Inertia::render('Admin/Enigmes/Create', [
            'places' => $places
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'place_id' => 'required|exists:places,id',
        'niveau'   => 'required|in:1,2,3,enfant',
        'texte'    => 'required|string',
        'solution' => 'required|string|max:255',
        'indice_1' => 'required|string|max:255',
        'indice_2' => 'nullable|string|max:255',
        'image'    => 'nullable|image|max:2048',
        'actif'    => 'required|boolean',
    ]);

    if ($request->hasFile('image')) {
        $file = is_array($request->file('image')) ? $request->file('image')[0] : $request->file('image');
        $path = $file->store('photos', 'public');
        $validated['image'] = $path;
    }

    Enigme::create($validated);

    return to_route('enigmes.index')->with('success', 'Énigme créée avec succès !');
}

    /**
     * Display the specified resource.
     */
    public function show(Enigme $enigme)
    {
        $enigme->load('place');

        return Inertia::render('Admin/Enigmes/Show', [
            'enigme' => $enigme
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enigme $enigme)
    {
        $places = Place::select('id', 'nom')->get();

        return Inertia::render('Admin/Enigmes/Edit', [
            'enigme' => $enigme,
            'places' => $places
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enigme $enigme)
    {
        $validated = $request->validate([
            'place_id' => 'required|exists:places,id',
            'niveau'   => 'required|in:1,2,3,enfant',
            'texte'    => 'required|string',
            'solution' => 'required|string|max:255',
            'indice_1' => 'required|string|max:255',
            'indice_2' => 'nullable|string|max:255',
            'image'    => 'nullable|image|max:2048',
            'actif'    => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $file = is_array($request->file('image')) ? $request->file('image')[0] : $request->file('image');
            $path = $file->store('photos', 'public');
            $validated['image'] = $path;
        }

        $enigme->update($validated);

        return to_route('enigmes.index')->with('success', 'Énigme mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enigme $enigme)
    {
        $enigme->delete();

        return to_route('enigmes.index')->with('success', 'Énigme supprimée avec succès !');
    }
}
