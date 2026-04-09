@props(['label', 'name', 'error' => null])

{{--
    Contract: the caller is responsible for rendering an <input> inside
    the default slot with `id="{{ $name }}"` so that the <label for>
    association works. Example:
        <x-form-field label="Email" name="email">
            <input id="email" name="email" type="email">
        </x-form-field>
--}}
<div class="flex flex-col gap-1">
    <label for="{{ $name }}" class="text-sm font-medium text-gray-700">
        {{ $label }}
    </label>

    {{ $slot }}

    @if ($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
