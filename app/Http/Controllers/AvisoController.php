<?php

namespace App\Http\Controllers;

use App\Models\Aviso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class AvisoController extends Controller
{
    public function index(): View
    {
        $avisos = Aviso::query()
            ->orderByDesc('activo')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('avisos.index', compact('avisos'));
    }

    public function create(): View
    {
        return view('avisos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAviso($request);

        Aviso::create($validated);

        return redirect()
            ->route('avisos.index')
            ->with('success', 'Aviso creado correctamente.');
    }

    public function edit(Aviso $aviso): View
    {
        return view('avisos.edit', compact('aviso'));
    }

    public function update(Request $request, Aviso $aviso): RedirectResponse
    {
        $validated = $this->validateAviso($request);

        $aviso->update($validated);

        return redirect()
            ->route('avisos.index')
            ->with('success', 'Aviso actualizado correctamente.');
    }

    public function toggle(Aviso $aviso): RedirectResponse
    {
        $aviso->update([
            'activo' => ! $aviso->activo,
        ]);

        return redirect()
            ->route('avisos.index')
            ->with(
                'success',
                $aviso->activo
                    ? 'Aviso activado correctamente.'
                    : 'Aviso desactivado correctamente.'
            );
    }

    public function destroy(Aviso $aviso): RedirectResponse
    {
        $aviso->delete();

        return redirect()
            ->route('avisos.index')
            ->with('success', 'Aviso eliminado correctamente.');
    }

    private function validateAviso(Request $request): array
    {
        $validated = $request->validate([
            'titulo' => [
                'required',
                'string',
                'max:150',
            ],
            'mensaje' => [
                'required',
                'string',
            ],
            'tipo' => [
                'required',
                Rule::in([
                    'informacion',
                    'promocion',
                    'importante',
                    'actualizacion',
                ]),
            ],
            'enlace' => [
                'nullable',
                'string',
                'url',
                'max:2048',
            ],
            'activo' => [
                'nullable',
                'boolean',
            ],
        ], [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.max' => 'El título no puede superar los 150 caracteres.',
            'mensaje.required' => 'El mensaje es obligatorio.',
            'tipo.required' => 'Debes seleccionar un tipo de aviso.',
            'tipo.in' => 'El tipo de aviso seleccionado no es válido.',
            'enlace.url' => 'El enlace debe ser una URL válida.',
            'enlace.max' => 'El enlace es demasiado largo.',
        ]);

        if (
            $validated['tipo'] === 'actualizacion' &&
            empty($validated['enlace'])
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'enlace' => 'Para un aviso de actualización debes ingresar el enlace de descarga.',
            ]);
        }

        $validated['activo'] = $request->boolean('activo');

        return $validated;
    }
}