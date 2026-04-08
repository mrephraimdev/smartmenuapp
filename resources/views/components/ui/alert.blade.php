@props([
    'variant' => 'info',
    'dismissible' => false,
    'icon' => null,
])

@php
$variants = [
    'success' => [
        'bg' => 'bg-green-50',
        'border' => 'border-green-200',
        'text' => 'text-green-800',
        'iconColor' => 'text-green-400',
        'heroicon' => 'heroicon-o-check-circle',
    ],
    'warning' => [
        'bg' => 'bg-amber-50',
        'border' => 'border-amber-200',
        'text' => 'text-amber-800',
        'iconColor' => 'text-amber-400',
        'heroicon' => 'heroicon-o-exclamation-triangle',
    ],
    'danger' => [
        'bg' => 'bg-red-50',
        'border' => 'border-red-200',
        'text' => 'text-red-800',
        'iconColor' => 'text-red-400',
        'heroicon' => 'heroicon-o-exclamation-circle',
    ],
    'info' => [
        'bg' => 'bg-blue-50',
        'border' => 'border-blue-200',
        'text' => 'text-blue-800',
        'iconColor' => 'text-blue-400',
        'heroicon' => 'heroicon-o-information-circle',
    ],
];

$config = $variants[$variant] ?? $variants['info'];
@endphp

<div
    {{ $attributes->merge(['class' => "rounded-lg border p-4 {$config['bg']} {$config['border']}"]) }}
    @if($dismissible)
        x-data="{ show: true }"
        x-show="show"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    @endif
>
    <div class="flex">
        <div class="flex-shrink-0">
            @svg($icon ?? $config['heroicon'], 'w-5 h-5 ' . $config['iconColor'])
        </div>

        <div class="ml-3 flex-1 {{ $config['text'] }}">
            {{ $slot }}
        </div>

        @if($dismissible)
            <div class="ml-auto pl-3">
                <button
                    @click="show = false"
                    class="{{ $config['text'] }} hover:opacity-75 transition-opacity"
                    aria-label="Fermer"
                >
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                </button>
            </div>
        @endif
    </div>
</div>
