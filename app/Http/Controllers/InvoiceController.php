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
     *
     * Solamente una factura en estado "procesando"
     * puede ser aprobada.
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
     *
     * El motivo es obligatorio.
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

            $motivo = trim(
                (string) $request->input('motivo')
            );

            if ($motivo === '') {

                return back()
                    ->withInput()
                    ->withErrors([
                        'motivo' =>
                        'El motivo de anulación es obligatorio.',
                    ]);
            }

            $this->invoiceAdminService->annul(
                invoice: $invoice,
                adminUserId: (int) $request->user()->id,
                motivo: $motivo,
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
     * Actualizar / modificar factura.
     *
     * IMPORTANTE:
     *
     * El administrador SOLO puede modificar:
     *
     * - valor de cada producto registrado.
     * - motivo de la modificación.
     *
     * NO puede modificar desde este formulario:
     *
     * - número de factura
     * - fecha de factura
     * - total de factura
     * - campaña
     * - porcentaje de cashback
     * - total de productos participantes
     *
     * Todos esos valores son conservados/calculados
     * directamente desde la factura almacenada.
     */
    public function update(
        Request $request,
        Invoice $invoice
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Validar únicamente los datos que realmente puede modificar
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([

            'items' => [
                'required',
                'array',
                'min:1',
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
        | Limpiar motivo
        |--------------------------------------------------------------------------
        */

        $motivo = trim(
            (string) $validated['motivo']
        );


        if ($motivo === '') {

            return back()
                ->withInput()
                ->withErrors([
                    'motivo' =>
                    'El motivo de la modificación es obligatorio.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Validar que los items enviados pertenezcan a esta factura
        |--------------------------------------------------------------------------
        |
        | Esto es MUY importante.
        |
        | No permitimos que alguien intente enviar:
        |
        | items[999][valor] = 500
        |
        | perteneciendo el item 999 a otra factura.
        |
        */

        $invoiceItemIds = $invoice->items()
            ->pluck('id')
            ->map(
                fn($id) => (int) $id
            )
            ->values();


        $submittedItemIds = collect(
            array_keys(
                $validated['items']
            )
        )
            ->map(
                fn($id) => (int) $id
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Todos los items enviados deben pertenecer a la factura
        |--------------------------------------------------------------------------
        */

        $invalidItemIds = $submittedItemIds
            ->diff($invoiceItemIds);


        if ($invalidItemIds->isNotEmpty()) {

            return back()
                ->withInput()
                ->withErrors([
                    'items' =>
                    'Se detectaron productos que no pertenecen a esta factura.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | El formulario debe contener todos los productos registrados
        |--------------------------------------------------------------------------
        |
        | Esto evita que alguien elimine silenciosamente un producto
        | omitiéndolo de la petición.
        |
        */

        $missingItemIds = $invoiceItemIds
            ->diff($submittedItemIds);


        if ($missingItemIds->isNotEmpty()) {

            return back()
                ->withInput()
                ->withErrors([
                    'items' =>
                    'Debes mantener todos los productos registrados en la factura.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Construir los datos que realmente recibirá el servicio
        |--------------------------------------------------------------------------
        |
        | MUY IMPORTANTE:
        |
        | NO tomamos del Request:
        |
        | numero_factura_original
        | fecha_factura
        | total_factura
        | total_productos_participantes
        |
        | Esos valores salen directamente de la factura almacenada.
        |
        */

        $data = [

            /*
            |--------------------------------------------------------------------------
            | Número original
            |--------------------------------------------------------------------------
            */

            'numero_factura_original' =>
            $invoice->numero_factura_original,

            /*
            |--------------------------------------------------------------------------
            | Fecha original
            |--------------------------------------------------------------------------
            */

            'fecha_factura' =>
            optional(
                $invoice->fecha_factura
            )->format('Y-m-d'),

            /*
            |--------------------------------------------------------------------------
            | Total original de factura
            |--------------------------------------------------------------------------
            */

            'total_factura' =>
            (float) $invoice->total_factura,

            /*
            |--------------------------------------------------------------------------
            | Productos modificados
            |--------------------------------------------------------------------------
            */

            'items' =>
            $validated['items'],

            /*
            |--------------------------------------------------------------------------
            | Motivo
            |--------------------------------------------------------------------------
            */

            'motivo' =>
            $motivo,
        ];


        /*
        |--------------------------------------------------------------------------
        | Ejecutar modificación administrativa
        |--------------------------------------------------------------------------
        */

        try {

            $this->invoiceAdminService->update(
                invoice: $invoice,
                data: $data,
                adminUserId: (int) $request->user()->id,
            );

            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'success',
                    'La factura fue modificada correctamente. El cashback fue recalculado utilizando la campaña asociada a esta factura.'
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
