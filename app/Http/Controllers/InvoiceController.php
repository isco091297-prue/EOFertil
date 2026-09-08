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
     * Actualizar factura.
     *
     * La modificación financiera todavía no se habilita.
     */
    public function update(
        Request $request,
        Invoice $invoice
    ): RedirectResponse {

        return redirect()
            ->route('invoices.show', $invoice)
            ->with(
                'error',
                'La modificación de facturas todavía no está habilitada.'
            );
    }
}
