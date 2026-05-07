<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Serveur — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: #0b0f1a; color: #f9fafb; font-family: 'DM Sans', sans-serif; }

        :root {
            --amber: #f59e0b; --amber-l: #fcd34d; --amber-dim: rgba(245,158,11,0.13);
            --green: #22c55e; --green-dim: rgba(34,197,94,0.12);
            --red: #ef4444; --red-dim: rgba(239,68,68,0.12);
            --s1: #0d1120; --s2: #111827; --s3: #1f2937; --s4: #374151;
            --bd: rgba(255,255,255,0.07); --txt: #f9fafb; --mut: #9ca3af;
        }

        .topbar {
            background: var(--s1);
            border-bottom: 1px solid var(--bd);
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .topbar h1 { font-size: 17px; font-weight: 700; }
        .topbar .badge {
            background: var(--red);
            color: #fff;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            min-width: 22px;
            text-align: center;
        }
        .topbar .sync { font-size: 12px; color: var(--mut); }

        .page { padding: 20px; max-width: 1200px; margin: 0 auto; }
        .section-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--mut);
            margin-bottom: 14px;
        }

        /* Table grid */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-bottom: 36px;
        }
        .tcard {
            background: var(--s2);
            border: 1px solid var(--bd);
            border-radius: 16px;
            padding: 18px 16px;
            transition: border-color .2s;
        }
        .tcard.active {
            border-color: rgba(34,197,94,.4);
            background: linear-gradient(135deg, rgba(34,197,94,.06) 0%, var(--s2) 60%);
        }
        .tcard-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .tcard-label { font-weight: 700; font-size: 15px; }
        .tcard-status {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 3px 8px;
            border-radius: 999px;
        }
        .status-active   { background: var(--green-dim); color: var(--green); }
        .status-inactive { background: rgba(255,255,255,.06); color: var(--mut); }
        .tcard-code  { font-size: 12px; color: var(--mut); margin-bottom: 10px; }
        .tcard-info  { font-size: 12px; color: var(--mut); min-height: 30px; margin-bottom: 14px; }
        .tcard-timer { font-size: 11px; color: var(--amber); font-variant-numeric: tabular-nums; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: opacity .15s, transform .1s;
            width: 100%;
        }
        .btn:hover  { opacity: .85; }
        .btn:active { transform: scale(.97); }
        .btn-open     { background: var(--green-dim); color: var(--green); border: 1px solid rgba(34,197,94,.25); }
        .btn-close    { background: var(--red-dim);   color: var(--red);   border: 1px solid rgba(239,68,68,.25); }
        .btn-validate { background: var(--green-dim); color: var(--green); border: 1px solid rgba(34,197,94,.25); }
        .btn-refuse   { background: var(--red-dim);   color: var(--red);   border: 1px solid rgba(239,68,68,.25); }

        /* Pending orders */
        .orders-list { display: flex; flex-direction: column; gap: 12px; }
        .ocard {
            background: var(--s2);
            border: 1px solid rgba(245,158,11,.25);
            border-radius: 16px;
            padding: 18px 20px;
        }
        .ocard-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 8px; }
        .ocard-num   { font-weight: 700; font-size: 15px; }
        .ocard-table { font-size: 12px; color: var(--amber); background: var(--amber-dim); padding: 3px 10px; border-radius: 999px; }
        .ocard-time  { font-size: 12px; color: var(--mut); }
        .ocard-items { margin-bottom: 14px; }
        .ocard-item  { display: flex; justify-content: space-between; font-size: 13px; padding: 4px 0; border-bottom: 1px solid var(--bd); }
        .ocard-item:last-child { border-bottom: none; }
        .ocard-total   { font-size: 14px; font-weight: 700; color: var(--amber); text-align: right; margin-bottom: 14px; }
        .ocard-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--mut);
            background: var(--s2);
            border: 1px dashed var(--bd);
            border-radius: 16px;
        }
        .empty-state .icon { font-size: 36px; margin-bottom: 12px; }
        .empty-state p { font-size: 14px; }

        .toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 100;
            background: var(--s3);
            border: 1px solid var(--bd);
            border-radius: 14px;
            padding: 14px 18px;
            font-size: 14px;
            max-width: 300px;
            box-shadow: 0 8px 32px rgba(0,0,0,.4);
        }
        .toast.success { border-color: rgba(34,197,94,.3); color: var(--green); }
        .toast.error   { border-color: rgba(239,68,68,.3); color: var(--red); }
    </style>
</head>
<body x-data="dashboard()" x-init="init()">

    <!-- Topbar -->
    <div class="topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <h1>{{ $tenant->name }}</h1>
            <span x-show="pendingCount > 0" class="badge" x-text="pendingCount + ' en attente'"></span>
        </div>
        <div style="display:flex;align-items:center;gap:16px">
            <span class="sync" x-text="lastSync ? 'Sync ' + lastSync : ''"></span>
            <a href="{{ route('serveur.commande.index', $tenant->slug) }}"
               style="font-size:13px;color:var(--amber);text-decoration:none;">
                Prise de commande →
            </a>
        </div>
    </div>

    <!-- Page -->
    <div class="page">

        <!-- Tables -->
        <div class="section-title">Tables ({{ $tables->count() }})</div>
        <div class="tables-grid">
            <template x-for="t in tables" :key="t.id">
                <div class="tcard" :class="{ active: t.has_session }">
                    <div class="tcard-top">
                        <span class="tcard-label" x-text="t.label"></span>
                        <span class="tcard-status"
                              :class="t.has_session ? 'status-active' : 'status-inactive'"
                              x-text="t.has_session ? 'Active' : 'Libre'"></span>
                    </div>
                    <div class="tcard-code" x-text="'Code : ' + t.code + ' · ' + t.capacity + ' pers.'"></div>
                    <div class="tcard-info">
                        <template x-if="t.has_session && t.session">
                            <div>
                                <div x-text="'Ouvert par ' + (t.session.opened_by || 'N/A')"></div>
                                <div class="tcard-timer" x-text="formatTimer(t.session.remaining_seconds)"></div>
                            </div>
                        </template>
                        <template x-if="!t.has_session">
                            <div>—</div>
                        </template>
                    </div>
                    <template x-if="!t.has_session">
                        <button class="btn btn-open" @click="openSession(t)">▶ Ouvrir la table</button>
                    </template>
                    <template x-if="t.has_session && t.session">
                        <button class="btn btn-close" @click="closeSession(t)">■ Fermer la table</button>
                    </template>
                </div>
            </template>
        </div>

        <!-- Commandes EN_ATTENTE -->
        <div class="section-title" style="display:flex;align-items:center;gap:10px">
            <span>Commandes en attente de validation</span>
            <span x-show="pendingCount > 0"
                  style="background:rgba(245,158,11,.15);color:var(--amber);font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;"
                  x-text="pendingCount"></span>
        </div>

        <div class="orders-list" x-show="pendingOrders.length > 0">
            <template x-for="order in pendingOrders" :key="order.id">
                <div class="ocard">
                    <div class="ocard-header">
                        <span class="ocard-num"   x-text="'#' + order.order_number"></span>
                        <span class="ocard-table" x-text="order.table ? order.table.label + ' (' + order.table.code + ')' : '—'"></span>
                        <span class="ocard-time"  x-text="timeAgo(order.created_at)"></span>
                    </div>
                    <div class="ocard-items">
                        <template x-for="item in order.items" :key="item.name + item.quantity">
                            <div class="ocard-item">
                                <span x-text="item.quantity + '× ' + item.name"></span>
                                <span x-text="formatPrice(item.price * item.quantity)"></span>
                            </div>
                        </template>
                    </div>
                    <div class="ocard-total" x-text="'Total : ' + formatPrice(order.total)"></div>
                    <template x-if="order.notes">
                        <div style="font-size:12px;color:var(--mut);margin-bottom:12px" x-text="'Note : ' + order.notes"></div>
                    </template>
                    <div class="ocard-actions">
                        <button class="btn btn-validate" @click="validateOrder(order)">✓ Valider → cuisine</button>
                        <button class="btn btn-refuse"   @click="refuseOrder(order)">✕ Refuser</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="empty-state" x-show="pendingOrders.length === 0">
            <div class="icon">✓</div>
            <p>Aucune commande en attente de validation.</p>
        </div>

    </div>

    <!-- Toast -->
    <div class="toast" :class="toast.type" x-show="toast.show" x-transition x-cloak x-text="toast.message"></div>

<script>
function dashboard() {
    return {
        tables: @json($tables->map(fn($t) => [
            'id'          => $t->id,
            'code'        => $t->code,
            'label'       => $t->label,
            'capacity'    => $t->capacity,
            'has_session' => (bool) $t->activeSession,
            'session'     => $t->activeSession ? [
                'id'                => $t->activeSession->id,
                'opened_at'         => $t->activeSession->opened_at->toIso8601String(),
                'last_activity_at'  => $t->activeSession->last_activity_at?->toIso8601String(),
                'remaining_seconds' => $t->activeSession->getRemainingSeconds(),
                'opened_by'         => $t->activeSession->openedBy?->name,
            ] : null,
        ])),
        pendingOrders: @json($pendingOrders->map(fn($o) => [
            'id'           => $o->id,
            'order_number' => $o->order_number,
            'table'        => $o->table ? ['label' => $o->table->label, 'code' => $o->table->code] : null,
            'total'        => $o->total,
            'notes'        => $o->notes,
            'created_at'   => $o->created_at->toIso8601String(),
            'items'        => $o->items->map(fn($i) => [
                'name'     => $i->dish?->name ?? '—',
                'quantity' => $i->quantity,
                'price'    => $i->unit_price,
            ]),
        ])),
        pendingCount: {{ $pendingOrders->count() }},
        lastSync: null,
        toast: { show: false, message: '', type: 'success' },

        init() {
            // Timer à rebours côté client
            setInterval(() => {
                this.tables = this.tables.map(t => {
                    if (t.has_session && t.session && t.session.remaining_seconds > 0) {
                        t.session.remaining_seconds = Math.max(0, t.session.remaining_seconds - 1);
                    }
                    return t;
                });
            }, 1000);

            // Polling toutes les 15 secondes
            setInterval(() => this.poll(), 15000);
        },

        async poll() {
            try {
                const res  = await fetch('{{ route('serveur.dashboard.data', $tenant->slug) }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                this.tables        = data.tables;
                this.pendingOrders = data.pending_orders;
                this.pendingCount  = data.pending_count;
                const now = new Date();
                this.lastSync = now.toTimeString().slice(0, 8);
            } catch (e) { /* silencieux */ }
        },

        async openSession(table) {
            const res  = await this.post('{{ url('/serveur/' . $tenant->slug . '/tables') }}/' + table.id + '/session/open');
            const data = await res.json();
            data.success ? this.showToast(data.message, 'success') : this.showToast(data.message || 'Erreur', 'error');
            if (data.success) await this.poll();
        },

        async closeSession(table) {
            if (!confirm(`Fermer la session de ${table.label} ?`)) return;
            const sessionId = table.session?.id;
            if (!sessionId) return;
            const res  = await this.post('{{ url('/serveur/' . $tenant->slug . '/sessions') }}/' + sessionId + '/close');
            const data = await res.json();
            data.success ? this.showToast(data.message, 'success') : this.showToast(data.message || 'Erreur', 'error');
            if (data.success) await this.poll();
        },

        async validateOrder(order) {
            const res  = await this.post('{{ url('/serveur/' . $tenant->slug . '/orders') }}/' + order.id + '/validate');
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                this.pendingOrders = this.pendingOrders.filter(o => o.id !== order.id);
                this.pendingCount  = this.pendingOrders.length;
            } else {
                this.showToast(data.message || 'Erreur', 'error');
            }
        },

        async refuseOrder(order) {
            if (!confirm(`Refuser la commande #${order.order_number} ?`)) return;
            const res  = await this.post('{{ url('/serveur/' . $tenant->slug . '/orders') }}/' + order.id + '/refuse');
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message, 'success');
                this.pendingOrders = this.pendingOrders.filter(o => o.id !== order.id);
                this.pendingCount  = this.pendingOrders.length;
            } else {
                this.showToast(data.message || 'Erreur', 'error');
            }
        },

        post(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':       'application/json',
                    'Content-Type': 'application/json',
                },
            });
        },

        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3500);
        },

        formatTimer(seconds) {
            if (seconds <= 0) return '⚠ Session expirée';
            if (seconds < 60) return `⏱ ${seconds}s restantes`;
            const m = Math.floor(seconds / 60);
            const s = seconds % 60;
            return `⏱ ${m}m ${s.toString().padStart(2, '0')}s`;
        },

        formatPrice(amount) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(amount)) + ' FCFA';
        },

        timeAgo(iso) {
            const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
            if (diff < 60)   return `il y a ${diff}s`;
            if (diff < 3600) return `il y a ${Math.floor(diff / 60)}min`;
            return `il y a ${Math.floor(diff / 3600)}h`;
        },
    };
}
</script>
</body>
</html>
