<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProtocolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Información general
            |--------------------------------------------------------------------------
            */

            'crop_id' => [
                'required',
                'exists:crops,id',
            ],

            'problem_id' => [
                'required',
                'exists:problems,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Aplicaciones
            |--------------------------------------------------------------------------
            */

            'applications' => [
                'required',
                'array',
                'min:1',
            ],

            'applications.*.application_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'applications.*.application_type' => [
                'nullable',
                'string',
                'max:100',
            ],

            'applications.*.description' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | Productos EOFertil directos
            |--------------------------------------------------------------------------
            */

            'applications.*.products' => [
                'nullable',
                'array',
            ],

            'applications.*.products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'applications.*.products.*.dose' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'applications.*.products.*.unit' => [
                'required',
                'string',
                'max:30',
            ],

            'applications.*.products.*.application_base' => [
                'required',
                'string',
                'max:50',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ingredientes activos individuales
            |--------------------------------------------------------------------------
            */

            'applications.*.active_ingredients' => [
                'nullable',
                'array',
            ],

            'applications.*.active_ingredients.*.active_ingredient_id' => [
                'required',
                'integer',
                'exists:active_ingredients,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Productos recomendados por ingrediente activo
            |--------------------------------------------------------------------------
            */

            'applications.*.active_ingredients.*.products' => [
                'required',
                'array',
                'min:1',
            ],

            'applications.*.active_ingredients.*.products.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],

            'applications.*.active_ingredients.*.products.*.dose' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'applications.*.active_ingredients.*.products.*.unit' => [
                'required',
                'string',
                'max:30',
            ],

            'applications.*.active_ingredients.*.products.*.application_base' => [
                'required',
                'string',
                'max:50',
            ],

            /*
            |--------------------------------------------------------------------------
            | Combinaciones de ingredientes activos
            |--------------------------------------------------------------------------
            */

            'applications.*.active_ingredient_combinations' => [
                'nullable',
                'array',
            ],

            'applications.*.active_ingredient_combinations.*.active_ingredient_combination_id' => [
                'required',
                'integer',
                'exists:active_ingredient_combinations,id',
            ],

            'applications.*.active_ingredient_combinations.*.dose' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'applications.*.active_ingredient_combinations.*.unit' => [
                'required',
                'string',
                'max:30',
            ],

            'applications.*.active_ingredient_combinations.*.application_base' => [
                'required',
                'string',
                'max:50',
            ],
        ];
    }

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Información general
            |--------------------------------------------------------------------------
            */

            'crop_id.required' =>
            'Debe seleccionar un cultivo.',

            'crop_id.exists' =>
            'El cultivo seleccionado no es válido.',

            'problem_id.required' =>
            'Debe seleccionar un problema.',

            'problem_id.exists' =>
            'El problema seleccionado no es válido.',

            /*
            |--------------------------------------------------------------------------
            | Aplicaciones
            |--------------------------------------------------------------------------
            */

            'applications.required' =>
            'Debe agregar al menos una aplicación.',

            'applications.min' =>
            'Debe agregar al menos una aplicación.',

            'applications.*.application_number.required' =>
            'El número de aplicación es obligatorio.',

            /*
            |--------------------------------------------------------------------------
            | Productos EOFertil
            |--------------------------------------------------------------------------
            */

            'applications.*.products.*.product_id.required' =>
            'Debe seleccionar un producto EOFertil.',

            'applications.*.products.*.product_id.exists' =>
            'Uno de los productos EOFertil seleccionados no existe.',

            'applications.*.products.*.dose.required' =>
            'Debe ingresar la dosis del producto.',

            'applications.*.products.*.dose.gt' =>
            'La dosis del producto debe ser mayor que cero.',

            'applications.*.products.*.unit.required' =>
            'Debe ingresar la unidad de la dosis.',

            'applications.*.products.*.application_base.required' =>
            'Debe ingresar la base de aplicación.',

            /*
            |--------------------------------------------------------------------------
            | Ingredientes activos individuales
            |--------------------------------------------------------------------------
            */

            'applications.*.active_ingredients.*.active_ingredient_id.required' =>
            'Debe seleccionar un ingrediente activo.',

            'applications.*.active_ingredients.*.active_ingredient_id.exists' =>
            'El ingrediente activo seleccionado no existe.',

            /*
            |--------------------------------------------------------------------------
            | Productos del ingrediente activo
            |--------------------------------------------------------------------------
            */

            'applications.*.active_ingredients.*.products.required' =>
            'Debe seleccionar al menos un producto para el ingrediente activo.',

            'applications.*.active_ingredients.*.products.min' =>
            'Debe seleccionar al menos un producto para el ingrediente activo.',

            'applications.*.active_ingredients.*.products.*.product_id.required' =>
            'Debe seleccionar un producto recomendado.',

            'applications.*.active_ingredients.*.products.*.product_id.exists' =>
            'Uno de los productos recomendados no existe.',

            'applications.*.active_ingredients.*.products.*.dose.required' =>
            'Debe ingresar la dosis del producto recomendado.',

            'applications.*.active_ingredients.*.products.*.dose.gt' =>
            'La dosis del producto recomendado debe ser mayor que cero.',

            'applications.*.active_ingredients.*.products.*.unit.required' =>
            'Debe ingresar la unidad del producto recomendado.',

            'applications.*.active_ingredients.*.products.*.application_base.required' =>
            'Debe ingresar la base de aplicación del producto recomendado.',

            /*
            |--------------------------------------------------------------------------
            | Combinaciones de ingredientes activos
            |--------------------------------------------------------------------------
            */

            'applications.*.active_ingredient_combinations.*.active_ingredient_combination_id.required' =>
            'Debe seleccionar una combinación de ingredientes activos.',

            'applications.*.active_ingredient_combinations.*.active_ingredient_combination_id.exists' =>
            'La combinación de ingredientes activos seleccionada no existe.',

            'applications.*.active_ingredient_combinations.*.dose.required' =>
            'Debe ingresar la dosis de la combinación.',

            'applications.*.active_ingredient_combinations.*.dose.gt' =>
            'La dosis de la combinación debe ser mayor que cero.',

            'applications.*.active_ingredient_combinations.*.unit.required' =>
            'Debe ingresar la unidad de la dosis de la combinación.',

            'applications.*.active_ingredient_combinations.*.application_base.required' =>
            'Debe ingresar la base de aplicación de la combinación.',
        ];
    }
}
