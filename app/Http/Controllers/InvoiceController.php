<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Invoice\InvoiceAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceAdminService $invoiceAdminService
    ) {}

    /**
     * Listado de facturas.
     */
    public function index()
    {
        $search = request('search');
        $estado = request('estado');

        $invoices = Invoice::query()
            ->with([
                'user',
                'branch',
                'cashbackCampaign',
            ])

            ->when($search, function ($query) use ($search) {

                $query->where(function ($query) use ($search) {

                    $query
                        ->where(
                            'numero_factura_original',
                            'like',
                            "%{$search}%"
                        )

                        ->orWhereHas('user', function ($query) use ($search) {

                            $query
                                ->where(
                                    'first_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'identification',
                                    'like',
                                    "%{$search}%"
                                );
                        });
                });
            })

            ->when($estado, function ($query) use ($estado) {

                $query->where(
                    'estado',
                    $estado
                );
            })

            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view(
            'invoices.index',
            compact('invoices')
        );
    }

    /**
     * Mostrar detalle de una factura.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'user',
            'branch',
            'cashbackCampaign',
            'items.product',
            'cashbackTransactions',
            'audits.admin',
        ]);

        return view(
            'invoices.show',
            compact('invoice')
        );
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Invoice $invoice)
    {
        $invoice->load([
            'user',
            'branch',
            'cashbackCampaign',
            'items.product',
        ]);

        return view(
            'invoices.edit',
            compact('invoice')
        );
    }

    /**
     * Aprobar factura.
     */
    public function approve(
        Request $request,
        Invoice $invoice
    ): RedirectResponse {

        $request->validate([
            'motivo' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        try {

            $this->invoiceAdminService->approve(
                invoice: $invoice,
                adminUserId: (int) $request->user()->id,
                motivo: $request->input('motivo'),
            );

            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'success',
                    'La factura fue aprobada correctamente.'
                );
        } catch (RuntimeException $e) {

            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Anular factura.
     */
    public function annul(
        Request $request,
        Invoice $invoice
    ): RedirectResponse {

        $request->validate([
            'motivo' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        try {

            $this->invoiceAdminService->annul(
                invoice: $invoice,
                adminUserId: (int) $request->user()->id,
                motivo: $request->input('motivo'),
            );

            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'success',
                    'La factura fue anulada correctamente.'
                );
        } catch (RuntimeException $e) {

            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Actualizar factura.
     */
    public function update(
        Request $request,
        Invoice $invoice
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'numero_factura_original' => [
                'required',
                'string',
                'max:100',
            ],

            'fecha_factura' => [
                'required',
                'date',
            ],

            'total_factura' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'total_productos_participantes' => [
                'required',
                'numeric',
                'min:0',
            ],

            'items' => [
                'nullable',
                'array',
            ],

            'items.*.valor' => [
                'required',
                'numeric',
                'min:0',
            ],

            'motivo' => [
                'required',
                'string',
                'max:1000',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Validar coherencia del total participante
        |--------------------------------------------------------------------------
        */

        $items = $validated['items'] ?? [];

        $totalItems = round(
            collect($items)
                ->sum(function ($item) {
                    return (float) ($item['valor'] ?? 0);
                }),
            2
        );

        $totalParticipantes = round(
            (float) $validated['total_productos_participantes'],
            2
        );

        /*
        |--------------------------------------------------------------------------
        | El total de productos participantes debe coincidir
        | con la suma de sus productos.
        |--------------------------------------------------------------------------
        */

        if (abs($totalItems - $totalParticipantes) > 0.01) {

            return back()
                ->withInput()
                ->withErrors([
                    'total_productos_participantes' =>
                    'El total de productos participantes ($'
                        . number_format($totalItems, 2)
                        . ') debe coincidir con la suma de los productos registrados.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ejecutar modificación
        |--------------------------------------------------------------------------
        */

        try {

            $validated['motivo'] =
                trim($validated['motivo']);

            $this->invoiceAdminService->update(
                invoice: $invoice,
                data: $validated,
                adminUserId: (int) $request->user()->id,
            );

            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'success',
                    'La factura fue modificada correctamente. El cashback y el acumulado fueron recalculados.'
                );
        } catch (RuntimeException $e) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }
}
