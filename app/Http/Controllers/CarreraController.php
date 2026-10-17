<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth; 
use App\Mail\SendMail; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Carrera; 
use Inertia\Inertia;
use App\Models\Materia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

use App\Exports\CarrerasExport; 
use Maatwebsite\Excel\Facades\Excel;

//IMPORTANTE: Importar el atributo de ruteo nativo de Laravel 
use Illuminate\Routing\Attributes\Controllers\Middleware;


class CarreraController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    #[Middleware('permission:listar carreras')]
    public function index(Request $request)
    {
        Log::info('Se ingresó a Carreras');
        $buscar = trim($request->input('buscar', '')); 
        $materia = $request->input('materia', '');

        $orden = $request->input('orden', 'nombre'); 
        $direccion = $request->input('direccion', 'asc'); 
        $camposPermitidos = ['codigo', 'nombre', 'estado']; 
        if (!in_array($orden, $camposPermitidos, true)) { 
            $orden = 'nombre'; 
        } 
        if (!in_array($direccion, ['asc', 'desc'], true)) { 
            $direccion = 'asc'; 
        } 

        $porPagina = (int) $request->input('por_pagina', 5); 
        if (!in_array($porPagina, [5, 10, 20], true)) { 
            $porPagina = 5; 
        } 

        $carreras = Carrera::with('materias.profesores')
            ->when($buscar !== '', function ($query) use ($buscar) {
                // Filtra por el nombre de la carrera (o código si prefieres)
                $query->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('codigo', 'like', "%{$buscar}%");
            })
            ->when($request->filled('materia'), function ($query) use ($materia) { 
                $query->whereHas('materias', function ($q) use ($materia) { 
                    $q->where('materias.id', $materia); 
                }); 
            }) 
            ->orderBy($orden, $direccion)
            ->paginate($porPagina) //antes aca iba -get();
            ->withQueryString();
        
        return Inertia::render('Carreras/Index', [ 
            'carreras' => $carreras,
            'materias' => Materia::orderBy('nombre')->get(),
            'filtros' => [ 
                'buscar' => $buscar, 
                'materia' => $materia, 
                'orden' => $orden, 
                'direccion' => $direccion,
                'por_pagina' => $porPagina,
            ], 
        ]);  
    }

    /**
     * Show the form for creating a new resource.
     */

    #[Middleware('permission:crear carreras')]
    public function create()
    {
        return Inertia::render('Carreras/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Middleware('permission:crear carreras')]
    public function store(Request $request)
    {
        $validated = $request->validate([ 
            'codigo' => 'required|string|max:10|unique:carreras,codigo', 
            'nombre' => 'required|string|max:150', 
            'estado' => 'required|boolean', 
        ]); 
        
        Carrera::create($validated); 
        
        return redirect()
            ->route('carreras.index')
            ->with('success', 'Carrera creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    #[Middleware('permission:ver carreras')]
    public function show(Carrera $carrera)
    {
        Log::info('Se ingresó al listado de carreras', [ 
            'usuario_id' => auth()->id(), 
        ]);
        $carrera->load('materias'); 
 
        return Inertia::render('Carreras/Show', [ 
            'carrera' => $carrera, 
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    #[Middleware('permission:editar carreras')]
    public function edit(Carrera $carrera) 
    {
        $carrera->load('materias');

        return Inertia::render('Carreras/Edit', [ 
            'carrera' => $carrera,
            'materias' => Materia::orderBy('nombre')->get(),
        ]); 
    }

    /**
     * Update the specified resource in storage.
     */
    #[Middleware('permission:editar carreras')]
    public function update(Request $request,  Carrera $carrera)
    {
        $validated = $request->validate([
            'codigo' => [ 
                'required', 
                'string', 
                'max:10', 
                Rule::unique('carreras', 'codigo')->ignore($carrera->id), 
            ], 
            'nombre' => 'required|string|max:150', 
            'estado' => 'required|boolean',
            'materias' => 'array', 
            'materias.*' => 'integer|exists:materias,id', 
        ]);

        $carrera->update([ 
            'codigo' => $validated['codigo'], 
            'nombre' => $validated['nombre'], 
            'estado' => $validated['estado'], 
        ]);
 
        $carrera->materias()->sync($validated['materias'] ?? []);

        $data = array(
               'name' => Auth::user()->name, 
               'email' => Auth::user()->email, 
               'carrera_nombre' => $carrera->nombre,
               'carrera_codigo' => $carrera->codigo,
               'carrera_estado' => $carrera->estado,
               'user_email' => "Algun@email.com" 
           ); 
        Mail::to($data['email'])->send(new SendMail($data));

 
        return redirect()
            ->route('carreras.index') 
            ->with('success', 'Carrera actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Middleware('permission:eliminar carreras')]
    public function destroy(Carrera $carrera)
    {
        $carrera->delete(); 
 
        return redirect()
            ->route('carreras.index')
            ->with('success', 'Carrera eliminada correctamente.');  
    }

    #[Middleware('permission:descargar reportes')]
    public function plan(Carrera $carrera) 
    { 
        $carrera->load('materias'); 
 
        $pdf = Pdf::loadView('carreras.plan', [ 
            'carrera' => $carrera, 
        ]); 
 
        //return $pdf->download('plan-' . $carrera->id . '.pdf');
        //download(...): Fuerza la descarga directa del archivo en la computadora del usuario.

        // Cambiamos ->download(...) por ->stream(...)
        return $pdf->stream('plan-' . $carrera->id . '.pdf');
        // stream(...): Abre el reproductor/visor de PDF integrado del navegador web para que se visualice directamente en pantalla
    }


    #[Middleware('permission:descargar reportes')]
    public function export(Request $request) 
    { 
        $buscar = trim($request->input('buscar', '')); 
        $materia = $request->input('materia', ''); 
        $orden = $request->input('orden', 'nombre'); 
        $direccion = $request->input('direccion', 'asc'); 
        $camposPermitidos = ['codigo', 'nombre', 'estado']; 
        
        if (!in_array($orden, $camposPermitidos, true)) { 
            $orden = 'nombre'; 
        } 
        
        if (!in_array($direccion, ['asc', 'desc'], true)) { 
            $direccion = 'asc'; 
        } 
        
        $filtros = [ 
            'buscar' => $buscar, 
            'materia' => $materia, 
            'orden' => $orden, 
            'direccion' => $direccion, 
        ]; 
        
        return Excel::download(new CarrerasExport($filtros), 'reporte_carreras.xlsx'); 
    }
}
