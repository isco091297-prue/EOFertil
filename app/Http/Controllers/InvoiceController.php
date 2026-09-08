<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class InvoiceController extends Controller
{
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

            /*
            |--------------------------------------------------------------------------
            | Búsqueda
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Filtro por estado
            |--------------------------------------------------------------------------
            */

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
        ]);

        return view(
            'invoices.show',
            compact('invoice')
        );
    }

    /**
     * Mostrar formulario de edición.
     *
     * IMPORTANTE:
     * En esta primera etapa solamente mostramos
     * la información de la factura.
     *
     * La modificación real se habilitará después
     * de implementar correctamente la reversión
     * y recalculación de cashback y rankings.
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
     * Actualizar factura.
     *
     * TEMPORALMENTE NO MODIFICA LA FACTURA.
     *
     * Esto es intencional para evitar que alguien
     * pueda cambiar valores antes de tener lista
     * la lógica financiera.
     */
    public function update(Invoice $invoice)
    {
        return redirect()
            ->route('invoices.show', $invoice)
            ->with(
                'error',
                'La modificación de facturas todavía no está habilitada.'
            );
    }
}
