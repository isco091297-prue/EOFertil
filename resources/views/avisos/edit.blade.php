@extends('layouts.admin')

@section('title', 'Editar aviso')

@section('content')
<div class="container-fluid py-3">

    <div class="mb-3">
        <h4 class="mb-1">Editar aviso</h4>
        <div class="text-muted small">
            Modifica el contenido del aviso.
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <form method="POST" action="{{ route('avisos.update', $aviso) }}">
                @csrf
                @method('PUT')

                <div class="row g-3">

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">
                            Título
                        </label>

                        <input
                            type="text"
                            name="titulo"
                            value="{{ old('titulo', $aviso->titulo) }}"
                            class="form-control"
                            maxlength="150"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Tipo
                        </label>

                        <select
                            name="tipo"
                            id="tipo"
                            class="form-select"
                            required
                        >
                            <option value="informacion" @selected(old('tipo', $aviso->tipo) === 'informacion')}>
                                📢 Información
                            </option>
                            <option value="promocion" @selected(old('tipo', $aviso->tipo) === 'promocion')}>
                                🎁 Promoción
                            </option>
                            <option value="importante" @selected(old('tipo', $aviso->tipo) === 'importante')}>
                                ⚠️ Importante
                            </option>
                            <option value="actualizacion" @selected(old('tipo', $aviso->tipo) === 'actualizacion')}>
                                🚀 Actualización
                            </option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Mensaje
                        </label>

                        <textarea
                            name="mensaje"
                            class="form-control"
                            rows="7"
                            required
                        >{{ old('mensaje', $aviso->mensaje) }}</textarea>

                        <div class="form-text">
                            Puedes utilizar emojis, saltos de línea y pegar contenido directamente.
                        </div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">
                            Enlace
                            <span class="text-muted fw-normal">(opcional)</span>
                        </label>

                        <input
                            type="url"
                            name="enlace"
                            id="enlace"
                            value="{{ old('enlace', $aviso->enlace) }}"
                            class="form-control"
                            placeholder="https://drive.google.com/..."
                        >

                        <div class="form-text">
                            En actualizaciones será el enlace utilizado por el botón
                            <strong>Descargar última versión</strong>.
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="activo"
                                value="1"
                                id="activo"
                                @checked(old('activo', $aviso->activo))
                            >

                            <label class="form-check-label" for="activo">
                                Aviso activo
                            </label>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a
                        href="{{ route('avisos.index') }}"
                        class="btn btn-light"
                    >
                        Cancelar
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Guardar cambios
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipo = document.getElementById('tipo');
    const enlace = document.getElementById('enlace');

    function actualizarEnlace() {
        enlace.required = tipo.value === 'actualizacion';
    }

    tipo.addEventListener('change', actualizarEnlace);
    actualizarEnlace();
});
</script>
@endsection