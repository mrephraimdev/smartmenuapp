@props([
    'name',
    'show' => false,
    'maxWidth' => 'lg',
    'closeable' => true,
])

@php
$maxWidthClass = [
    'sm' => 'max-w-sm',
    'md' => 'max-w-md',
    'lg' => 'max-w-lg',
    'xl' => 'max-w-xl',
    '2xl' => 'max-w-2xl',
][$maxWidth] ?? 'max-w-lg';
@endphp

<div
    x-data="{ show: @js($show) }"
    x-on:open-modal.window="$event.detail === '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail === '{{ $name }}' ? show = false : null"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <!-- Overlay -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black bg-opacity-50"
        @if($closeable)
            @click="show = false"
        @endif
    ></div>

    <!-- Modal Container -->
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <!-- Modal Content -->
        <div
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-xl shadow-2xl {{ $maxWidthClass }} w-full max-h-[90vh] overflow-hidden"
            @click.stop
        >
            <!-- Header -->
            @if(isset($header) || isset($title))
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div class="flex-1">
                        @isset($header)
                            {{ $header }}
                        @else
                            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
                        @endisset
                    </div>
                    @if($closeable)
                        <button
                            @click="show = false"
                            class="text-gray-400 hover:text-gray-600 transition-colors"
                            aria-label="Fermer"
                        >
                            @svg('heroicon-o-x-mark', 'w-6 h-6')
                        </button>
                    @endif
                </div>
            @endif

            <!-- Body -->
            <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-140px)]">
                {{ $slot }}
            </div>

            <!-- Footer -->
            @isset($footer)
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-end space-x-3">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
