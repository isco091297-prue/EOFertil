@extends('layouts.app')

@section('content')

<x-card>

    <div class="flex justify-between items-center mb-8">

        <div>

            <h1 class="text-3xl font-bold">

                Editar Usuario

            </h1>

            <p class="text-gray-500">

                Modificación de información.

            </p>

        </div>

    </div>

    <form
        action="{{ route('users.update',$user) }}"
        method="POST">

        @method('PUT')

        @include('users.form')

    </form>

</x-card>

@endsection
