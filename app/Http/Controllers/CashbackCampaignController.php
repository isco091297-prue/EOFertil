<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCashbackCampaignRequest;
use App\Http\Requests\UpdateCashbackCampaignRequest;
use App\Models\CashbackCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashbackCampaignController extends Controller
{
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
            compact('campaigns', 'search')
        );
    }

    /**
     * Formulario crear.
     */
    public function create(): View
    {
        return view(
            'admin.incentivos.cashback_campaigns.create'
        );
    }

    /**
     * Guardar.
     */
    public function store(
        StoreCashbackCampaignRequest $request
    ): RedirectResponse {
        CashbackCampaign::create($request->validated());

        return redirect()
            ->route('cashback-campaigns.index')
            ->with('success', 'Campaña creada correctamente.');
    }

    /**
     * Formulario editar.
     */
    public function edit(
        CashbackCampaign $cashbackCampaign
    ): View {
        return view(
            'admin.incentivos.cashback_campaigns.edit',
            compact('cashbackCampaign')
        );
    }

    /**
     * Actualizar.
     */
    public function update(
        UpdateCashbackCampaignRequest $request,
        CashbackCampaign $cashbackCampaign
    ): RedirectResponse {
        $cashbackCampaign->update($request->validated());

        return redirect()
            ->route('cashback-campaigns.index')
            ->with('success', 'Campaña actualizada correctamente.');
    }

    /**
     * Eliminar.
     */
    public function destroy(
        CashbackCampaign $cashbackCampaign
    ): RedirectResponse {
        $cashbackCampaign->delete();

        return redirect()
            ->route('cashback-campaigns.index')
            ->with('success', 'Campaña eliminada correctamente.');
    }
}
