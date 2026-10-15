<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profesor;
use App\Models\Materia;
use Inertia\Inertia;

use App\Http\Requests\StoreProfesorRequest;
use App\Http\Requests\UpdateProfesorRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ProfesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        /*$profesores = Profesor::with('materias')->get();

        $profesores = Profesor::orderBy('apellido')->orderBy('nombre')->get();

        return Inertia::render('Profesores/Index', [
            'profesores' => $profesores,
        ]);
        */

        return Inertia::render('Profesores/Index', [
            'profesores' => Profesor::with('materias')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Profesores/Create', [
            'materias' => Materia::where('estado', true)->orderBy('nombre')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfesorRequest $request): RedirectResponse
    {
        // Recuperamos únicamente los datos que pasaron las reglas de validación
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('profesores', 'public');
        }

        Profesor::create($validated);

        // Sincronizar materias si se envían
        if (isset($validated['materias'])) {
            $profesor->materias()->sync($validated['materias']);
        }

        return redirect()
            ->route('profesores.index')
            ->with('success', 'Profesor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Profesor $profesor): RedirectResponses
    {
        return redirect()->route('profesores.edit', $profesor);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Profesor $profesor): Response
    {
        $profesor->load('materias');

        /*
        return Inertia::render('Profesores/Edit', [ 
            'profesor' => $profesor,
            'materias' => Materia::where('estado',true)->orderBy('nombre')->get(),
        ]);
        */
        
        return Inertia::render('Profesores/Edit', [
            'profesor' => $profesor,
            'materias' => Materia::where('estado', true)->orderBy('nombre')->get(), // Materias disponibles para elegir
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfesorRequest $request, Profesor $profesor): RedirectResponse
    {

        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            // Borra la imagen previa del disco para no acumular archivos huérfanos
            if ($profesor->foto && Storage::disk('public')->exists($profesor->foto)) {
                Storage::disk('public')->delete($profesor->foto);
            }
            $validated['foto'] = $request->file('foto')->store('profesores', 'public');
        } else {
            // Si no enviaron foto nueva, no sobreescribe con null
            unset($validated['foto']);
        }

        $profesor->update($validated);

        /*
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
        */

        // Sincronizar materias (si el request trae 'materias', las actualiza; si está vacío, las desmarca todas)
        $profesor->materias()->sync($request->input('materias', []));

        return redirect()
            ->route('profesores.index')
            ->with('success', 'Profesor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profesor $profesor): RedirectResponse
    {
        if ($profesor->foto && Storage::disk('public')->exists($profesor->foto)) {
            Storage::disk('public')->delete($profesor->foto);
        }

        $profesor->delete();

        return redirect()
            ->route('profesores.index')
            ->with('success', 'Profesor eliminado correctamente.');
    }
}
