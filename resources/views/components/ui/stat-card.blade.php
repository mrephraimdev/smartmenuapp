@props([
    'title' => '',
    'label' => null, // Alias pour title
    'value' => '',
    'icon' => null,
    'color' => 'indigo',
    'variant' => null, // Alias pour color
    'trend' => null,
    'trendValue' => null,
])

@php
$colorName = $variant ?? $color;

$colors = [
    'indigo' => ['text' => 'text-indigo-600', 'bg' => 'bg-indigo-100', 'icon' => 'text-indigo-500'],
    'green' => ['text' => 'text-green-600', 'bg' => 'bg-green-100', 'icon' => 'text-green-500'],
    'red' => ['text' => 'text-red-600', 'bg' => 'bg-red-100', 'icon' => 'text-red-500'],
    'amber' => ['text' => 'text-amber-600', 'bg' => 'bg-amber-100', 'icon' => 'text-amber-500'],
    'purple' => ['text' => 'text-purple-600', 'bg' => 'bg-purple-100', 'icon' => 'text-purple-500'],
    'blue' => ['text' => 'text-blue-600', 'bg' => 'bg-blue-100', 'icon' => 'text-blue-500'],
    'orange' => ['text' => 'text-orange-600', 'bg' => 'bg-orange-100', 'icon' => 'text-orange-500'],
];

$colorSet = $colors[$colorName] ?? $colors['indigo'];
$displayTitle = $label ?? $title;

// Heroicon mapping
$heroicons = [
    'shopping-cart' => 'heroicon-o-shopping-cart',
    'banknotes' => 'heroicon-o-banknotes',
    'fire' => 'heroicon-o-fire',
    'chart-bar' => 'heroicon-o-chart-bar',
    'users' => 'heroicon-o-users',
    'table-cells' => 'heroicon-o-table-cells',
    'clock' => 'heroicon-o-clock',
    'check-circle' => 'heroicon-o-check-circle',
    'cube' => 'heroicon-o-cube',
];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl shadow-md p-6 transition-all duration-300 hover:shadow-lg']) }}>
    <div class="flex items-center justify-between">
        <div class="flex-1">
            @if($displayTitle)
                <div class="text-sm font-medium text-gray-500 mb-1">{{ $displayTitle }}</div>
            @endif

            @if($slot->isNotEmpty())
                {{ $slot }}
            @else
                <div class="text-3xl font-bold {{ $colorSet['text'] }}">
                    {{ $value }}
                </div>
            @endif

            @if($trend && $trendValue)
                <div class="mt-2 flex items-center text-sm">
                    @if($trend === 'up')
                        <x-heroicon-s-arrow-up class="w-4 h-4 text-green-500 mr-1" />
                        <span class="text-green-600 font-medium">{{ $trendValue }}</span>
                    @else
                        <x-heroicon-s-arrow-down class="w-4 h-4 text-red-500 mr-1" />
                        <span class="text-red-600 font-medium">{{ $trendValue }}</span>
                    @endif
                </div>
            @endif
        </div>

        @if($icon)
            <div class="flex-shrink-0 ml-4">
                <div class="w-14 h-14 rounded-xl {{ $colorSet['bg'] }} flex items-center justify-center">
                    @if(isset($heroicons[$icon]))
                        @svg($heroicons[$icon], 'w-7 h-7 ' . $colorSet['icon'])
                    @else
                        @svg('heroicon-o-' . $icon, 'w-7 h-7 ' . $colorSet['icon'])
                    @endif
                </div>
            </div>
        @endif
    </div>

    @isset($footer)
        <div class="mt-4 pt-4 border-t border-gray-100">
            {{ $footer }}
        </div>
    @endisset
</div>
