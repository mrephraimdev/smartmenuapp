{{--
  Onboarding Wizard — affiché une seule fois quand un admin n'a aucun menu.
  Inclure dans le dashboard admin : @if($showOnboarding) @include('admin.onboarding') @endif
--}}
<div
    x-data="onboardingWizard()"
    x-init="init()"
    x-show="visible"
    x-cloak
    class="fixed inset-0 z-[9998] flex items-center justify-center p-4"
    style="background: rgba(15,23,42,0.75); backdrop-filter: blur(4px);"
>
    <div
        x-show="visible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
        @click.stop
    >
        {{-- Header avec progression --}}
        <div class="bg-slate-900 px-7 pt-7 pb-5">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-amber-400 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-900" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white text-sm">SmartMenu</span>
                </div>
                <button @click="skip()" class="text-slate-500 hover:text-slate-300 transition-colors text-xs underline underline-offset-2">
                    Passer la configuration
                </button>
            </div>

            {{-- Step indicators --}}
            <div class="flex items-center gap-2 mb-4">
                <template x-for="i in 3" :key="i">
                    <div class="flex items-center gap-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition-all duration-300"
                             :class="step > i ? 'bg-amber-400 text-slate-900' : step === i ? 'bg-amber-400 text-slate-900 ring-4 ring-amber-400/30' : 'bg-slate-700 text-slate-500'">
                            <template x-if="step > i">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            </template>
                            <template x-if="step <= i">
                                <span x-text="i"></span>
                            </template>
                        </div>
                        <div x-show="i < 3" class="h-0.5 w-12 rounded transition-all duration-500"
                             :class="step > i ? 'bg-amber-400' : 'bg-slate-700'"></div>
                    </div>
                </template>
            </div>

            {{-- Progress bar --}}
            <div class="h-1 bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-amber-400 rounded-full transition-all duration-500 ease-out"
                     :style="`width: ${(step / 3) * 100}%`"></div>
            </div>
        </div>

        {{-- Steps content --}}
        <div class="px-7 py-6">

            {{-- ÉTAPE 1 : Créer un menu --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide">Étape 1 sur 3</p>
                        <h2 class="text-xl font-bold text-gray-900">Créez votre premier menu</h2>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-5 ml-13">Donnez un nom à votre carte — vous pourrez y ajouter des plats ensuite.</p>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom du menu <span class="text-red-500">*</span></label>
                        <input
                            type="text"
                            x-model="menu.title"
                            @keydown.enter="createMenu()"
                            placeholder="Ex : Menu Principal, Carte du soir…"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm transition-all"
                            autofocus
                        >
                    </div>

                    <div x-show="error" class="flex items-center gap-2 text-sm text-red-600 bg-red-50 px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                        <span x-text="error"></span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button @click="skip()" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Passer</button>
                    <button
                        @click="createMenu()"
                        :disabled="loading || !menu.title.trim()"
                        class="flex items-center gap-2 bg-amber-400 hover:bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed text-slate-900 font-semibold px-6 py-2.5 rounded-xl text-sm transition-all"
                    >
                        <template x-if="loading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <span>Créer le menu</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ÉTAPE 2 : Créer une table --}}
            <div x-show="step === 2" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0 1 12 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h.008v.008h-.008V8.25Zm-17.25 0h.008v.008H3.375V8.25Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-teal-600 uppercase tracking-wide">Étape 2 sur 3</p>
                        <h2 class="text-xl font-bold text-gray-900">Ajoutez une première table</h2>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mb-5">Chaque table aura un QR code unique que vos clients scannent pour commander.</p>

                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                            <input type="text" x-model="table.code" placeholder="Ex : T01, A1…"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm transition-all uppercase">
                            <p class="text-xs text-gray-400 mt-1">Code court unique</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nom affiché</label>
                            <input type="text" x-model="table.label" placeholder="Ex : Table 1"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-400 focus:border-transparent text-sm transition-all">
                            <p class="text-xs text-gray-400 mt-1">Nom lisible</p>
                        </div>
                    </div>

                    <div x-show="error" class="flex items-center gap-2 text-sm text-red-600 bg-red-50 px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                        <span x-text="error"></span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-6">
                    <button @click="skip()" class="text-sm text-gray-400 hover:text-gray-600 transition-colors">Passer cette étape</button>
                    <button
                        @click="createTable()"
                        :disabled="loading || !table.code.trim()"
                        class="flex items-center gap-2 bg-amber-400 hover:bg-amber-500 disabled:opacity-50 disabled:cursor-not-allowed text-slate-900 font-semibold px-6 py-2.5 rounded-xl text-sm transition-all"
                    >
                        <template x-if="loading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </template>
                        <span>Créer la table</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- ÉTAPE 3 : Succès --}}
            <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="text-center py-4">
                    {{-- Animated checkmark --}}
                    <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5 relative">
                        <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        <div class="absolute inset-0 rounded-full bg-emerald-200 animate-ping opacity-30"></div>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Votre restaurant est prêt ! 🎉</h2>
                    <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto">
                        Votre QR code est généré. Vos clients peuvent maintenant scanner et commander directement depuis leur téléphone.
                    </p>

                    {{-- Summary cards --}}
                    <div class="grid grid-cols-2 gap-3 mb-6 text-left">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3">
                            <p class="text-xs text-indigo-500 font-semibold mb-0.5">Menu créé</p>
                            <p class="text-sm font-bold text-indigo-900 truncate" x-text="menu.title || '—'"></p>
                        </div>
                        <div class="bg-teal-50 border border-teal-100 rounded-xl px-4 py-3">
                            <p class="text-xs text-teal-500 font-semibold mb-0.5">Table créée</p>
                            <p class="text-sm font-bold text-teal-900 truncate" x-text="table.label || table.code || '—'"></p>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <a :href="menuUrl"
                           target="_blank"
                           class="flex items-center justify-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-5 py-3 rounded-xl text-sm transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Voir mon menu en ligne
                        </a>
                        <button @click="finish()"
                                class="flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-5 py-3 rounded-xl text-sm transition-all">
                            Aller sur mon tableau de bord
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function onboardingWizard() {
    return {
        visible: true,
        step: 1,
        loading: false,
        error: '',
        tenantSlug: '{{ $tenant->slug ?? "" }}',
        tenantId: {{ $tenant->id ?? 'null' }},
        createdMenuId: null,
        menuUrl: '#',

        menu: { title: '' },
        table: { code: 'T01', label: 'Table 1' },

        init() {
            // Auto-fill table label when code changes
            this.$watch('table.code', val => {
                if (!this.table.label || this.table.label.startsWith('Table')) {
                    this.table.label = 'Table ' + val;
                }
            });
        },

        async createMenu() {
            if (!this.menu.title.trim()) return;
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch(`/admin/${this.tenantSlug}/menus`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ title: this.menu.title, active: true })
                });
                const data = await res.json();
                if (res.ok && data.id) {
                    this.createdMenuId = data.id;
                    this.step = 2;
                } else {
                    this.error = data.message || 'Erreur lors de la création du menu.';
                }
            } catch (e) {
                this.error = 'Erreur réseau. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        },

        async createTable() {
            if (!this.table.code.trim()) return;
            this.loading = true;
            this.error = '';
            try {
                const res = await fetch(`/admin/${this.tenantSlug}/tables`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        code: this.table.code.toUpperCase(),
                        label: this.table.label || this.table.code,
                        capacity: 4
                    })
                });
                const data = await res.json();
                if (res.ok) {
                    this.menuUrl = `/menu/${this.tenantId}/${this.table.code.toUpperCase()}`;
                    this.step = 3;
                } else {
                    this.error = data.message || 'Erreur lors de la création de la table.';
                }
            } catch (e) {
                this.error = 'Erreur réseau. Veuillez réessayer.';
            } finally {
                this.loading = false;
            }
        },

        skip() {
            this.dismiss();
        },

        finish() {
            this.dismiss();
        },

        async dismiss() {
            // Mark onboarding as done in session
            await fetch('/admin/onboarding/dismiss', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            }).catch(() => {});
            this.visible = false;
        }
    };
}
</script>
