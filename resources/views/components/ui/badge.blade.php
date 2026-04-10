@props([
    'variant' => 'gray',
    'size' => 'md',
    'icon' => null,
])

@php
$baseClasses = 'inline-flex items-center rounded-full font-medium';

$variants = [
    'success' => 'bg-green-100 text-green-800',
    'warning' => 'bg-amber-100 text-amber-800',
    'danger' => 'bg-red-100 text-red-800',
    'info' => 'bg-blue-100 text-blue-800',
    'gray' => 'bg-gray-100 text-gray-800',
    'purple' => 'bg-purple-100 text-purple-800',
    'indigo' => 'bg-indigo-100 text-indigo-800',
];

$sizes = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-3 py-1 text-xs',
    'lg' => 'px-4 py-1.5 text-sm',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['gray']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <i class="{{ $icon }} mr-1"></i>
    @endif
    {{ $slot }}
</span>
