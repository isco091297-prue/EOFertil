<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashbackCampaignRequest;
use App\Http\Requests\UpdateCashbackCampaignRequest;

use App\Models\Branch;
use App\Models\CashbackCampaign;
use App\Models\Warehouse;
use App\Models\Zone;

use App\Services\Cashback\CashbackCampaignService;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashbackCampaignController extends Controller
{
    public function __construct(
        private readonly CashbackCampaignService $service
    ) {}

    /**
     * Mostrar listado.
     */
    public function index(Request $request): View
    {
        $search = trim($request->get('search'));

        $campaigns = CashbackCampaign::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->orderByDesc('fecha_inicio')
            ->paginate(10)
            ->withQueryString();

        return view(
            'admin.incentivos.cashback_campaigns.index',
            compact(
                'campaigns',
                'search'
            )
        );
    }

    /**
     * Formulario crear.
     */
    public function create(): View
    {
        return view(
            'admin.incentivos.cashback_campaigns.create',
            [
                'warehouses' => Warehouse::orderBy('name')->get(),
                'zones' => Zone::orderBy('name')->get(),
                'branches' => Branch::orderBy('name')->get(),
            ]
        );
    }

    /**
     * Guardar.
     */
    public function store(
        StoreCashbackCampaignRequest $request
    ): RedirectResponse {

        $this->service->store(
            $request->validated()
        );

        return redirect()
            ->route('cashback-campaigns.index')
            ->with(
                'success',
                'Campaña creada correctamente.'
            );
    }

    /**
     * Formulario editar.
     */
    public function edit(
        CashbackCampaign $cashbackCampaign
    ): View {

        $cashbackCampaign->load('scopes');

        return view(
            'admin.incentivos.cashback_campaigns.edit',
            [
                'cashbackCampaign' => $cashbackCampaign,
                'warehouses' => Warehouse::orderBy('name')->get(),
                'zones' => Zone::orderBy('name')->get(),
                'branches' => Branch::orderBy('name')->get(),
            ]
        );
    }

    /**
     * Actualizar.
     */
    public function update(
        UpdateCashbackCampaignRequest $request,
        CashbackCampaign $cashbackCampaign
    ): RedirectResponse {

        $this->service->update(
            $cashbackCampaign,
            $request->validated()
        );

        return redirect()
            ->route('cashback-campaigns.index')
            ->with(
                'success',
                'Campaña actualizada correctamente.'
            );
    }

    /**
     * Eliminar.
     */
    public function destroy(
        CashbackCampaign $cashbackCampaign
    ): RedirectResponse {

        $this->service->delete(
            $cashbackCampaign
        );

        return redirect()
            ->route('cashback-campaigns.index')
            ->with(
                'success',
                'Campaña eliminada correctamente.'
            );
    }
}
