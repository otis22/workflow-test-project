@props(['label', 'name', 'error' => null])

<div class="flex flex-col gap-1">
    <label for="{{ $name }}" class="text-sm font-medium text-gray-700">
        {{ $label }}
    </label>

    {{ $slot }}

    @if ($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
