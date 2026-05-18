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

    // Gestion du téléversement de fichier (Adaptée pour Vue 3)
    if ($request->hasFile('image')) {
        // Si Vue transmet un tableau de fichiers, on extrait le premier
        $file = is_array($request->file('image')) ? $request->file('image')[0] : $request->file('image');
        
        $path = $file->store('photos', 'public');
        $validated['image'] = $path;
    }

    Enigme::create($validated);

    return to_route('enigmes.index')->with('success', 'Énigme créée avec succès !');
}
}
