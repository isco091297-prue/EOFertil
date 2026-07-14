<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\Zone;

class BranchController extends Controller
{
    public function index()
    {
        $search = request('search');

        $branches = Branch::with(['warehouse', 'zone'])

            ->when($search, function ($query) use ($search) {

                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");

            })

            ->orderBy('name')

            ->paginate(10);

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();

        $zones = Zone::orderBy('name')->get();

        return view('branches.create', compact(
            'warehouses',
            'zones'
        ));
    }

    public function store(StoreBranchRequest $request)
    {
        Branch::create($request->validated());

        return redirect()
            ->route('branches.index')
            ->with('success', 'Sucursal creada correctamente.');
    }

    public function show(Branch $branch)
    {
        return view('branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $warehouses = Warehouse::orderBy('name')->get();

        $zones = Zone::orderBy('name')->get();

        return view(
            'branches.edit',
            compact(
                'branch',
                'warehouses',
                'zones'
            )
        );
    }

    public function update(
        UpdateBranchRequest $request,
        Branch $branch
    )
    {
        $branch->update($request->validated());

        return redirect()
            ->route('branches.index')
            ->with('success', 'Sucursal actualizada correctamente.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', 'Sucursal eliminada correctamente.');
    }
}
