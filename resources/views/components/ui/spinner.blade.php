@props([
    'size' => 'md',
])

@php
$sizes = [
    'sm' => 'w-4 h-4 border-2',
    'md' => 'w-8 h-8 border-4',
    'lg' => 'w-12 h-12 border-4',
    'xl' => 'w-16 h-16 border-4',
];

$sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div {{ $attributes->merge(['class' => "animate-spin rounded-full border-gray-200 border-t-indigo-600 {$sizeClass}"]) }} role="status">
    <span class="sr-only">Chargement...</span>
</div>
