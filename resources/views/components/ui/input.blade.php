@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'error' => null,
    'required' => false,
    'placeholder' => '',
    'value' => '',
    'help' => null,
])

@php
$inputClasses = 'w-full px-4 py-2 border rounded-lg text-gray-900 placeholder-gray-400 transition-colors duration-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent';

if ($error) {
    $inputClasses .= ' border-red-500 focus:ring-red-500';
} else {
    $inputClasses .= ' border-gray-300';
}
@endphp

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif

    @if($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => $inputClasses . ' min-h-[100px] resize-y']) }}
        >{{ old($name, $value) }}</textarea>
    @else
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => $inputClasses]) }}
        />
    @endif

    @if($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif

    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
