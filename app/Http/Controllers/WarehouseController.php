<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        $search = request('search');

        $warehouses = Warehouse::query()
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10);

        return view('warehouses.index', compact('warehouses', 'search'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(StoreWarehouseRequest $request)
    {
        Warehouse::create($request->validated());

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Almacén creado correctamente.');
    }

    public function show(Warehouse $warehouse)
    {
        return view('warehouses.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Almacén actualizado correctamente.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return redirect()
            ->route('warehouses.index')
            ->with('success', 'Almacén eliminado correctamente.');
    }
}
