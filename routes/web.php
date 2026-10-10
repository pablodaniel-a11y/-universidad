<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\DashboardController;

Route::inertia('/', 'welcome')->name('home');

Route::get('/carreras/export', [CarreraController::class, 'export'])->name('carreras.export'); 
Route::get('/carreras/{carrera}/plan', [CarreraController::class, 'plan'])->name('carreras.plan'); 

Route::resource('carreras', CarreraController::class);


Route::resource('materias', MateriaController::class);
Route::resource('profesores', ProfesorController::class)->parameters([
    'profesores' => 'profesor'
]);



Route::middleware(['auth'])->group(function () {
    //Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

require __DIR__.'/settings.php';


