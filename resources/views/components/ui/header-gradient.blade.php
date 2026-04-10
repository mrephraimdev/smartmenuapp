@props([
    'variant' => 'primary',
    'title' => '',
    'subtitle' => null,
])

@php
$variants = [
    'primary' => 'bg-gradient-to-r from-indigo-600 to-purple-600',
    'secondary' => 'bg-gradient-to-r from-indigo-600 to-cyan-600',
    'kds' => 'bg-gradient-to-r from-orange-500 to-red-500',
    'success' => 'bg-gradient-to-r from-green-500 to-emerald-600',
    'purple' => 'bg-gradient-to-r from-purple-500 to-purple-600',
];

$gradientClass = $variants[$variant] ?? $variants['primary'];
@endphp

<div {{ $attributes->merge(['class' => $gradientClass . ' text-white']) }}>
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                @if($title)
                    <h1 class="text-3xl md:text-4xl font-bold mb-2">
                        {{ $title }}
                    </h1>
                @endif

                @if($subtitle)
                    <p class="text-white/90 text-lg">
                        {{ $subtitle }}
                    </p>
                @endif

                {{ $slot }}
            </div>

            @isset($actions)
                <div class="ml-4">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
</div>
