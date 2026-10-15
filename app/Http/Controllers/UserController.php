<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use Inertia\Inertia; 
use Inertia\Response; 
use App\Models\User; 
use Illuminate\Http\RedirectResponse; 
use Illuminate\Support\Facades\Hash; 
use Illuminate\Validation\Rules\Password; 
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('users/index', [ 
            'users' => User::with('roles:id,name')->get(['id', 'name', 'email']) 
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('users/create', [ 
            'roles' => Role::get(['id', 'name']) 
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([ 
            'name' => 'required|string|max:255', 
            'email' => 'required|string|email|max:255|unique:users,email', 
            'password' => ['required', 'confirmed', Password::defaults()], 
            'roles' => 'array' 
        ]); 

        $validated['password'] = Hash::make($validated['password']); 

        // Crear usuario (Corregido: $validated con $)
        $user = User::create($validated); 

        // Sincronizar roles de Spatie 
        if ($request->has('roles')) { 
            $user->syncRoles($request->roles); 
        } 

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        return Inertia::render('users/show', [ 
            'user' => $user->load('roles:id,name'), 
            // Obtenemos todos los permisos que el usuario tiene heredados a través de sus roles 
            'permissions' => $user->getAllPermissions()->map(fn($perm) => [ 
                'id' => $perm->id, 
                'name' => $perm->name, 
            ]) 
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('users/edit', [ 
            'user' => $user->only(['id', 'name', 'email']), 
            'roles' => Role::get(['id', 'name']), 
            'userRoles' => $user->roles->pluck('name') // Array de strings para los checkboxes de React 
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $validated = $request->validate([ 
            'name' => 'required|string|max:255', 
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, 
            'password' => ['nullable', Password::defaults()], // Nullable porque puede no querer cambiarla 
            'roles' => 'array' 
        ]);

        // REGLA CRÍTICA DE ROL: Evitar que un Super Admin se quite el rol a sí mismo 
        if ($request->user()->id === $user->id && $user->hasRole('super-admin')) { 
            // Buscamos si dentro del array 'roles' que viene del formulario se incluyó 'Super Admin' 
            if (!in_array('super-admin', $request->input('roles', []))) { 
                return redirect()->back()->withErrors([ 
                    'roles' => 'No puedes quitarte a ti mismo el rol de Super Admin.' 
                ])->withInput(); 
            } 
        } 

        // 1. Manejo seguro de la contraseña: si no se envía, se remueve del array para no pisarla 
        if (!empty($validated['password'])) { 
            $validated['password'] = Hash::make($validated['password']); 
        } else { 
            unset($validated['password']); 
        } 

        // 2. Actualizar datos de identidad (Corregido: $validated con $)
        $user->update($validated); 

        // 3. Sincronización robusta de roles (si se desmarcan todos, limpia la tabla intermedia automáticamente) 
        $user->syncRoles($request->input('roles', [])); 

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        //Validar la Policy (Llamará al método 'delete' del UserPolicy) 
        // Si intenta borrarse a sí mismo, la Policy devolverá false y Laravel lanzará un error 403 automáticamente 
        Gate::authorize('delete', $user);

        // Spatie elimina automáticamente las relaciones de la tabla intermedia gracias a claves foráneas cascade 
        $user->delete(); 

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
