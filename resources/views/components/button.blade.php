<button
    {{ $attributes->merge([
        'class' => 'w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 rounded-xl transition duration-300'
    ]) }}>
    {{ $slot }}
</button>
