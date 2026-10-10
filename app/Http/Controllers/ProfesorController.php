<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profesor;
use App\Models\Materia;
use Inertia\Inertia; 

class ProfesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profesores = Profesor::with('materias')->get();

        return Inertia::render('Profesores/Index', [
            'profesores' => $profesores,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profesor $profesor)
    {
        $profesor->load('materias');

        return Inertia::render('Profesores/Edit', [ 
            'profesor' => $profesor,
            'materias' => Materia::where('estado',true)->orderBy('nombre')->get(),
        ]); 
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Profesor $profesor)
    {

        $validated = $request->validate([
            'dni'        => 'required|string|max:20|unique:profesores,dni,' . $profesor->id,
            'nombre'     => 'required|string|max:100',
            'apellido'   => 'required|string|max:100',
            'materias'   => 'nullable|array',
            'materias.*' => 'integer|exists:materias,id',
        ]);

        $profesor->update([
            'dni'      => $validated['dni'],
            'nombre'   => $validated['nombre'],
            'apellido' => $validated['apellido'],
        ]);

        $profesor->materias()->sync($validated['materias'] ?? []);

        return redirect()
            ->route('profesores.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
