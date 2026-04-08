@props([
    'icon' => 'inbox',
    'title' => 'Aucune donnée',
    'description' => 'Il n\'y a rien à afficher pour le moment.',
])

@php
// Heroicon mapping
$heroicons = [
    'inbox' => 'heroicon-o-inbox',
    'table' => 'heroicon-o-table-cells',
    'chart-bar' => 'heroicon-o-chart-bar',
    'users' => 'heroicon-o-users',
    'document' => 'heroicon-o-document',
    'folder' => 'heroicon-o-folder',
    'shopping-cart' => 'heroicon-o-shopping-cart',
    'bell' => 'heroicon-o-bell',
    'search' => 'heroicon-o-magnifying-glass',
    'photo' => 'heroicon-o-photo',
    'cake' => 'heroicon-o-cake',
];

$iconName = $heroicons[$icon] ?? 'heroicon-o-' . $icon;
@endphp

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4']) }}>
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4">
        @svg($iconName, 'w-8 h-8 text-gray-400')
    </div>

    <h3 class="text-lg font-medium text-gray-900 mb-2">
        {{ $title }}
    </h3>

    <p class="text-gray-500 max-w-md mx-auto mb-6">
        {{ $description }}
    </p>

    @isset($action)
        <div>
            {{ $action }}
        </div>
    @endisset

    {{ $slot }}
</div>
