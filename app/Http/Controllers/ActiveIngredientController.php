<?php

namespace App\Http\Controllers;

use App\Models\ActiveIngredient;
use App\Models\Product;
use Illuminate\Http\Request;

class ActiveIngredientController extends Controller
{
    /**
     * Listado de ingredientes activos.
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $activeIngredients = ActiveIngredient::query()
            ->withCount('products')
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
            'active-ingredients.index',
            compact(
                'activeIngredients',
                'search'
            )
        );
    }

    /**
     * Formulario para crear ingrediente activo.
     */
    public function create()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->get();

        return view(
            'active-ingredients.create',
            compact('products')
        );
    }

    /**
     * Guardar ingrediente activo.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:active_ingredients,name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'products' => [
                'nullable',
                'array',
            ],
            'products.*' => [
                'integer',
                'exists:products,id',
            ],
        ]);

        $activeIngredient = ActiveIngredient::create([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vincular productos
        |--------------------------------------------------------------------------
        */

        $activeIngredient->products()->sync(
            $validated['products'] ?? []
        );

        return redirect()
            ->route('active-ingredients.index')
            ->with(
                'success',
                'Ingrediente activo creado correctamente.'
            );
    }

    /**
     * Mostrar ingrediente activo.
     */
    public function show(ActiveIngredient $activeIngredient)
    {
        $activeIngredient->load([
            'products.brand',
            'products.category',
        ]);

        return view(
            'active-ingredients.show',
            compact('activeIngredient')
        );
    }

    /**
     * Formulario para editar ingrediente activo.
     */
    public function edit(ActiveIngredient $activeIngredient)
    {
        $activeIngredient->load('products');

        $products = Product::query()
            ->where('is_active', true)
            ->with(['brand', 'category'])
            ->orderBy('name')
            ->get();

        return view(
            'active-ingredients.edit',
            compact(
                'activeIngredient',
                'products'
            )
        );
    }

    /**
     * Actualizar ingrediente activo.
     */
    public function update(
        Request $request,
        ActiveIngredient $activeIngredient
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:active_ingredients,name,' . $activeIngredient->id,
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'products' => [
                'nullable',
                'array',
            ],
            'products.*' => [
                'integer',
                'exists:products,id',
            ],
        ]);

        $activeIngredient->update([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Actualizar productos vinculados
        |--------------------------------------------------------------------------
        */

        $activeIngredient->products()->sync(
            $validated['products'] ?? []
        );

        return redirect()
            ->route('active-ingredients.index')
            ->with(
                'success',
                'Ingrediente activo actualizado correctamente.'
            );
    }

    /**
     * Eliminar ingrediente activo.
     */
    public function destroy(ActiveIngredient $activeIngredient)
    {
        /*
        |--------------------------------------------------------------------------
        | Desvincular productos
        |--------------------------------------------------------------------------
        */

        $activeIngredient->products()->detach();

        $activeIngredient->delete();

        return redirect()
            ->route('active-ingredients.index')
            ->with(
                'success',
                'Ingrediente activo eliminado correctamente.'
            );
    }
}
