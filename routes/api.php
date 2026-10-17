<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\ProfesorController;
use Illuminate\Support\Facades\Route;


Route::apiResource(
	'profesores',
	ProfesorController::class
);
Route::apiResource('profesores', ProfesorController::class);

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

*/
