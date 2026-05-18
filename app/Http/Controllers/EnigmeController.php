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
            'image'    => 'nullable|image|max:2048',
            'actif'    => 'required|boolean',
        ]);

        // Gestion du téléversement de fichier
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('photos', 'public');
            $validated['image'] = $path;
        }

        Enigme::create($validated);

        // return redirect()->route('enigmes.index');
        return to_route('enigmes.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Enigme $enigme)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Enigme $enigme)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Enigme $enigme)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enigme $enigme)
    {
        //
    }
}
