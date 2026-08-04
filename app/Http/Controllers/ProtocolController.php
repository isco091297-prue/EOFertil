<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProtocolRequest;
use App\Http\Requests\UpdateProtocolRequest;

use App\Models\ActiveIngredient;
use App\Models\Crop;
use App\Models\Problem;
use App\Models\Product;
use App\Models\Protocol;
use App\Models\Brand;

use App\Services\Protocol\ProtocolService;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProtocolController extends Controller
{
    public function __construct(
        private readonly ProtocolService $protocolService
    ) {}

    /**
     * Mostrar listado de receta.
     */
    public function index(): View
    {
        $protocols = Protocol::with([
            'crop',
            'problem',
        ])
            ->latest()
            ->paginate(15);

        return view(
            'protocols.index',
            compact('protocols')
        );
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): View
    {
        return view('protocols.create');
    }

    /**
     * Guardar un receta.
     */
    public function store(
        StoreProtocolRequest $request
    ): RedirectResponse {

        $this->protocolService->store(
            $request->validated()
        );

        return redirect()
            ->route('protocols.index')
            ->with(
                'success',
                'Receta creada correctamente.'
            );
    }

    /**
     * Mostrar un receta.
     */
    public function show(
        Protocol $protocol
    ): View {

        $protocol->load([
            'crop',
            'problem',

            /*
            |--------------------------------------------------------------
            | Productos EOFertil directos
            |--------------------------------------------------------------
            */

            'applications.products.product.brand',
            'applications.products.product.category',

            /*
            |--------------------------------------------------------------
            | Ingredientes activos
            |--------------------------------------------------------------
            */

            'applications.activeIngredients.activeIngredient',

            /*
            |--------------------------------------------------------------
            | Productos recomendados de cada ingrediente
            |--------------------------------------------------------------
            */

            'applications.activeIngredients.products.product.brand',
            'applications.activeIngredients.products.product.category',
        ]);

        return view(
            'protocols.show',
            compact('protocol')
        );
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(
        Protocol $protocol
    ): View {

        $protocol->load([
            'crop',
            'problem',

            /*
            |--------------------------------------------------------------
            | Productos EOFertil directos
            |--------------------------------------------------------------
            */

            'applications.products.product.brand',
            'applications.products.product.category',

            /*
            |--------------------------------------------------------------
            | Ingredientes activos
            |--------------------------------------------------------------
            */

            'applications.activeIngredients.activeIngredient',

            /*
            |--------------------------------------------------------------
            | Productos recomendados
            |--------------------------------------------------------------
            */

            'applications.activeIngredients.products.product.brand',
            'applications.activeIngredients.products.product.category',
        ]);

        return view(
            'protocols.edit',
            compact('protocol')
        );
    }

    /**
     * Actualizar un receta.
     */
    public function update(
        UpdateProtocolRequest $request,
        Protocol $protocol
    ): RedirectResponse {

        $this->protocolService->update(
            $protocol,
            $request->validated()
        );

        return redirect()
            ->route('protocols.index')
            ->with(
                'success',
                'Receta actualizada correctamente.'
            );
    }

    /**
     * Eliminar un receta.
     */
    public function destroy(
        Protocol $protocol
    ): RedirectResponse {

        $this->protocolService->delete(
            $protocol
        );

        return redirect()
            ->route('protocols.index')
            ->with(
                'success',
                'Receta eliminada correctamente.'
            );
    }

    /**
     * Buscar cultivos.
     */
    public function searchCrops(
        Request $request
    ): JsonResponse {

        $search = trim(
            $request->get('search', '')
        );

        $crops = Crop::query()
            ->where('is_active', true)
            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                }
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $crops->map(
                fn($crop) => [
                    'id' => $crop->id,
                    'text' => $crop->name,
                ]
            )
        );
    }

    /**
     * Buscar problemas por cultivo.
     */
    public function searchProblems(
        Request $request
    ): JsonResponse {

        $cropId = $request->get('crop_id');

        $search = trim(
            $request->get('search', '')
        );

        $problems = Problem::query()
            ->where('crop_id', $cropId)
            ->where('is_active', true)
            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                }
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $problems->map(
                fn($problem) => [
                    'id' => $problem->id,
                    'text' => $problem->name,
                ]
            )
        );
    }

    /**
     * Buscar productos EOFertil.
     *
     * Estos son los productos que pueden agregarse
     * directamente a una aplicación.
     */
    public function searchProducts(
        Request $request
    ): JsonResponse {

        $search = trim(
            $request->get('search', '')
        );

        $eofertilBrand = Brand::query()
            ->where('name', 'EOFertil')
            ->first();

        $products = Product::query()
            ->where('is_active', true)
            ->when(
                $eofertilBrand,
                function ($query) use ($eofertilBrand) {
                    $query->where(
                        'brand_id',
                        $eofertilBrand->id
                    );
                }
            )
            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(function ($q) use ($search) {

                        $q->where(
                            'code',
                            'like',
                            "%{$search}%"
                        )
                            ->orWhere(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )
            ->orderBy('name')
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json(
            $products->map(
                fn($product) => [
                    'id' => $product->id,
                    'text' => "{$product->code} - {$product->name}",
                ]
            )
        );
    }

    /**
     * Buscar ingredientes activos.
     */
    public function searchActiveIngredients(
        Request $request
    ): JsonResponse {

        $search = trim(
            $request->get('search', '')
        );

        $activeIngredients = ActiveIngredient::query()
            ->where('is_active', true)
            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(
                        'name',
                        'like',
                        "%{$search}%"
                    );
                }
            )
            ->orderBy('name')

            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $activeIngredients->map(
                fn($activeIngredient) => [
                    'id' => $activeIngredient->id,
                    'text' => $activeIngredient->name,
                ]
            )
        );
    }

    /**
     * Obtener los productos vinculados
     * a un ingrediente activo.
     */
    public function activeIngredientProducts(
        ActiveIngredient $activeIngredient
    ): JsonResponse {

        /*
        |--------------------------------------------------------------
        | Cargar únicamente productos activos
        |--------------------------------------------------------------
        |
        | Esta relación ya existe en ActiveIngredient porque la
        | construimos anteriormente mediante la tabla pivote
        | active_ingredient_product.
        |
        */

        $products = $activeIngredient
            ->products()
            ->where('products.is_active', true)
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->orderBy('products.name')
            ->get();

        /*
        |--------------------------------------------------------------
        | Respuesta para el formulario
        |--------------------------------------------------------------
        */

        return response()->json(
            $products->map(
                fn($product) => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,

                    'brand' =>
                    $product->brand?->name,

                    'category' =>
                    $product->category?->name,

                    'text' =>
                    "{$product->code} - {$product->name}",
                ]
            )
        );
    }
}
