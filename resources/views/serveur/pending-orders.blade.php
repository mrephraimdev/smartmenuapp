<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Commandes en attente — {{ $tenant->name }}</title>
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
        .sb-item{display:flex;align-items:center;gap:9px;padding:8px 9px;border-radius:9px;font-size:12px;font-weight:500;color:#d1d5db;text-decoration:none;transition:background .15s,color .15s}
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
        .tb-title{font-size:15px;font-weight:700}
        .tb-right{display:flex;align-items:center;gap:8px}
        .sync-txt{font-size:11px;color:var(--mut)}
        .pending-pill{background:var(--amber-dim);color:var(--amber);font-size:11px;font-weight:700;padding:4px 10px;border-radius:999px}

        .content{flex:1;overflow-y:auto;padding:20px}

        /* Orders */
        .orders-list{display:flex;flex-direction:column;gap:12px}
        .ocard{background:var(--s2);border:1px solid rgba(245,158,11,.2);border-radius:14px;padding:16px 18px}
        .ocard-header{display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:10px}
        .ocard-num{font-weight:700;font-size:14px}
        .ocard-table{font-size:11px;color:var(--amber);background:var(--amber-dim);padding:2px 9px;border-radius:999px}
        .ocard-time{font-size:11px;color:var(--mut);margin-left:auto}
        .ocard-items{margin-bottom:10px}
        .ocard-item{display:flex;justify-content:space-between;font-size:12px;padding:4px 0;border-bottom:1px solid var(--bd)}
        .ocard-item:last-child{border-bottom:none}
        .ocard-total{font-size:13px;font-weight:700;color:var(--amber);text-align:right;margin-bottom:10px}
        .ocard-note{font-size:11px;color:var(--mut);margin-bottom:10px;font-style:italic}
        .ocard-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        .btn-action{display:flex;align-items:center;justify-content:center;gap:5px;padding:7px 12px;border-radius:8px;font-size:11px;font-weight:600;border:none;cursor:pointer;transition:opacity .15s}
        .btn-action:hover{opacity:.8}
        .btn-validate{background:var(--green-dim);color:var(--green);border:1px solid rgba(34,197,94,.25)}
        .btn-refuse{background:var(--red-dim);color:var(--red);border:1px solid rgba(239,68,68,.25)}

        .empty{text-align:center;padding:60px 20px;color:var(--mut);background:var(--s2);border:1px dashed var(--bd);border-radius:14px}
        .empty-icon{font-size:36px;margin-bottom:12px}
        .empty p{font-size:13px}

        .toast{position:fixed;bottom:20px;right:20px;z-index:200;background:var(--s3);border:1px solid var(--bd);border-radius:12px;padding:12px 16px;font-size:13px;max-width:280px;box-shadow:0 8px 32px rgba(0,0,0,.5)}
        .toast.success{border-color:rgba(34,197,94,.3);color:var(--green)}
        .toast.error{border-color:rgba(239,68,68,.3);color:var(--red)}
    </style>
</head>
<body x-data="pending()" x-init="init()">
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
                <a href="{{ route('serveur.dashboard', $tenant->slug) }}" class="sb-item">
                    <span class="ico">⊟</span><span>Gestion tables</span>
                </a>
            </div>
            <div class="sb-group">
                <div class="sb-group-title">Commandes</div>
                <a href="{{ route('serveur.pending', $tenant->slug) }}" class="sb-item active">
                    <span class="ico">⏳</span><span>En attente</span>
                    <span class="sb-badge amber" x-show="orders.length > 0" x-text="orders.length" x-cloak></span>
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
            <div style="display:flex;align-items:center;gap:10px">
                <span class="tb-title">Commandes en attente</span>
                <span class="pending-pill" x-show="orders.length > 0" x-text="orders.length + ' commande(s)'" x-cloak></span>
            </div>
            <div class="tb-right">
                <span class="sync-txt" x-show="lastSync" x-text="'⟳ ' + lastSync" x-cloak></span>
            </div>
        </div>

        <div class="content">
            <div class="orders-list" x-show="orders.length > 0">
                <template x-for="order in orders" :key="order.id">
                    <div class="ocard">
                        <div class="ocard-header">
                            <span class="ocard-num" x-text="'#' + order.order_number"></span>
                            <span class="ocard-table" x-text="order.table ? order.table.label + ' (' + order.table.code + ')' : '—'"></span>
                            <span class="ocard-time" x-text="timeAgo(order.created_at)"></span>
                        </div>
                        <div class="ocard-items">
                            <template x-for="item in order.items" :key="item.name+item.quantity">
                                <div class="ocard-item">
                                    <span x-text="item.quantity + '× ' + item.name"></span>
                                    <span x-text="fmt(item.price * item.quantity)"></span>
                                </div>
                            </template>
                        </div>
                        <div class="ocard-total" x-text="'Total : ' + fmt(order.total)"></div>
                        <template x-if="order.notes">
                            <div class="ocard-note" x-text="'📝 ' + order.notes"></div>
                        </template>
                        <div class="ocard-actions">
                            <button class="btn-action btn-validate" @click="validate(order)">✓ Valider → cuisine</button>
                            <button class="btn-action btn-refuse"   @click="refuse(order)">✕ Refuser</button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="empty" x-show="orders.length === 0">
                <div class="empty-icon">✓</div>
                <p>Aucune commande en attente de validation.</p>
            </div>
        </div>
    </div>
</div>

<div class="toast" :class="toast.type" x-show="toast.show" x-transition x-cloak x-text="toast.message"></div>

<script>
function pending() {
    return {
        orders:   {!! $ordersJson !!},
        lastSync: null,
        toast:    { show:false, message:'', type:'success' },

        init() {
            setInterval(() => this.poll(), 10000);
        },

        async poll() {
            try {
                const res  = await fetch('{{ route('serveur.pending.data', $tenant->slug) }}', { headers:{'X-Requested-With':'XMLHttpRequest'} });
                const data = await res.json();
                this.orders   = data.pending_orders;
                this.lastSync = new Date().toTimeString().slice(0,8);
            } catch(e){}
        },

        async validate(order) {
            const res  = await this.post('{{ url('/serveur/'.$tenant->slug.'/orders') }}/' + order.id + '/validate');
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message,'success');
                this.orders = this.orders.filter(o => o.id !== order.id);
            } else { this.showToast(data.message||'Erreur','error'); }
        },

        async refuse(order) {
            if (!confirm(`Refuser la commande #${order.order_number} ?`)) return;
            const res  = await this.post('{{ url('/serveur/'.$tenant->slug.'/orders') }}/' + order.id + '/refuse');
            const data = await res.json();
            if (data.success) {
                this.showToast(data.message,'success');
                this.orders = this.orders.filter(o => o.id !== order.id);
            } else { this.showToast(data.message||'Erreur','error'); }
        },

        post(url) {
            return fetch(url, {
                method:'POST',
                headers:{
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept':'application/json','Content-Type':'application/json',
                },
            });
        },

        showToast(message, type='success') {
            this.toast = { show:true, message, type };
            setTimeout(() => this.toast.show = false, 3500);
        },

        fmt(amount) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(amount)) + ' FCFA';
        },

        timeAgo(iso) {
            const diff = Math.floor((Date.now() - new Date(iso)) / 1000);
            if (diff < 60)   return `il y a ${diff}s`;
            if (diff < 3600) return `il y a ${Math.floor(diff/60)}min`;
            return `il y a ${Math.floor(diff/3600)}h`;
        },
    };
}
</script>
</body>
</html>
