@props([
    'title' => null,
    'subtitle' => null,
    'hover' => false,
    'gradient' => false,
    'padding' => '6',
    'noPadding' => false,
])

@php
$baseClasses = 'bg-white rounded-xl shadow-md transition-all duration-300';

if ($hover) {
    $baseClasses .= ' hover:shadow-xl hover:-translate-y-1 cursor-pointer';
}

if ($gradient) {
    $baseClasses = 'rounded-xl shadow-md transition-all duration-300 bg-gradient-to-br from-indigo-500 to-purple-600 text-white';
}

$paddingClass = $noPadding ? '' : "p-{$padding}";
@endphp

<div {{ $attributes->merge(['class' => $baseClasses]) }}>
    @if(isset($header))
        <div class="px-6 py-4 border-b border-gray-100">
            {{ $header }}
        </div>
    @elseif($title || $subtitle)
        <div class="px-6 py-4 border-b border-gray-100">
            @if($title)
                <h3 class="text-xl font-bold {{ $gradient ? 'text-white' : 'text-gray-900' }}">
                    {{ $title }}
                </h3>
            @endif
            @if($subtitle)
                <p class="mt-1 text-sm {{ $gradient ? 'text-white/80' : 'text-gray-500' }}">
                    {{ $subtitle }}
                </p>
            @endif
        </div>
    @endif

    <div class="{{ $paddingClass }}">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-xl">
            {{ $footer }}
        </div>
    @endisset
</div>
