<?php

namespace App\Http\Controllers;

use App\Models\Environment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnvironmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $environments = Environment::all();
        return Inertia::render('Admin/Environments/Index', [
            'environments' => $environments
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Environments/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'nom' => 'required|string|max:150',
        'description' => 'nullable|string',
        'actif' => 'required|boolean',
    ]);

    Environment::create($validated);

    return redirect()->route('environments.index');
}


    /**
     * Display the specified resource.
     */
    public function show(Environment $environment)
    {
        $environment->load('places');

        return Inertia::render('Admin/Environments/Show', [
            'environment' => $environment
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Environment $environment)
    {
        return Inertia::render('Admin/Environments/Edit', [
            'environment' => $environment
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Environment $environment)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:150',
            'description' => 'nullable|string',
            'actif' => 'required|boolean',
        ]);

        $environment->update($validated);

        return to_route('environments.index')->with('success', 'Environnement mis à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Environment $environment)
    {
        $environment->delete();

        return to_route('environments.index')->with('success', 'Environnement supprimé avec succès !');
    }
}
