{{--
  Help Tooltip Component
  Usage: <x-ui.help-tooltip text="Explication du terme ici" />
  Optional: <x-ui.help-tooltip text="..." position="left" />
--}}
@props(['text', 'position' => 'top'])

<span
    x-data="{ open: false }"
    class="relative inline-flex items-center"
    @mouseenter="open = true"
    @mouseleave="open = false"
    @focus="open = true"
    @blur="open = false"
>
    {{-- The ? button --}}
    <button
        type="button"
        class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-gray-200 hover:bg-indigo-100 text-gray-500 hover:text-indigo-600 transition-colors cursor-help focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1 ml-1"
        tabindex="0"
        aria-label="Aide"
        :aria-expanded="open"
    >
        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a.75.75 0 0 0 0 1.5h.253a.25.25 0 0 1 .244.304l-.459 2.066A1.75 1.75 0 0 0 10.747 15H11a.75.75 0 0 0 0-1.5h-.253a.25.25 0 0 1-.244-.304l.459-2.066A1.75 1.75 0 0 0 9.253 9H9Z" clip-rule="evenodd" />
        </svg>
    </button>

    {{-- Tooltip popover --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 w-56 pointer-events-none"
        :class="{
            'bottom-full left-1/2 -translate-x-1/2 mb-2': '{{ $position }}' === 'top',
            'top-full left-1/2 -translate-x-1/2 mt-2': '{{ $position }}' === 'bottom',
            'right-full top-1/2 -translate-y-1/2 mr-2': '{{ $position }}' === 'left',
            'left-full top-1/2 -translate-y-1/2 ml-2': '{{ $position }}' === 'right',
        }"
        role="tooltip"
    >
        <div class="bg-gray-900 text-white text-xs rounded-lg px-3 py-2 shadow-xl leading-relaxed">
            {{ $text }}
            {{-- Arrow --}}
            @if($position === 'top')
                <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
            @elseif($position === 'bottom')
                <div class="absolute bottom-full left-1/2 -translate-x-1/2 border-4 border-transparent border-b-gray-900"></div>
            @elseif($position === 'left')
                <div class="absolute left-full top-1/2 -translate-y-1/2 border-4 border-transparent border-l-gray-900"></div>
            @elseif($position === 'right')
                <div class="absolute right-full top-1/2 -translate-y-1/2 border-4 border-transparent border-r-gray-900"></div>
            @endif
        </div>
    </div>
</span>
