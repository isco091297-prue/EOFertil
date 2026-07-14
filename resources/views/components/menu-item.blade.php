@props([
    'route',
    'label',
    'icon'
])

@php
    $active = $route !== '#' && request()->routeIs($route);
@endphp

<a
    href="{{ $route !== '#' ? route($route) : '#' }}"
    class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200
    {{ $active ? 'bg-white text-green-800 font-semibold shadow' : 'hover:bg-green-700 text-white' }}">

    <span class="text-lg">
        {{ $icon }}
    </span>

    <span>
        {{ $label }}
    </span>

</a>
