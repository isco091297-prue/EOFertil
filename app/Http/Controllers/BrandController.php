<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Mostrar listado.
     */
    public function index(Request $request): View
    {
        $search = trim($request->get('search', ''));

        $brands = Brand::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('brands.index', compact('brands', 'search'));
    }

    /**
     * Formulario crear.
     */
    public function create(): View
    {
        return view('brands.create');
    }

    /**
     * Guardar.
     */
    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        unset($data['logo']);

        Brand::create($data);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca creada correctamente.');
    }

    /**
     * Ver.
     */
    public function show(Brand $brand): View
    {
        return view('brands.show', compact('brand'));
    }

    /**
     * Formulario editar.
     */
    public function edit(Brand $brand): View
    {
        return view('brands.edit', compact('brand'));
    }

    /**
     * Actualizar.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {

            if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
                Storage::disk('public')->delete($brand->logo_path);
            }

            $data['logo_path'] = $request->file('logo')->store('brands', 'public');
        }

        unset($data['logo']);

        $brand->update($data);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca actualizada correctamente.');
    }

    /**
     * Eliminar.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->logo_path && Storage::disk('public')->exists($brand->logo_path)) {
            Storage::disk('public')->delete($brand->logo_path);
        }

        $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with('success', 'Marca eliminada correctamente.');
    }
}
