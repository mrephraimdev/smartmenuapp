{{--
  Toast Notification System
  Usage:
    - Via Laravel flash: session()->flash('success', 'Message ici')
    - Via JS: window.toast.success('Message') / .error() / .warning() / .info()
--}}
<div
    x-data="toastSystem()"
    x-init="init()"
    class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none"
    aria-live="polite"
    aria-label="Notifications"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-8 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-8 scale-95"
            class="pointer-events-auto w-80 rounded-xl shadow-2xl overflow-hidden bg-white border border-gray-100"
            :class="{
                'border-l-4 border-l-emerald-500': toast.type === 'success',
                'border-l-4 border-l-red-500': toast.type === 'error',
                'border-l-4 border-l-amber-400': toast.type === 'warning',
                'border-l-4 border-l-blue-500': toast.type === 'info',
            }"
            role="alert"
        >
            <div class="flex items-start p-4 gap-3">
                {{-- Icon --}}
                <div class="flex-shrink-0 mt-0.5">
                    {{-- Success --}}
                    <template x-if="toast.type === 'success'">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    </template>
                    {{-- Error --}}
                    <template x-if="toast.type === 'error'">
                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </template>
                    {{-- Warning --}}
                    <template x-if="toast.type === 'warning'">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                    </template>
                    {{-- Info --}}
                    <template x-if="toast.type === 'info'">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Message --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800" x-text="toast.title" x-show="toast.title"></p>
                    <p class="text-sm text-gray-600 mt-0.5" x-text="toast.message"></p>
                </div>

                {{-- Close button --}}
                <button @click="dismiss(toast.id)" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Progress bar --}}
            <div class="h-0.5 w-full bg-gray-100">
                <div
                    class="h-full transition-all ease-linear"
                    :class="{
                        'bg-emerald-500': toast.type === 'success',
                        'bg-red-500': toast.type === 'error',
                        'bg-amber-400': toast.type === 'warning',
                        'bg-blue-500': toast.type === 'info',
                    }"
                    :style="`width: ${toast.progress}%; transition-duration: ${toast.duration}ms`"
                ></div>
            </div>
        </div>
    </template>
</div>

<script>
function toastSystem() {
    return {
        toasts: [],
        counter: 0,

        init() {
            // Expose global API
            window.toast = {
                success: (message, title = '') => this.add('success', message, title),
                error:   (message, title = '') => this.add('error', message, title),
                warning: (message, title = '') => this.add('warning', message, title),
                info:    (message, title = '') => this.add('info', message, title),
            };

            // Fire Laravel flash messages
            @if(session('success'))
                this.$nextTick(() => this.add('success', @json(session('success'))));
            @endif
            @if(session('error'))
                this.$nextTick(() => this.add('error', @json(session('error'))));
            @endif
            @if(session('warning'))
                this.$nextTick(() => this.add('warning', @json(session('warning'))));
            @endif
            @if(session('info'))
                this.$nextTick(() => this.add('info', @json(session('info'))));
            @endif
            @if($errors->any())
                this.$nextTick(() => this.add('error', @json($errors->first())));
            @endif
        },

        add(type, message, title = '') {
            const duration = type === 'error' ? 6000 : 4000;
            const id = ++this.counter;

            const toast = { id, type, message, title, visible: true, progress: 100, duration };
            this.toasts.push(toast);

            // Animate progress bar
            this.$nextTick(() => {
                setTimeout(() => {
                    const t = this.toasts.find(t => t.id === id);
                    if (t) t.progress = 0;
                }, 50);
            });

            // Auto-dismiss
            setTimeout(() => this.dismiss(id), duration + 100);
        },

        dismiss(id) {
            const toast = this.toasts.find(t => t.id === id);
            if (toast) {
                toast.visible = false;
                setTimeout(() => {
                    this.toasts = this.toasts.filter(t => t.id !== id);
                }, 300);
            }
        }
    };
}
</script>
