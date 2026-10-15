<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProfesorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dni'      => ['required', 'string', 'max:20', 'unique:profesores,dni'],
            'apellido' => ['required', 'string', 'max:100'],
            'nombre'   => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:profesores,email'],
            'estado'   => ['required', 'boolean'],
            'foto'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'], // máx 2MB
        ];
    }

    /**
     * Nombres legibles para los campos en los errores.
     */
    public function attributes(): array
    {
        return [
            'dni'      => 'DNI',
            'apellido' => 'apellido',
            'nombre'   => 'nombre',
            'email'    => 'correo electrónico',
            'estado'   => 'estado',
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     */
    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'unique'   => 'El :attribute ingresado ya se encuentra registrado.',
            'email'    => 'El :attribute debe ser una dirección de correo válida.',
            'max'      => 'El campo :attribute no debe superar los :max caracteres.',
            'boolean'  => 'El campo :attribute debe ser verdadero o falso.',
        ];
    }
}
