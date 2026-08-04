<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\Warehouse;
use App\Models\Zone;

class ZoneController extends Controller
{
    public function index()
    {
        $search = request('search');

        $zones = Zone::query()
            ->when($search, function ($query) use ($search) {

                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })

            ->orderBy('name')

            ->paginate(10);

        return view('zones.index', compact('zones'));
    }

    public function create()
    {
        return view('zones.create');
    }

    public function store(StoreZoneRequest $request)
    {
        Zone::create($request->validated());

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zona creada correctamente.');
    }

    public function show(Zone $zone)
    {
        return view('zones.show', compact('zone'));
    }

    public function edit(Zone $zone)
    {
        return view('zones.edit', compact('zone'));
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        $zone->update($request->validated());

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zona actualizada correctamente.');
    }

    public function destroy(Zone $zone)
    {
        $zone->delete();

        return redirect()
            ->route('zones.index')
            ->with('success', 'Zona eliminada correctamente.');
    }
}
