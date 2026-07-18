@extends('layouts.app')

@section('title','Detalle Problema')

@section('content')

<x-card>

    <div class="mb-8 flex items-center justify-between">

        <h1 class="text-3xl font-bold">

            Detalle del Problema

        </h1>

        <a
            href="{{ route('problems.index') }}"
            class="rounded-xl border px-5 py-3 hover:bg-gray-100">

            Regresar

        </a>

    </div>

    @if($problem->image_path)

        <div class="mb-8 flex justify-center">

            <img
                src="{{ asset('storage/'.$problem->image_path) }}"
                class="h-64 w-64 rounded-2xl border object-cover shadow">

        </div>

    @endif

    <div class="space-y-5">

        <p>

            <strong>Código:</strong>

            {{ $problem->code }}

        </p>

        <p>

            <strong>Nombre:</strong>

            {{ $problem->name }}

        </p>

        <p>

            <strong>Cultivo:</strong>

            {{ $problem->crop->name }}

        </p>

        <p>

            <strong>Descripción:</strong>

        </p>

        <p class="text-gray-600">

            {{ $problem->description }}

        </p>

        <p>

            <strong>Estado:</strong>

            @if($problem->is_active)

                <span class="text-green-700 font-semibold">

                    Activo

                </span>

            @else

                <span class="text-red-700 font-semibold">

                    Inactivo

                </span>

            @endif

        </p>

    </div>

</x-card>

@endsection
