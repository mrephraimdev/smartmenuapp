@props([
    'type' => 'text',
    'lines' => 3,
])

@php
$baseClasses = 'animate-pulse bg-gray-200 rounded';
@endphp

@if($type === 'text')
    <div class="space-y-2">
        @for($i = 0; $i < $lines; $i++)
            <div class="{{ $baseClasses }} h-4 {{ $i === $lines - 1 ? 'w-3/4' : 'w-full' }}"></div>
        @endfor
    </div>
@elseif($type === 'title')
    <div class="{{ $baseClasses }} h-6 w-3/4 mb-4"></div>
@elseif($type === 'avatar')
    <div class="{{ $baseClasses }} rounded-full w-12 h-12"></div>
@elseif($type === 'image')
    <div class="{{ $baseClasses }} w-full h-48"></div>
@elseif($type === 'card')
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="{{ $baseClasses }} h-6 w-3/4 mb-4"></div>
        <div class="space-y-2">
            <div class="{{ $baseClasses }} h-4 w-full"></div>
            <div class="{{ $baseClasses }} h-4 w-full"></div>
            <div class="{{ $baseClasses }} h-4 w-2/3"></div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => $baseClasses]) }}>
        {{ $slot }}
    </div>
@endif
