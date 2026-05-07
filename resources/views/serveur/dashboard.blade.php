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
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: #0b0f1a; color: #f9fafb; font-family: 'DM Sans', ui-sans-serif, sans-serif; }

        :root {
            --amber: #f59e0b; --amber-l: #fcd34d; --amber-dim: rgba(245,158,11,0.13);
            --green: #22c55e; --green-dim: rgba(34,197,94,0.12);
            --red: #ef4444;   --red-dim: rgba(239,68,68,0.12);
            --blue: #3b82f6;  --blue-dim: rgba(59,130,246,0.12);
            --purple: #a78bfa;
            --s0: #080c18; --s1: #0d1120; --s2: #111827; --s3: #1f2937; --s4: #374151;
            --bd: rgba(255,255,255,0.07); --txt: #f9fafb; --mut: #6b7280;
            --sidebar-w: 240px;
        }

        /* ── Layout ── */
        .app { display: flex; height: 100vh; overflow: hidden; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            flex-shrink: 0;
            background: var(--s0);
            border-right: 1px solid var(--bd);
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 20px 18px 16px;
            border-bottom: 1px solid var(--bd);
        }
        .sidebar-brand .logo {
            display: flex; align-items: center; gap: 10px;
            text-decoration: none; color: var(--txt);
        }
        .sidebar-brand .logo-icon {
            width: 34px; height: 34px; border-radius: 10px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .sidebar-brand .logo-name { font-size: 15px; font-weight: 700; }
        .sidebar-brand .logo-sub  { font-size: 11px; color: var(--mut); margin-top: 1px; }

        .sidebar-tenant {
            padding: 14px 18px;
            border-bottom: 1px solid var(--bd);
        }
        .sidebar-tenant .tenant-label { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: var(--mut); margin-bottom: 4px; }
        .sidebar-tenant .tenant-name  { font-size: 13px; font-weight: 600; color: var(--txt); }

        .sidebar-nav { padding: 12px 10px; flex: 1; }
        .nav-group { margin-bottom: 20px; }
        .nav-group-title {
            font-size: 10px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .08em; color: var(--mut);
            padding: 0 8px; margin-bottom: 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 10px; border-radius: 10px;
            font-size: 13px; font-weight: 500; color: #d1d5db;
            text-decoration: none; transition: background .15s, color .15s;
            position: relative; cursor: pointer;
        }
        .nav-item:hover { background: var(--s3); color: var(--txt); }
        .nav-item.active { background: rgba(167,139,250,0.12); color: var(--purple); }
        .nav-item .nav-icon { font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; }
        .nav-item .nav-badge {
            margin-left: auto;
            background: var(--red); color: #fff;
            font-size: 10px; font-weight: 700;
            padding: 1px 6px; border-radius: 999px; min-width: 18px; text-align: center;
        }
        .nav-item .nav-badge.amber { background: var(--amber); color: #000; }
        .nav-item .nav-badge.green { background: var(--green); color: #000; }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid var(--bd);
        }
        .user-row {
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; flex-shrink: 0;
        }
        .user-name  { font-size: 13px; font-weight: 600; }
        .user-role  { font-size: 11px; color: var(--mut); }
        .logout-btn {
            margin-left: auto; font-size: 18px; color: var(--mut);
            background: none; border: none; cursor: pointer;
            transition: color .15s;
        }
        .logout-btn:hover { color: var(--red); }

        /* ── Main ── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }

        .topbar {
            background: var(--s1);
            border-bottom: 1px solid var(--bd);
            padding: 14px 24px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .topbar-title { font-size: 16px; font-weight: 700; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .sync-badge {
            font-size: 11px; color: var(--mut);
            background: var(--s3); padding: 4px 10px; border-radius: 999px;
        }
        .pending-badge {
            background: var(--amber); color: #000;
            font-size: 11px; font-weight: 700;
            padding: 4px 10px; border-radius: 999px;
        }

        .content { flex: 1; overflow-y: auto; padding: 24px; }

        /* ── Section title ── */
        .section-title {
            font-size: 12px; font-weight: 600; text-transform: uppercase;
            letter-spacing: .07em; color: var(--mut);
            margin-bottom: 14px; display: flex; align-items: center; gap: 8px;
        }

        /* ── Tables grid ── */
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 12px; margin-bottom: 36px;
        }
        .tcard {
            background: var(--s2);
            border: 1px solid var(--bd);
            border-radius: 16px; padding: 18px 16px;
            transition: border-color .2s, box-shadow .2s;
        }
        .tcard.active {
            border-color: rgba(34,197,94,.35);
            box-shadow: 0 0 0 1px rgba(34,197,94,.08);
        }
        .tcard-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
        .tcard-label { font-weight: 700; font-size: 15px; }
        .tcard-status {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; padding: 3px 8px; border-radius: 999px;
        }
        .status-active   { background: var(--green-dim); color: var(--green); }
        .status-inactive { background: rgba(255,255,255,.06); color: var(--mut); }
        .tcard-meta  { font-size: 12px; color: var(--mut); margin-bottom: 10px; }
        .tcard-info  { font-size: 12px; color: var(--mut); min-height: 32px; margin-bottom: 14px; }
        .tcard-timer { font-size: 11px; color: var(--amber); font-variant-numeric: tabular-nums; margin-top: 3px; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;
            border: none; cursor: pointer; transition: opacity .15s, transform .1s; width: 100%;
        }
        .btn:hover  { opacity: .85; }
        .btn:active { transform: scale(.97); }
        .btn-open     { background: var(--green-dim); color: var(--green);  border: 1px solid rgba(34,197,94,.25); }
        .btn-close    { background: var(--red-dim);   color: var(--red);    border: 1px solid rgba(239,68,68,.25); }
        .btn-validate { background: var(--green-dim); color: var(--green);  border: 1px solid rgba(34,197,94,.25); }
        .btn-refuse   { background: var(--red-dim);   color: var(--red);    border: 1px solid rgba(239,68,68,.25); }

        /* ── Orders ── */
        .orders-list { display: flex; flex-direction: column; gap: 12px; }
        .ocard {
            background: var(--s2);
            border: 1px solid rgba(245,158,11,.2);
            border-radius: 16px; padding: 18px 20px;
        }
        .ocard-header { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .ocard-num   { font-weight: 700; font-size: 15px; margin-right: 4px; }
        .ocard-table { font-size: 12px; color: var(--amber); background: var(--amber-dim); padding: 3px 10px; border-radius: 999px; }
        .ocard-time  { font-size: 12px; color: var(--mut); margin-left: auto; }
        .ocard-items { margin-bottom: 12px; }
        .ocard-item  { display: flex; justify-content: space-between; font-size: 13px; padding: 5px 0; border-bottom: 1px solid var(--bd); }
        .ocard-item:last-child { border-bottom: none; }
        .ocard-total   { font-size: 14px; font-weight: 700; color: var(--amber); text-align: right; margin-bottom: 14px; }
        .ocard-note    { font-size: 12px; color: var(--mut); margin-bottom: 12px; font-style: italic; }
        .ocard-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

        .empty-state {
            text-align: center; padding: 48px 20px; color: var(--mut);
            background: var(--s2); border: 1px dashed var(--bd); border-radius: 16px;
        }
        .empty-icon { font-size: 32px; margin-bottom: 10px; }
        .empty-state p { font-size: 13px; }

        /* ── Toast ── */
        .toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 200;
            background: var(--s3); border: 1px solid var(--bd);
            border-radius: 14px; padding: 14px 18px;
            font-size: 13px; max-width: 300px;
            box-shadow: 0 8px 32px rgba(0,0,0,.5);
        }
        .toast.success { border-color: rgba(34,197,94,.3); color: var(--green); }
        .toast.error   { border-color: rgba(239,68,68,.3); color: var(--red); }
    </style>
</head>
<body x-data="dashboard()" x-init="init()">
<div class="app">

    <!-- ════════════════════════ SIDEBAR ════════════════════════ -->
    <aside class="sidebar">

        <!-- Logo -->
        <div class="sidebar-brand">
            <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="logo">
                <div class="logo-icon">⊞</div>
                <div>
                    <div class="logo-name">HorusPOS</div>
                    <div class="logo-sub">Espace Serveur</div>
                </div>
            </a>
        </div>

        <!-- Restaurant -->
        <div class="sidebar-tenant">
            <div class="tenant-label">Restaurant</div>
            <div class="tenant-name">{{ $tenant->name }}</div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            <div class="nav-group">
                <div class="nav-group-title">Tables</div>
                <a href="{{ route('serveur.dashboard', $tenant->slug) }}" class="nav-item active">
                    <span class="nav-icon">⊟</span>
                    <span>Dashboard tables</span>
                    <span class="nav-badge amber" x-show="pendingCount > 0" x-text="pendingCount"></span>
                </a>
            </div>

            <div class="nav-group">
                <div class="nav-group-title">Commandes</div>
                <a href="{{ route('serveur.commande.index', $tenant->slug) }}" class="nav-item">
                    <span class="nav-icon">✏</span>
                    <span>Prise de commande</span>
                </a>
                <a href="{{ route('serveur.historique.index', $tenant->slug) }}" class="nav-item">
                    <span class="nav-icon">📋</span>
                    <span>Historique</span>
                </a>
            </div>

            @if(auth()->user()->role === 'ADMIN')
            <div class="nav-group">
                <div class="nav-group-title">Administration</div>
                <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="nav-item">
                    <span class="nav-icon">⚙</span>
                    <span>Panneau admin</span>
                </a>
                <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="nav-item">
                    <span class="nav-icon">◫</span>
                    <span>Gestion tables</span>
                </a>
                <a href="{{ route('admin.qrcodes.index', $tenant->slug) }}" class="nav-item">
                    <span class="nav-icon">⊞</span>
                    <span>QR Codes</span>
                </a>
            </div>
            @endif

        </nav>

        <!-- Footer user -->
        <div class="sidebar-footer">
            <div class="user-row">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->role }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
                    @csrf
                    <button type="submit" class="logout-btn" title="Déconnexion">⏻</button>
                </form>
            </div>
        </div>

    </aside>

    <!-- ════════════════════════ MAIN ════════════════════════ -->
    <div class="main">

        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-title">Dashboard Serveur</div>
            <div class="topbar-right">
                <span class="sync-badge" x-show="lastSync" x-text="'⟳ ' + lastSync" x-cloak></span>
                <span class="pending-badge" x-show="pendingCount > 0" x-text="pendingCount + ' en attente'" x-cloak></span>
            </div>
        </div>

        <!-- Content -->
        <div class="content">

            <!-- Tables -->
            <div class="section-title">
                <span>Tables</span>
                <span style="background:var(--s3);padding:2px 8px;border-radius:999px;font-size:11px" x-text="tables.length"></span>
            </div>

            <div class="tables-grid">
                <template x-for="t in tables" :key="t.id">
                    <div class="tcard" :class="{ active: t.has_session }">
                        <div class="tcard-top">
                            <span class="tcard-label" x-text="t.label"></span>
                            <span class="tcard-status"
                                  :class="t.has_session ? 'status-active' : 'status-inactive'"
                                  x-text="t.has_session ? 'Active' : 'Libre'"></span>
                        </div>
                        <div class="tcard-meta" x-text="'Code : ' + t.code + ' · ' + t.capacity + ' pers.'"></div>
                        <div class="tcard-info">
                            <template x-if="t.has_session && t.session">
                                <div>
                                    <div x-text="'Ouvert par ' + (t.session.opened_by || 'N/A')"></div>
                                    <div class="tcard-timer" x-text="formatTimer(t.session.remaining_seconds)"></div>
                                </div>
                            </template>
                            <template x-if="!t.has_session">
                                <div style="color:var(--mut)">—</div>
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
            <div class="section-title">
                <span>Commandes en attente de validation</span>
                <span x-show="pendingCount > 0"
                      style="background:var(--amber-dim);color:var(--amber);padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700;"
                      x-text="pendingCount" x-cloak></span>
            </div>

            <div class="orders-list" x-show="pendingOrders.length > 0">
                <template x-for="order in pendingOrders" :key="order.id">
                    <div class="ocard">
                        <div class="ocard-header">
                            <span class="ocard-num" x-text="'#' + order.order_number"></span>
                            <span class="ocard-table" x-text="order.table ? order.table.label + ' (' + order.table.code + ')' : '—'"></span>
                            <span class="ocard-time" x-text="timeAgo(order.created_at)"></span>
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
                            <div class="ocard-note" x-text="'📝 ' + order.notes"></div>
                        </template>
                        <div class="ocard-actions">
                            <button class="btn btn-validate" @click="validateOrder(order)">✓ Valider → cuisine</button>
                            <button class="btn btn-refuse"   @click="refuseOrder(order)">✕ Refuser</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="empty-state" x-show="pendingOrders.length === 0">
                <div class="empty-icon">✓</div>
                <p>Aucune commande en attente de validation.</p>
            </div>

        </div>
    </div>

</div>

<!-- Toast -->
<div class="toast" :class="toast.type" x-show="toast.show" x-transition x-cloak x-text="toast.message"></div>

<script>
function dashboard() {
    return {
        tables:        {!! $tablesJson !!},
        pendingOrders: {!! $ordersJson !!},
        pendingCount:  {{ $pendingOrders->count() }},
        lastSync: null,
        toast: { show: false, message: '', type: 'success' },

        init() {
            setInterval(() => {
                this.tables = this.tables.map(t => {
                    if (t.has_session && t.session && t.session.remaining_seconds > 0) {
                        t.session.remaining_seconds = Math.max(0, t.session.remaining_seconds - 1);
                    }
                    return t;
                });
            }, 1000);
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
                this.lastSync = new Date().toTimeString().slice(0, 8);
            } catch (e) {}
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
