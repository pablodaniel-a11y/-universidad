<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Materia; 
use Inertia\Inertia; 

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materias = Materia::with('profesores')->get();

        return Inertia::render('Materias/Index', [ 
            'materias' => $materias, 
        ]); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Materias/Create', [
            'profesores' => Profesor::where('estado', true)->orderBy('apellido')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo'       => 'required|string|max:10|unique:materias,codigo',
            'nombre'       => 'required|string|max:150',
            'anio'         => 'required|integer|min:1|max:6',
            'cuatrimestre' => 'required|integer|min:1|max:2',
            'estado'       => 'boolean',
            'profesores'   => 'nullable|array',
            'profesores.*' => 'integer|exists:profesores,id',
        ]);

        $materia = Materia::create([
            'codigo'       => $validated['codigo'],
            'nombre'       => $validated['nombre'],
            'anio'         => $validated['anio'],
            'cuatrimestre' => $validated['cuatrimestre'],
            'estado'       => $validated['estado'] ?? true,
        ]);

        $materia->profesores()->sync($validated['profesores'] ?? []);

        return redirect()
            ->route('materias.index')
            ->with('success', 'Materia creada correctamente.');
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
    public function edit(string $id)
    {
        $materia->load('profesores');

        return Inertia::render('Materias/Edit', [
            'materia'    => $materia,
            'profesores' => Profesor::where('estado', true)->orderBy('apellido')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Materia $materia)
    {
        $validated = $request->validate([
            'codigo'       => 'required|string|max:10|unique:materias,codigo,' . $materia->id,
            'nombre'       => 'required|string|max:150',
            'anio'         => 'required|integer|min:1|max:6',
            'cuatrimestre' => 'required|integer|min:1|max:2',
            'estado'       => 'boolean',
            'profesores'   => 'nullable|array',
            'profesores.*' => 'integer|exists:profesores,id',
        ]);

        $materia->update([
            'codigo'       => $validated['codigo'],
            'nombre'       => $validated['nombre'],
            'anio'         => $validated['anio'],
            'cuatrimestre' => $validated['cuatrimestre'],
            'estado'       => $validated['estado'] ?? $materia->estado,
        ]);

        $materia->profesores()->sync($validated['profesores'] ?? []);

        return redirect()
            ->route('materias.index')
            ->with('success', 'Materia actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
