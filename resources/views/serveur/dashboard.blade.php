<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tables — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak]{display:none!important}
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        html,body{height:100%;background:#0b0f1a;color:#f9fafb;font-family:'DM Sans',ui-sans-serif,sans-serif;overflow:hidden}
        :root{
            --amber:#f59e0b;--amber-l:#fcd34d;--amber-dim:rgba(245,158,11,.13);
            --green:#22c55e;--green-dim:rgba(34,197,94,.12);
            --red:#ef4444;--red-dim:rgba(239,68,68,.12);
            --blue:#3b82f6;--blue-dim:rgba(59,130,246,.12);
            --s1:#0d1120;--s2:#111827;--s3:#1f2937;--s4:#374151;
            --bd:rgba(255,255,255,.07);--mut:#6b7280;
        }
        .app{display:flex;height:100vh}

        /* ── Sidebar ── */
        .sidebar{width:220px;flex-shrink:0;background:var(--s1);border-right:1px solid var(--bd);display:flex;flex-direction:column;overflow-y:auto}
        .sb-brand{padding:16px 14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:10px;text-decoration:none;color:#f9fafb}
        .sb-icon{width:32px;height:32px;border-radius:9px;background:var(--amber-dim);border:1px solid rgba(245,158,11,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .sb-tenant{padding:12px 14px;border-bottom:1px solid var(--bd)}
        .sb-tenant-lbl{font-size:10px;text-transform:uppercase;letter-spacing:.07em;color:var(--mut);margin-bottom:3px}
        .sb-tenant-name{font-size:13px;font-weight:600}
        .sb-nav{padding:10px 8px;flex:1}
        .sb-group{margin-bottom:16px}
        .sb-group-title{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--mut);padding:0 6px;margin-bottom:5px}
        .sb-item{display:flex;align-items:center;gap:9px;padding:8px 9px;border-radius:9px;font-size:12px;font-weight:500;color:#d1d5db;text-decoration:none;transition:background .15s,color .15s;position:relative}
        .sb-item:hover{background:var(--s3);color:#f9fafb}
        .sb-item.active{background:var(--amber-dim);color:var(--amber);border:1px solid rgba(245,158,11,.2)}
        .sb-item .ico{font-size:14px;width:18px;text-align:center;flex-shrink:0}
        .sb-badge{margin-left:auto;background:var(--red);color:#fff;font-size:9px;font-weight:700;padding:1px 5px;border-radius:999px;min-width:16px;text-align:center}
        .sb-badge.amber{background:var(--amber);color:#000}
        .sb-footer{padding:12px 14px;border-top:1px solid var(--bd)}
        .sb-user{display:flex;align-items:center;gap:9px}
        .sb-avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#92400e,var(--amber));display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;color:#000}
        .sb-uname{font-size:12px;font-weight:600}
        .sb-urole{font-size:10px;color:var(--mut)}
        .sb-logout{margin-left:auto;background:none;border:none;cursor:pointer;color:var(--mut);font-size:16px;transition:color .15s;padding:4px}
        .sb-logout:hover{color:var(--red)}

        /* ── Main ── */
        .main{flex:1;display:flex;flex-direction:column;overflow:hidden}
        .topbar{flex-shrink:0;display:flex;align-items:center;justify-content:space-between;padding:10px 20px;background:var(--s1);border-bottom:1px solid var(--bd);gap:10px}
        .tb-left{display:flex;align-items:center;gap:10px}
        .tb-title{font-size:15px;font-weight:700}
        .tb-right{display:flex;align-items:center;gap:8px}
        .sync-txt{font-size:11px;color:var(--mut)}

        /* validation toggle */
        .vtoggle{display:flex;align-items:center;gap:7px;padding:6px 12px;border-radius:9px;font-size:11px;font-weight:600;cursor:pointer;border:none;transition:all .2s}
        .vtoggle.on{background:rgba(34,197,94,.12);color:var(--green);border:1px solid rgba(34,197,94,.25)}
        .vtoggle.off{background:rgba(245,158,11,.12);color:var(--amber);border:1px solid rgba(245,158,11,.25)}
        .vtoggle-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .vtoggle.on .vtoggle-dot{background:var(--green)}
        .vtoggle.off .vtoggle-dot{background:var(--amber)}

        .content{flex:1;overflow-y:auto;padding:20px}

        /* Bulk actions */
        .bulk-bar{display:flex;gap:8px;margin-bottom:16px}
        .btn-bulk{display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:9px;font-size:11px;font-weight:600;border:none;cursor:pointer;transition:opacity .15s}
        .btn-bulk:hover{opacity:.8}
        .btn-bulk.open-all{background:var(--green-dim);color:var(--green);border:1px solid rgba(34,197,94,.25)}
        .btn-bulk.close-all{background:var(--red-dim);color:var(--red);border:1px solid rgba(239,68,68,.25)}

        /* Tables grid */
        .section-lbl{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.07em;color:var(--mut);margin-bottom:12px;display:flex;align-items:center;gap:7px}
        .tables-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;margin-bottom:28px}
        .tcard{background:var(--s2);border:1px solid var(--bd);border-radius:14px;padding:16px 14px;transition:border-color .2s}
        .tcard.active{border-color:rgba(34,197,94,.35);background:linear-gradient(135deg,rgba(34,197,94,.04) 0%,var(--s2) 60%)}
        .tcard-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px}
        .tcard-lbl{font-weight:700;font-size:14px}
        .tcard-st{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:2px 7px;border-radius:999px}
        .st-active{background:var(--green-dim);color:var(--green)}
        .st-libre{background:rgba(255,255,255,.06);color:var(--mut)}
        .tcard-meta{font-size:11px;color:var(--mut);margin-bottom:8px}
        .tcard-info{font-size:11px;color:var(--mut);min-height:28px;margin-bottom:12px}
        .tcard-timer{font-size:10px;color:var(--amber);font-variant-numeric:tabular-nums;margin-top:2px}
        .btn-tbl{display:flex;align-items:center;justify-content:center;gap:5px;padding:6px 10px;border-radius:8px;font-size:11px;font-weight:600;border:none;cursor:pointer;width:100%;transition:opacity .15s,transform .1s}
        .btn-tbl:hover{opacity:.85}
        .btn-tbl:active{transform:scale(.97)}
        .btn-open{background:var(--green-dim);color:var(--green);border:1px solid rgba(34,197,94,.25)}
        .btn-close{background:var(--red-dim);color:var(--red);border:1px solid rgba(239,68,68,.25)}

        /* Toast */
        .toast{position:fixed;bottom:20px;right:20px;z-index:200;background:var(--s3);border:1px solid var(--bd);border-radius:12px;padding:12px 16px;font-size:13px;max-width:280px;box-shadow:0 8px 32px rgba(0,0,0,.5)}
        .toast.success{border-color:rgba(34,197,94,.3);color:var(--green)}
        .toast.error{border-color:rgba(239,68,68,.3);color:var(--red)}
    </style>
</head>
<body x-data="dashboard()" x-init="init()">
<div class="app">

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <a href="{{ route('serveur.commande.index', $tenant->slug) }}" class="sb-brand">
            <div class="sb-icon">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="color:var(--amber)"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5m-16.5 4.5h16.5"/></svg>
            </div>
            <div>
                <div style="font-size:13px;font-weight:700">HorusPOS</div>
                <div style="font-size:10px;color:var(--mut)">Espace Serveur</div>
            </div>
        </a>

        <div class="sb-tenant">
            <div class="sb-tenant-lbl">Restaurant</div>
            <div class="sb-tenant-name">{{ $tenant->name }}</div>
        </div>

        <nav class="sb-nav">
            <div class="sb-group">
                <div class="sb-group-title">Tables</div>
                <a href="{{ route('serveur.dashboard', $tenant->slug) }}" class="sb-item active">
                    <span class="ico">⊟</span><span>Gestion tables</span>
                </a>
            </div>
            <div class="sb-group">
                <div class="sb-group-title">Commandes</div>
                <a href="{{ route('serveur.pending', $tenant->slug) }}" class="sb-item">
                    <span class="ico">⏳</span><span>En attente</span>
                    <span class="sb-badge amber" x-show="pendingCount > 0" x-text="pendingCount" x-cloak></span>
                </a>
                <a href="{{ route('serveur.commande.index', $tenant->slug) }}" class="sb-item">
                    <span class="ico">✏</span><span>Prise de commande</span>
                </a>
                <a href="{{ route('serveur.historique.index', $tenant->slug) }}" class="sb-item">
                    <span class="ico">📋</span><span>Historique</span>
                </a>
            </div>
            @if(auth()->user()->role === 'ADMIN')
            <div class="sb-group">
                <div class="sb-group-title">Administration</div>
                <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="sb-item">
                    <span class="ico">⚙</span><span>Panneau admin</span>
                </a>
                <a href="{{ route('admin.tables.index', $tenant->slug) }}" class="sb-item">
                    <span class="ico">◫</span><span>Gestion tables</span>
                </a>
                <a href="{{ route('admin.qrcodes.index', $tenant->slug) }}" class="sb-item">
                    <span class="ico">⊞</span><span>QR Codes</span>
                </a>
            </div>
            @endif
        </nav>

        <div class="sb-footer">
            <div class="sb-user">
                <div class="sb-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div><div class="sb-uname">{{ auth()->user()->name }}</div><div class="sb-urole">{{ auth()->user()->role }}</div></div>
                <form method="POST" action="{{ route('logout') }}" style="margin-left:auto">
                    @csrf
                    <button class="sb-logout" title="Déconnexion">⏻</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ── Main ── -->
    <div class="main">
        <div class="topbar">
            <div class="tb-left">
                <span class="tb-title">Gestion des tables</span>
                <span x-show="pendingCount > 0" class="sb-badge amber" x-text="pendingCount + ' en attente'" x-cloak></span>
                <span x-show="waiterCallCount > 0" class="sb-badge" style="background:var(--red);" x-text="waiterCallCount + ' appel(s) serveur'" x-cloak></span>
            </div>
            <div class="tb-right">
                <span class="sync-txt" x-show="lastSync" x-text="'⟳ ' + lastSync" x-cloak></span>

                {{-- Toggle validation — visible pour tous les rôles --}}
                <button class="vtoggle" :class="validationEnabled ? 'on' : 'off'" @click="toggleValidation()">
                    <span class="vtoggle-dot"></span>
                    <span x-text="validationEnabled ? 'Validation ON' : 'Direct cuisine'"></span>
                </button>
            </div>
        </div>

        <div class="content">
            <!-- Boutons bulk -->
            <div class="bulk-bar">
                <button class="btn-bulk open-all" @click="openAll()">▶ Ouvrir toutes les tables</button>
                <button class="btn-bulk close-all" @click="closeAll()">■ Fermer toutes les tables</button>
            </div>

            <div class="section-lbl">
                <span>Tables</span>
                <span style="background:var(--s3);padding:1px 7px;border-radius:999px;font-size:10px" x-text="tables.length"></span>
            </div>

            <div class="tables-grid">
                <template x-for="t in tables" :key="t.id">
                    <div class="tcard" :class="{ active: t.has_session }">
                        <div class="tcard-top">
                            <span class="tcard-lbl" x-text="t.label"></span>
                            <span class="tcard-st" :class="t.has_session ? 'st-active' : 'st-libre'"
                                  x-text="t.has_session ? 'Active' : 'Libre'"></span>
                        </div>
                        <div class="tcard-meta" x-text="'Code : ' + t.code + ' · ' + t.capacity + ' pers.'"></div>
                        <div class="tcard-info">
                            <template x-if="t.has_session && t.session">
                                <div>
                                    <div x-text="t.session.opened_by ? 'Par ' + t.session.opened_by : ''"></div>
                                    <div class="tcard-timer" x-text="'⏱ ' + formatDuration(t.session.opened_at)"></div>
                                </div>
                            </template>
                            <template x-if="!t.has_session"><div>—</div></template>
                        </div>
                        <template x-if="!t.has_session">
                            <button class="btn-tbl btn-open" @click="openSession(t)">▶ Ouvrir</button>
                        </template>
                        <template x-if="t.has_session && t.session">
                            <button class="btn-tbl btn-close" @click="closeSession(t)">■ Fermer</button>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<div class="toast" :class="toast.type" x-show="toast.show" x-transition x-cloak x-text="toast.message"></div>

<script>
function playNotification(type = 'order') {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const sequences = {
            order: [{f:880,d:0.08},{f:1100,d:0.12},{f:880,d:0.08},{f:1320,d:0.18}],
            call:  [{f:660,d:0.1},{f:880,d:0.1},{f:660,d:0.1},{f:880,d:0.1},{f:1100,d:0.2}],
        };
        let t = ctx.currentTime;
        (sequences[type] || sequences.order).forEach(({f, d}) => {
            const o = ctx.createOscillator();
            const g = ctx.createGain();
            o.connect(g); g.connect(ctx.destination);
            o.frequency.value = f;
            g.gain.setValueAtTime(0.35, t);
            g.gain.exponentialRampToValueAtTime(0.001, t + d);
            o.start(t); o.stop(t + d);
            t += d + 0.03;
        });
    } catch(e){}
}

function dashboard() {
    return {
        tables:           {!! $tablesJson !!},
        pendingCount:     {{ $pendingOrders->count() }},
        waiterCallCount:  0,
        validationEnabled: {{ $tenant->require_order_validation ? 'true' : 'false' }},
        lastSync: null,
        toast: { show: false, message: '', type: 'success' },
        _prevPendingCount: {{ $pendingOrders->count() }},
        _prevCallCount: 0,

        init() {
            setInterval(() => this.poll(), 8000);
            setInterval(() => this.pollCalls(), 10000);
            this.pollCalls();
        },

        async poll() {
            try {
                const res  = await fetch('{{ route('serveur.dashboard.data', $tenant->slug) }}', { headers: {'X-Requested-With':'XMLHttpRequest'} });
                const data = await res.json();
                const newCount = data.pending_count;
                if (newCount > this._prevPendingCount) {
                    playNotification('order');
                    this.showToast(`🔔 ${newCount - this._prevPendingCount} nouvelle(s) commande(s) en attente`, 'success');
                }
                this._prevPendingCount = newCount;
                this.tables       = data.tables;
                this.pendingCount = newCount;
                this.lastSync = new Date().toTimeString().slice(0,8);
            } catch(e){}
        },

        async pollCalls() {
            try {
                const res  = await fetch('/api/waiter-calls?tenant_id={{ $tenant->id }}', { headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'} });
                const data = await res.json();
                const calls = data.calls ?? [];
                const newCount = calls.length;
                if (newCount > this._prevCallCount) {
                    playNotification('call');
                    const tableName = calls[0]?.table_name ?? calls[0]?.table_code ?? '';
                    this.showToast(`🔔 Appel serveur${tableName ? ' — ' + tableName : ''}`, 'error');
                }
                this._prevCallCount = newCount;
                this.waiterCallCount = newCount;
            } catch(e){}
        },

        async openSession(table) {
            const res  = await this.post('{{ url('/serveur/'.$tenant->slug.'/tables') }}/' + table.id + '/session/open');
            const data = await res.json();
            data.success ? this.showToast(data.message,'success') : this.showToast(data.message||'Erreur','error');
            if (data.success) await this.poll();
        },

        async closeSession(table) {
            if (!confirm(`Fermer ${table.label} ?`)) return;
            const res  = await this.post('{{ url('/serveur/'.$tenant->slug.'/sessions') }}/' + table.session.id + '/close');
            const data = await res.json();
            data.success ? this.showToast(data.message,'success') : this.showToast(data.message||'Erreur','error');
            if (data.success) await this.poll();
        },

        async openAll() {
            if (!confirm('Ouvrir toutes les tables ?')) return;
            const res  = await this.post('{{ route('serveur.session.openAll', $tenant->slug) }}');
            const data = await res.json();
            data.success ? this.showToast(data.message,'success') : this.showToast(data.message||'Erreur','error');
            if (data.success) await this.poll();
        },

        async closeAll() {
            if (!confirm('Fermer toutes les tables ?')) return;
            const res  = await this.post('{{ route('serveur.session.closeAll', $tenant->slug) }}');
            const data = await res.json();
            data.success ? this.showToast(data.message,'success') : this.showToast(data.message||'Erreur','error');
            if (data.success) await this.poll();
        },

        async toggleValidation() {
            const res  = await this.post('{{ route('serveur.settings.toggleValidation', $tenant->slug) }}');
            const data = await res.json();
            if (data.success) {
                this.validationEnabled = data.enabled;
                this.showToast(data.message, 'success');
            }
        },

        post(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });
        },

        showToast(message, type='success') {
            this.toast = { show:true, message, type };
            setTimeout(() => this.toast.show = false, 3500);
        },

        formatDuration(openedAt) {
            if (!openedAt) return '';
            const diffMs = Date.now() - new Date(openedAt).getTime();
            const totalSec = Math.floor(diffMs / 1000);
            if (totalSec < 60) return `${totalSec}s`;
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            return h > 0 ? `${h}h ${m.toString().padStart(2,'0')}m` : `${m}m`;
        },
    };
}
</script>
</body>
</html>
