@extends('layouts.app')

@section('content')

    <x-card>
        <div class="mb-8">
            <h1 class="text-3xl font-bold">
                Nuevo aviso
            </h1>

            <p class="text-gray-500 mt-1">
                Crea un mensaje que aparecerá en la aplicación móvil.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-100 border border-red-300 text-red-700 p-4">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('avisos.store') }}">
            @csrf

            <div class="space-y-6">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Título
                    </label>

                    <x-input
                        type="text"
                        name="titulo"
                        placeholder="Ej. Nueva promoción disponible"
                        value="{{ old('titulo') }}"
                    />

                    @error('titulo')
                        <p class="text-red-600 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mensaje
                    </label>

                    <textarea
                        name="mensaje"
                        rows="8"
                        placeholder="Escribe aquí el mensaje del aviso..."
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-green-600 focus:border-transparent resize-y"
                    >{{ old('mensaje') }}</textarea>

                    <p class="text-xs text-gray-500 mt-2">
                        Puedes utilizar emojis y saltos de línea.
                    </p>

                    @error('mensaje')
                        <p class="text-red-600 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Tipo de aviso
                    </label>

                    <select
                        name="tipo"
                        id="tipo"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-green-600"
                    >
                        <option value="informacion" @selected(old('tipo', 'informacion') === 'informacion')>
                            Información
                        </option>

                        <option value="promocion" @selected(old('tipo') === 'promocion')>
                            Promoción
                        </option>

                        <option value="importante" @selected(old('tipo') === 'importante')>
                            Importante
                        </option>

                        <option value="actualizacion" @selected(old('tipo') === 'actualizacion')>
                            Actualización
                        </option>
                    </select>

                    @error('tipo')
                        <p class="text-red-600 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div id="enlace-container">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Enlace
                    </label>

                    <x-input
                        type="url"
                        name="enlace"
                        placeholder="https://..."
                        value="{{ old('enlace') }}"
                    />

                    <p id="enlace-help" class="text-xs text-gray-500 mt-2">
                        Opcional. Úsalo cuando el aviso necesite abrir una página o realizar una acción.
                    </p>

                    @error('enlace')
                        <p class="text-red-600 text-sm mt-1">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        name="activo"
                        value="1"
                        id="activo"
                        class="w-5 h-5 text-green-700 border-gray-300 rounded focus:ring-green-600"
                        @checked(old('activo', true))
                    >

                    <label for="activo" class="font-semibold text-gray-700">
                        Publicar aviso inmediatamente
                    </label>
                </div>

                <div class="flex gap-3 pt-4">
                    <button
                        type="submit"
                        class="bg-green-700 hover:bg-green-800 text-white px-6 py-3 rounded-xl font-semibold"
                    >
                        Guardar aviso
                    </button>

                    <a
                        href="{{ route('avisos.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-xl font-semibold"
                    >
                        Cancelar
                    </a>
                </div>

            </div>
        </form>
    </x-card>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipo = document.getElementById('tipo');
            const enlace = document.querySelector('input[name="enlace"]');
            const help = document.getElementById('enlace-help');

            function actualizarEnlace() {
                if (tipo.value === 'actualizacion') {
                    enlace.required = true;
                    help.textContent = 'Obligatorio para una actualización. La aplicación mostrará automáticamente el botón "Descargar última versión".';
                } else {
                    enlace.required = false;
                    help.textContent = 'Opcional. Úsalo cuando el aviso necesite abrir una página o realizar una acción.';
                }
            }

            tipo.addEventListener('change', actualizarEnlace);

            actualizarEnlace();
        });
    </script>

@endsection