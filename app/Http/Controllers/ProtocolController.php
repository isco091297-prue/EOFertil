<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProtocolRequest;
use App\Http\Requests\UpdateProtocolRequest;

use App\Models\Crop;
use App\Models\Problem;
use App\Models\Product;
use App\Models\Protocol;

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
     * Mostrar listado de protocolos.
     */
    public function index(): View
    {
        $protocols = Protocol::with([
            'crop',
            'problem',
        ])
            ->latest()
            ->paginate(15);

        return view('protocols.index', compact('protocols'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): View
    {
        return view('protocols.create');
    }

    /**
     * Guardar un protocolo.
     */
    public function store(
        StoreProtocolRequest $request
    ): RedirectResponse {

        $protocol = $this->protocolService->store(
            $request->validated()
        );

        return redirect()
            ->route('protocols.index')
            ->with(
                'success',
                'Protocolo creado correctamente.'
            );
    }

    /**
     * Mostrar un protocolo.
     */
    public function show(
        Protocol $protocol
    ): View {

        $protocol->load([
            'crop',
            'problem',
            'applications.products.product.brand',
            'applications.products.product.category',
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
            'applications.products.product.brand',
            'applications.products.product.category',
        ]);

        return view(
            'protocols.edit',
            compact('protocol')
        );
    }

    /**
     * Actualizar un protocolo.
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
            ->route(
                'protocols.index',
                $protocol
            )
            ->with(
                'success',
                'Protocolo actualizado correctamente.'
            );
    }

    /**
     * Eliminar un protocolo.
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
                'Protocolo eliminado correctamente.'
            );
    }
    /**
     * Buscar cultivos.
     */
    public function searchCrops(Request $request): JsonResponse
    {
        $search = trim($request->get('search', ''));

        $crops = Crop::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $crops->map(fn($crop) => [
                'id' => $crop->id,
                'text' => $crop->name,
            ])
        );
    }

    /**
     * Buscar problemas por cultivo.
     */
    public function searchProblems(Request $request): JsonResponse
    {
        $cropId = $request->get('crop_id');
        $search = trim($request->get('search', ''));

        $problems = Problem::query()
            ->where('crop_id', $cropId)
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get([
                'id',
                'name',
            ]);

        return response()->json(
            $problems->map(fn($problem) => [
                'id' => $problem->id,
                'text' => $problem->name,
            ])
        );
    }

    /**
     * Buscar productos.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $search = trim($request->get('search', ''));

        $products = Product::query()
            ->where('is_active', true)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get([
                'id',
                'code',
                'name',
            ]);

        return response()->json(
            $products->map(fn($product) => [
                'id' => $product->id,
                'text' => "{$product->code} - {$product->name}",
            ])
        );
    }
}
