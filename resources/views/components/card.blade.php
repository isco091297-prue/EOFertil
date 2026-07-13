<div
    {{ $attributes->merge([
        'class' => 'bg-white rounded-3xl shadow-2xl p-10 border border-green-100'
    ]) }}>

    {{ $slot }}

</div>
