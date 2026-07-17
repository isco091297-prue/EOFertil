<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCropRequest;
use App\Http\Requests\UpdateCropRequest;
use App\Models\Crop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CropController extends Controller
{
    /**
     * Mostrar listado de cultivos.
     */
    public function index(): View
    {
        $search = request('search');

        $crops = Crop::query()
            ->when($search, function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('crops.index', compact(
            'crops',
            'search'
        ));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): View
    {
        return view('crops.create');
    }

    /**
     * Guardar un nuevo cultivo.
     */
    public function store(StoreCropRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('crops', 'public');
        }

        Crop::create($data);

        return redirect()
            ->route('crops.index')
            ->with('success', 'Cultivo creado correctamente.');
    }

    /**
     * Mostrar detalle del cultivo.
     */
    public function show(Crop $crop): View
    {
        return view('crops.show', compact('crop'));
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Crop $crop): View
    {
        return view('crops.edit', compact('crop'));
    }

    /**
     * Actualizar cultivo.
     */
    public function update(UpdateCropRequest $request, Crop $crop): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {

            if ($crop->image_path) {
                Storage::disk('public')->delete($crop->image_path);
            }

            $data['image_path'] = $request->file('image')->store('crops', 'public');
        }

        $crop->update($data);

        return redirect()
            ->route('crops.index')
            ->with('success', 'Cultivo actualizado correctamente.');
    }

    /**
     * Eliminar cultivo.
     */
    public function destroy(Crop $crop): RedirectResponse
    {
        if ($crop->image_path) {
            Storage::disk('public')->delete($crop->image_path);
        }

        $crop->delete();

        return redirect()
            ->route('crops.index')
            ->with('success', 'Cultivo eliminado correctamente.');
    }
}
