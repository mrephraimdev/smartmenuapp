<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KDS Cuisine — {{ $tenantSlug }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'DM Sans', sans-serif; }
        .order-enter { animation: orderIn .25s ease-out; }
        @keyframes orderIn { from { opacity:0; transform:translateY(8px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
        .dot-pulse { animation: dotPulse 2s ease-in-out infinite; }
        @keyframes dotPulse { 0%,100% { opacity:1; } 50% { opacity:.4; } }
        .col-scroll::-webkit-scrollbar { width: 4px; }
        .col-scroll::-webkit-scrollbar-track { background: transparent; }
        .col-scroll::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-950 min-h-screen text-white">

<div x-data="kds('{{ $tenantSlug }}', {{ $tenantId ?? 0 }})" x-init="init()" x-cloak class="flex flex-col h-screen">

    {{-- ── HEADER ──────────────────────────────────────────────────────── --}}
    <header class="bg-slate-900 border-b border-slate-800 px-4 py-3 flex items-center justify-between flex-shrink-0 shadow-lg">

        {{-- Logo + titre --}}
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-400 flex items-center justify-center shadow-lg shadow-amber-400/30 flex-shrink-0">
                <svg class="w-5 h-5 text-slate-900" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z"/>
                </svg>
            </div>
            <div>
                <h1 class="font-bold text-base text-white leading-tight">KDS Cuisine</h1>
                <p class="text-xs text-slate-400">{{ $tenantSlug }}</p>
            </div>
        </div>

        {{-- Stats rapides --}}
        <div class="hidden md:flex items-center gap-2">
            <div class="flex items-center gap-1.5 bg-yellow-500/10 border border-yellow-500/20 rounded-xl px-3 py-1.5">
                <span class="w-2 h-2 bg-yellow-400 rounded-full dot-pulse flex-shrink-0"></span>
                <span class="text-xs font-semibold text-yellow-300" x-text="countByStatus('RECU')"></span>
                <span class="text-xs text-yellow-400/70">en attente</span>
            </div>
            <div class="flex items-center gap-1.5 bg-blue-500/10 border border-blue-500/20 rounded-xl px-3 py-1.5">
                <span class="w-2 h-2 bg-blue-400 rounded-full dot-pulse flex-shrink-0"></span>
                <span class="text-xs font-semibold text-blue-300" x-text="countByStatus('PREP')"></span>
                <span class="text-xs text-blue-400/70">en prépa</span>
            </div>
            <div class="flex items-center gap-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-3 py-1.5">
                <span class="w-2 h-2 bg-emerald-400 rounded-full flex-shrink-0"></span>
                <span class="text-xs font-semibold text-emerald-300" x-text="countByStatus('PRET')"></span>
                <span class="text-xs text-emerald-400/70">prêts</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasRole('SERVEUR') || auth()->user()->hasRole('ADMIN'))
            <a href="{{ route('serveur.commande.index', $tenantSlug) }}"
               class="flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-3 py-2 rounded-xl transition-colors shadow-md shadow-amber-500/20">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span class="hidden sm:inline">Prendre commande</span>
            </a>
            @endif

            <button @click="refresh()"
                    class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-medium px-3 py-2 rounded-xl border border-slate-700 transition-all">
                <svg class="w-3.5 h-3.5" :class="loading && 'animate-spin'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                <span class="hidden sm:inline">Actualiser</span>
            </button>

            <button @click="autoRefresh = !autoRefresh"
                    :class="autoRefresh ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 'bg-slate-800 border-slate-700 text-slate-500'"
                    class="flex items-center gap-1.5 text-xs font-medium px-3 py-2 rounded-xl border transition-all">
                <span class="w-2 h-2 rounded-full flex-shrink-0"
                      :class="autoRefresh ? 'bg-emerald-400 dot-pulse' : 'bg-slate-600'"></span>
                <span x-text="autoRefresh ? 'Auto ON' : 'Auto OFF'"></span>
            </button>
        </div>
    </header>

    {{-- ── KANBAN ───────────────────────────────────────────────────────── --}}
    <div class="flex-1 overflow-hidden p-3">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 h-full">

            {{-- ─ REÇU ─ --}}
            <div class="flex flex-col bg-slate-900/60 rounded-2xl border border-yellow-500/20 overflow-hidden">
                <div class="px-3 py-2.5 border-b border-yellow-500/20 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-yellow-400 rounded-full dot-pulse"></span>
                        <span class="text-xs font-bold text-yellow-300 uppercase tracking-wider">Reçu</span>
                    </div>
                    <span class="text-xs font-bold bg-yellow-400/20 text-yellow-300 px-2 py-0.5 rounded-full"
                          x-text="countByStatus('RECU')"></span>
                </div>
                <div class="flex-1 overflow-y-auto col-scroll p-2 space-y-2">
                    <template x-for="order in filterByStatus('RECU')" :key="order.id">
                        <div class="order-enter bg-slate-800 rounded-xl border border-yellow-400/30 overflow-hidden">
                            <div class="border-l-4 border-yellow-400 p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <p class="font-bold text-sm text-white" x-text="'Table ' + (order.table?.label || order.table?.code || '?')"></p>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="order.order_number + ' · ' + formatTime(order.created_at)"></p>
                                        <p x-show="order.serveur" class="text-xs text-yellow-400/70 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                            <span x-text="order.serveur?.name"></span>
                                        </p>
                                    </div>
                                    <span class="text-xs font-bold text-yellow-300 bg-yellow-400/10 px-2 py-0.5 rounded-lg flex-shrink-0"
                                          x-text="formatPrice(order.total) + ' F'"></span>
                                </div>
                                <div class="bg-black/20 rounded-lg p-2 mb-2.5 space-y-1">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="w-5 h-5 bg-yellow-400/20 text-yellow-300 rounded-full flex items-center justify-center font-bold flex-shrink-0"
                                                  x-text="item.quantity"></span>
                                            <span class="text-slate-200 font-medium" x-text="item.dish?.name || 'Plat'"></span>
                                        </div>
                                    </template>
                                    <template x-if="order.notes">
                                        <p class="text-[11px] text-slate-400 italic border-t border-slate-700 pt-1 mt-1" x-text="'Note : ' + order.notes"></p>
                                    </template>
                                </div>
                                <button @click="updateStatus(order.id, 'PREP')"
                                        :disabled="updating"
                                        class="w-full bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                                    Commencer la préparation
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="filterByStatus('RECU').length === 0"
                         class="flex flex-col items-center justify-center py-8 text-slate-600">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <p class="text-xs">Aucune commande</p>
                    </div>
                </div>
            </div>

            {{-- ─ PRÉPARATION ─ --}}
            <div class="flex flex-col bg-slate-900/60 rounded-2xl border border-blue-500/20 overflow-hidden">
                <div class="px-3 py-2.5 border-b border-blue-500/20 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-blue-400 rounded-full dot-pulse"></span>
                        <span class="text-xs font-bold text-blue-300 uppercase tracking-wider">Préparation</span>
                    </div>
                    <span class="text-xs font-bold bg-blue-400/20 text-blue-300 px-2 py-0.5 rounded-full"
                          x-text="countByStatus('PREP')"></span>
                </div>
                <div class="flex-1 overflow-y-auto col-scroll p-2 space-y-2">
                    <template x-for="order in filterByStatus('PREP')" :key="order.id">
                        <div class="order-enter bg-slate-800 rounded-xl border border-blue-400/30 overflow-hidden">
                            <div class="border-l-4 border-blue-400 p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <p class="font-bold text-sm text-white" x-text="'Table ' + (order.table?.label || order.table?.code || '?')"></p>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="order.order_number + ' · ' + formatTime(order.created_at)"></p>
                                        <p x-show="order.serveur" class="text-xs text-blue-400/70 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                            <span x-text="order.serveur?.name"></span>
                                        </p>
                                    </div>
                                    <span class="text-xs font-bold text-blue-300 bg-blue-400/10 px-2 py-0.5 rounded-lg flex-shrink-0"
                                          x-text="formatPrice(order.total) + ' F'"></span>
                                </div>
                                <div class="bg-black/20 rounded-lg p-2 mb-2.5 space-y-1">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="w-5 h-5 bg-blue-400/20 text-blue-300 rounded-full flex items-center justify-center font-bold flex-shrink-0"
                                                  x-text="item.quantity"></span>
                                            <span class="text-slate-200 font-medium" x-text="item.dish?.name || 'Plat'"></span>
                                        </div>
                                    </template>
                                    <template x-if="order.notes">
                                        <p class="text-[11px] text-slate-400 italic border-t border-slate-700 pt-1 mt-1" x-text="'Note : ' + order.notes"></p>
                                    </template>
                                </div>
                                <button @click="updateStatus(order.id, 'PRET')"
                                        :disabled="updating"
                                        class="w-full bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white text-xs font-bold py-2 rounded-lg transition-colors">
                                    Marquer comme prêt
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="filterByStatus('PREP').length === 0"
                         class="flex flex-col items-center justify-center py-8 text-slate-600">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <p class="text-xs">Aucune commande</p>
                    </div>
                </div>
            </div>

            {{-- ─ PRÊT ─ --}}
            <div class="flex flex-col bg-slate-900/60 rounded-2xl border border-emerald-500/40 overflow-hidden">
                <div class="px-3 py-2.5 border-b border-emerald-500/40 bg-emerald-500/10 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full dot-pulse"></span>
                        <span class="text-xs font-bold text-emerald-300 tracking-wide">Prêt à servir</span>
                    </div>
                    <span class="text-xs font-bold bg-emerald-500 text-white px-2 py-0.5 rounded-full"
                          x-text="countByStatus('PRET')"></span>
                </div>
                <div class="flex-1 overflow-y-auto col-scroll p-2 space-y-2">
                    <template x-for="order in filterByStatus('PRET')" :key="order.id">
                        <div class="order-enter bg-slate-800 rounded-xl border border-emerald-400/30 overflow-hidden">
                            <div class="border-l-4 border-emerald-400 p-3">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <p class="font-bold text-sm text-white" x-text="'Table ' + (order.table?.label || order.table?.code || '?')"></p>
                                        <p class="text-xs text-slate-400 mt-0.5" x-text="order.order_number + ' · ' + formatTime(order.created_at)"></p>
                                        <p x-show="order.serveur" class="text-xs text-emerald-400/70 mt-0.5 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                            <span x-text="order.serveur?.name"></span>
                                        </p>
                                    </div>
                                    <span class="text-xs font-bold text-emerald-300 bg-emerald-400/10 px-2 py-0.5 rounded-lg flex-shrink-0"
                                          x-text="formatPrice(order.total) + ' F'"></span>
                                </div>
                                <div class="bg-black/20 rounded-lg p-2 mb-2.5 space-y-1">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="w-5 h-5 bg-emerald-400/20 text-emerald-300 rounded-full flex items-center justify-center font-bold flex-shrink-0"
                                                  x-text="item.quantity"></span>
                                            <span class="text-slate-200 font-medium" x-text="item.dish?.name || 'Plat'"></span>
                                        </div>
                                    </template>
                                    <template x-if="order.notes">
                                        <p class="text-xs text-slate-400 italic border-t border-slate-700 pt-1 mt-1" x-text="'Note : ' + order.notes"></p>
                                    </template>
                                </div>
                                <button @click="updateStatus(order.id, 'SERVI')"
                                        :disabled="updating"
                                        class="w-full bg-emerald-500 hover:bg-emerald-400 disabled:opacity-50 text-white text-xs font-bold py-2.5 rounded-lg transition-colors flex items-center justify-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                    </svg>
                                    Confirmer le service
                                </button>
                            </div>
                        </div>
                    </template>
                    <div x-show="filterByStatus('PRET').length === 0"
                         class="flex flex-col items-center justify-center py-8 text-slate-600">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <p class="text-xs">Aucune commande</p>
                    </div>
                </div>
            </div>

            {{-- ─ SERVI ─ --}}
            <div class="flex flex-col bg-slate-900/40 rounded-2xl border border-slate-700/50 overflow-hidden">
                <div class="px-3 py-2.5 border-b border-slate-700/50 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-slate-500 rounded-full"></span>
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Servi</span>
                    </div>
                    <span class="text-xs font-bold bg-slate-700 text-slate-400 px-2 py-0.5 rounded-full"
                          x-text="countByStatus('SERVI')"></span>
                </div>
                <div class="flex-1 overflow-y-auto col-scroll p-2 space-y-2">
                    <template x-for="order in filterByStatus('SERVI')" :key="order.id">
                        <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 overflow-hidden opacity-50">
                            <div class="border-l-4 border-slate-600 p-3">
                                <div class="flex items-start justify-between mb-1.5">
                                    <div>
                                        <p class="font-bold text-sm text-slate-400" x-text="'Table ' + (order.table?.label || order.table?.code || '?')"></p>
                                        <p class="text-xs text-slate-600 mt-0.5" x-text="order.order_number + ' · ' + formatTime(order.created_at)"></p>
                                    </div>
                                    <span class="text-xs font-bold text-slate-500 flex-shrink-0"
                                          x-text="formatPrice(order.total) + ' F'"></span>
                                </div>
                                <div class="bg-black/10 rounded-lg p-2 space-y-1">
                                    <template x-for="item in order.items" :key="item.id">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="w-5 h-5 bg-slate-700 text-slate-400 rounded-full flex items-center justify-center font-bold flex-shrink-0"
                                                  x-text="item.quantity"></span>
                                            <span class="text-slate-500" x-text="item.dish?.name || 'Plat'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="filterByStatus('SERVI').length === 0"
                         class="flex flex-col items-center justify-center py-8 text-slate-700">
                        <svg class="w-8 h-8 mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <p class="text-xs">Aucun servi</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- ── SCRIPTS ──────────────────────────────────────────────────────────── --}}
<script>
class OrderAlarm {
    constructor() { this.ctx = null; this.comp = null; this.playing = false; }
    _init() {
        if (!this.ctx) {
            this.ctx  = new (window.AudioContext || window.webkitAudioContext)();
            this.comp = this.ctx.createDynamicsCompressor();
            this.comp.threshold.value = -6; this.comp.knee.value = 0;
            this.comp.ratio.value = 20; this.comp.attack.value = 0.001;
            this.comp.release.value = 0.05;
            this.comp.connect(this.ctx.destination);
        }
        if (this.ctx.state === 'suspended') this.ctx.resume();
    }
    _tone(freq, start, dur) {
        const osc = this.ctx.createOscillator(), gain = this.ctx.createGain();
        osc.type = 'square'; osc.frequency.value = freq;
        gain.gain.setValueAtTime(1.0, start);
        gain.gain.exponentialRampToValueAtTime(0.001, start + dur);
        osc.connect(gain); gain.connect(this.comp);
        osc.start(start); osc.stop(start + dur);
    }
    play(rings = 5) {
        this._init(); if (this.playing) return; this.playing = true;
        const now = this.ctx.currentTime;
        for (let r = 0; r < rings; r++) {
            const b = now + r * 0.75;
            this._tone(1400, b+0.00, 0.10); this._tone(700, b+0.15, 0.10);
            this._tone(1400, b+0.30, 0.10); this._tone(700, b+0.45, 0.10);
        }
        setTimeout(() => { this.playing = false; }, rings * 750 + 300);
    }
}

window._orderAlarm = new OrderAlarm();
document.addEventListener('click', () => window._orderAlarm._init(), { once: true });

if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

function _kdsNotif(newCount) {
    if (!('Notification' in window) || Notification.permission !== 'granted') return;
    const n = new Notification('🍳 Nouvelle commande cuisine !', {
        body: `${newCount} nouvelle(s) commande(s) à préparer`,
        icon: '/favicon.ico', tag: 'kds-order', requireInteraction: true,
    });
    n.onclick = () => { window.focus(); n.close(); };
}
</script>

<script>
function kds(tenantSlug, tenantId) {
    return {
        tenantSlug, tenantId,
        orders: [],
        loading: false,
        autoRefresh: true,
        interval: null,
        updating: false,
        pendingUpdates: {},
        knownOrderIds: new Set(),

        init() {
            this.refresh();
            this.startAutoRefresh();
        },

        async refresh() {
            if (!this.tenantSlug || this.updating) return;
            this.loading = true;
            try {
                const res = await fetch(`/api/orders/tenant/${this.tenantSlug}`, {
                    credentials: 'include',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    let newOrders = await res.json();
                    const seen = new Set();
                    newOrders = newOrders.filter(o => { if (seen.has(o.id)) return false; seen.add(o.id); return true; });

                    const newRecuOrders = newOrders.filter(o => o.status === 'RECU' && !this.knownOrderIds.has(o.id));
                    if (newRecuOrders.length > 0 && this.knownOrderIds.size > 0) this.playNotificationSound(newRecuOrders.length);

                    newOrders.forEach(o => this.knownOrderIds.add(o.id));
                    newOrders = newOrders.map(o => {
                        if (this.pendingUpdates[o.id]) o.status = this.pendingUpdates[o.id];
                        return o;
                    });
                    this.orders = newOrders;
                }
            } catch (e) { console.error('Erreur réseau KDS:', e); }
            this.loading = false;
        },

        playNotificationSound(newCount) {
            try { window._orderAlarm?.play(5); _kdsNotif(newCount || 1); } catch(e) {}
        },

        startAutoRefresh() {
            this.interval = setInterval(() => {
                if (this.autoRefresh && !this.updating) this.refresh();
            }, 15000);
        },

        filterByStatus(status) { return this.orders.filter(o => o.status === status); },
        countByStatus(status) { return this.filterByStatus(status).length; },

        async updateStatus(orderId, newStatus) {
            if (this.updating) return;
            const order = this.orders.find(o => o.id === orderId);
            if (!order) return;
            const oldStatus = order.status;
            this.updating = true;
            this.pendingUpdates[orderId] = newStatus;
            order.status = newStatus;
            try {
                const res = await fetch(`/api/orders/${orderId}/status`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ status: newStatus })
                });
                const data = await res.json();
                if (!res.ok || !data.success) {
                    order.status = oldStatus;
                    delete this.pendingUpdates[orderId];
                } else {
                    setTimeout(() => { delete this.pendingUpdates[orderId]; }, 2000);
                }
            } catch (e) {
                order.status = oldStatus;
                delete this.pendingUpdates[orderId];
            } finally {
                this.updating = false;
            }
        },

        formatTime(ts) {
            if (!ts) return '';
            return new Date(ts).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        },

        formatPrice(n) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(n || 0));
        }
    };
}
</script>
</body>
</html>
