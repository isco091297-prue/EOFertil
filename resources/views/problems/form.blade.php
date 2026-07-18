@csrf

<div class="grid grid-cols-2 gap-6">

    <div>

        <label class="block mb-2 font-semibold">
            Código
        </label>

        <x-input
            type="text"
            name="code"
            value="{{ old('code', $problem->code ?? '') }}"
            required />

        @error('code')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

    <div>

        <label class="block mb-2 font-semibold">
            Nombre
        </label>

        <x-input
            type="text"
            name="name"
            value="{{ old('name', $problem->name ?? '') }}"
            required />

        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

    </div>

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Cultivo
    </label>

    <select
        name="crop_id"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">

        <option value="">
            Seleccione...
        </option>

        @foreach($crops as $crop)

            <option
                value="{{ $crop->id }}"
                {{ old('crop_id',$problem->crop_id ?? '') == $crop->id ? 'selected' : '' }}>

                {{ $crop->name }}

            </option>

        @endforeach

    </select>

    @error('crop_id')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Imagen
    </label>

    @if(isset($problem) && $problem->image_path)

        <img
            src="{{ asset('storage/'.$problem->image_path) }}"
            class="mb-4 h-40 w-40 rounded-xl border object-cover">

    @endif

    <input
        type="file"
        name="image"
        accept=".jpg,.jpeg,.png,.webp"
        class="block w-full rounded-xl border border-gray-300 px-4 py-3">

    @error('image')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Descripción
    </label>

    <textarea
        rows="4"
        name="description"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">{{ old('description',$problem->description ?? '') }}</textarea>

    @error('description')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

</div>

<div class="mt-6">

    <label class="block mb-2 font-semibold">
        Estado
    </label>

    <select
        name="is_active"
        class="w-full rounded-xl border border-gray-300 px-4 py-3">

        <option
            value="1"
            {{ old('is_active',$problem->is_active ?? 1) == 1 ? 'selected' : '' }}>
            Activo
        </option>

        <option
            value="0"
            {{ old('is_active',$problem->is_active ?? 1) == 0 ? 'selected' : '' }}>
            Inactivo
        </option>

    </select>

</div>

<div class="mt-8 flex justify-end gap-4">

    <a
        href="{{ route('problems.index') }}"
        class="rounded-xl border border-gray-300 px-6 py-3 hover:bg-gray-100">

        Cancelar

    </a>

    <x-button>

        Guardar

    </x-button>

</div>
