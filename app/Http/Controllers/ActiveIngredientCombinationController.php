<?php

namespace App\Http\Controllers;

use App\Models\ActiveIngredient;
use App\Models\ActiveIngredientCombination;
use App\Models\Product;
use Illuminate\Http\Request;

class ActiveIngredientCombinationController extends Controller
{
    /**
     * Listado de combinaciones.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $combinations = ActiveIngredientCombination::query()
            ->withCount([
                'activeIngredients',
                'products',
            ])
            ->when(
                $search,
                function ($query) use ($search) {
                    $query->where(
                        'name',
                        'like',
                        '%' . $search . '%'
                    );
                }
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view(
            'active-ingredient-combinations.index',
            compact(
                'combinations',
                'search'
            )
        );
    }

    /**
     * Formulario para crear combinación.
     */
    public function create()
    {
        $activeIngredients = ActiveIngredient::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'brand',
                'category',
            ])
            ->orderBy('name')
            ->get();

        return view(
            'active-ingredient-combinations.create',
            compact(
                'activeIngredients',
                'products'
            )
        );
    }

    /**
     * Guardar combinación.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'active_ingredients' => [
                'required',
                'array',
                'min:2',
            ],

            'active_ingredients.*' => [
                'integer',
                'distinct',
                'exists:active_ingredients,id',
            ],

            'products' => [
                'nullable',
                'array',
            ],

            'products.*' => [
                'integer',
                'distinct',
                'exists:products,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Obtener ingredientes seleccionados
        |--------------------------------------------------------------------------
        */

        $ingredients = ActiveIngredient::query()
            ->whereIn(
                'id',
                $validated['active_ingredients']
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Generar nombre automáticamente
        |--------------------------------------------------------------------------
        */

        $name = $ingredients
            ->pluck('name')
            ->map(fn($name) => trim($name))
            ->implode(' + ');

        /*
        |--------------------------------------------------------------------------
        | Crear combinación
        |--------------------------------------------------------------------------
        */

        $combination = ActiveIngredientCombination::create([
            'name' => $name,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vincular ingredientes activos
        |--------------------------------------------------------------------------
        */

        $combination->activeIngredients()->sync(
            $validated['active_ingredients']
        );

        /*
        |--------------------------------------------------------------------------
        | Vincular productos
        |--------------------------------------------------------------------------
        */

        $combination->products()->sync(
            $validated['products'] ?? []
        );

        return redirect()
            ->route('active-ingredient-combinations.index')
            ->with(
                'success',
                'Combinación de ingredientes creada correctamente.'
            );
    }

    /**
     * Mostrar combinación.
     */
    public function show(
        ActiveIngredientCombination $activeIngredientCombination
    ) {
        $activeIngredientCombination->load([
            'activeIngredients',
            'products.brand',
            'products.category',
        ]);

        return view(
            'active-ingredient-combinations.show',
            compact(
                'activeIngredientCombination'
            )
        );
    }

    /**
     * Formulario para editar combinación.
     */
    public function edit(
        ActiveIngredientCombination $activeIngredientCombination
    ) {
        $activeIngredientCombination->load([
            'activeIngredients',
            'products',
        ]);

        $activeIngredients = ActiveIngredient::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('is_active', true)
            ->with([
                'brand',
                'category',
            ])
            ->orderBy('name')
            ->get();

        return view(
            'active-ingredient-combinations.edit',
            compact(
                'activeIngredientCombination',
                'activeIngredients',
                'products'
            )
        );
    }

    /**
     * Actualizar combinación.
     */
    public function update(
        Request $request,
        ActiveIngredientCombination $activeIngredientCombination
    ) {
        $validated = $request->validate([

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'active_ingredients' => [
                'required',
                'array',
                'min:2',
            ],

            'active_ingredients.*' => [
                'integer',
                'distinct',
                'exists:active_ingredients,id',
            ],

            'products' => [
                'nullable',
                'array',
            ],

            'products.*' => [
                'integer',
                'distinct',
                'exists:products,id',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Obtener ingredientes seleccionados
        |--------------------------------------------------------------------------
        */

        $ingredients = ActiveIngredient::query()
            ->whereIn(
                'id',
                $validated['active_ingredients']
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Generar nuevamente el nombre
        |--------------------------------------------------------------------------
        */

        $name = $ingredients
            ->pluck('name')
            ->map(fn($name) => trim($name))
            ->implode(' + ');

        /*
        |--------------------------------------------------------------------------
        | Actualizar combinación
        |--------------------------------------------------------------------------
        */

        $activeIngredientCombination->update([
            'name' => $name,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Actualizar ingredientes activos
        |--------------------------------------------------------------------------
        */

        $activeIngredientCombination
            ->activeIngredients()
            ->sync(
                $validated['active_ingredients']
            );

        /*
        |--------------------------------------------------------------------------
        | Actualizar productos
        |--------------------------------------------------------------------------
        */

        $activeIngredientCombination
            ->products()
            ->sync(
                $validated['products'] ?? []
            );

        return redirect()
            ->route('active-ingredient-combinations.index')
            ->with(
                'success',
                'Combinación de ingredientes actualizada correctamente.'
            );
    }

    /**
     * Eliminar combinación.
     */
    public function destroy(
        ActiveIngredientCombination $activeIngredientCombination
    ) {
        /*
        |--------------------------------------------------------------------------
        | Las relaciones se eliminan automáticamente por
        | las claves foráneas con cascadeOnDelete().
        |--------------------------------------------------------------------------
        */

        $activeIngredientCombination->delete();

        return redirect()
            ->route('active-ingredient-combinations.index')
            ->with(
                'success',
                'Combinación de ingredientes eliminada correctamente.'
            );
    }
}
