@props(['variant' => 'primary', 'type' => 'button'])

@php
    $classes = match ($variant) {
        'secondary' => 'rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50',
        default => 'rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</button>
