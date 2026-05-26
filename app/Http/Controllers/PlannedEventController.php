<?php

namespace App\Http\Controllers;

use App\Models\PlannedEvent;
use App\Models\Environment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlannedEventController extends Controller
{
    public function index()
    {
        $events = PlannedEvent::with('environment')->latest()->get();
        return Inertia::render('Admin/Events/Index', [
            'events' => $events
        ]);
    }

    public function create()
    {
        $environments = Environment::where('actif', true)->get();
        return Inertia::render('Admin/Events/Create', [
            'environments' => $environments
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'environment_id' => 'required|exists:environments,id',
            'date_evenement' => 'required|date',
            'points_xp_bonus' => 'required|integer|min:0',
        ]);

        PlannedEvent::create($validated);

        return redirect()->route('events.index')
            ->with('success', 'Événement planifié avec succès.');
    }
}
