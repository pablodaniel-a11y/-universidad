<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesor;
use App\Models\Materia;
use App\Http\Requests\StoreProfesorRequest;
use App\Http\Requests\UpdateProfesorRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\ProfesorResource;
use Illuminate\Http\Response;

class ProfesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return ProfesorResource::collection(Profesor::with('materias')->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfesorRequest $request): ProfesorResource
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('profesores', 'public');
        }

        $profesor = Profesor::create($validated);

        if (isset($validated['materias'])) {
            $profesor->materias()->sync($validated['materias']);
        }

        return new ProfesorResource($profesor->load('materias'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Profesor $profesor): ProfesorResource
    {
        return new ProfesorResource($profesor->load('materias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfesorRequest $request, Profesor $profesor): ProfesorResource
    {
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($profesor->foto && Storage::disk('public')->exists($profesor->foto)) {
                Storage::disk('public')->delete($profesor->foto);
            }
            $validated['foto'] = $request->file('foto')->store('profesores', 'public');
        } else {
            unset($validated['foto']);
        }

        $profesor->update($validated);

        $profesor->materias()->sync($request->input('materias', []));

        return new ProfesorResource($profesor->load('materias'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Profesor $profesor): Response
    {
        if ($profesor->foto && Storage::disk('public')->exists($profesor->foto)) {
            Storage::disk('public')->delete($profesor->foto);
        }

        $profesor->delete();

        return response()->noContent();
    }
}