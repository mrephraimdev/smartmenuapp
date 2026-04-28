<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes — {{ $tenant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;0,9..40,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; }
        .font-display { font-weight: 700; }

        :root {
            --amber: #f59e0b;
            --amber-light: #fcd34d;
            --amber-dim: rgba(245,158,11,0.12);
            --surface: #111827;
            --surface-2: #1f2937;
            --surface-3: #374151;
            --border: rgba(255,255,255,0.07);
            --text: #f9fafb;
            --text-muted: #9ca3af;
        }

        html, body { min-height: 100%; background: #0b0f1a; color: var(--text); }

        /* Scrollbars */
        body::-webkit-scrollbar { width: 6px; }
        body::-webkit-scrollbar-thumb { background: var(--surface-3); border-radius: 4px; }

        /* Status badge */
        .badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 3px 10px; border-radius: 20px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.04em;
            font-family: 'DM Sans', sans-serif;
        }
        .badge-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
        .badge-recu  { background: rgba(234,179,8,0.15);  color: #facc15; }
        .badge-prep  { background: rgba(59,130,246,0.15); color: #60a5fa; }
        .badge-pret  { background: rgba(16,185,129,0.15); color: #34d399; }
        .badge-servi { background: rgba(107,114,128,0.15);color: #9ca3af; }
        .badge-annule{ background: rgba(239,68,68,0.15);  color: #f87171; }

        /* Order card */
        .order-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px 20px;
            transition: border-color 0.15s;
        }
        .order-card:hover { border-color: rgba(245,158,11,0.25); }

        /* Pagination */
        .page-link {
            display: inline-flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            border: 1px solid var(--border);
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.15s;
        }
        .page-link:hover { border-color: rgba(245,158,11,0.4); color: var(--amber); }
        .page-link.active { background: var(--amber-dim); border-color: rgba(245,158,11,0.4); color: var(--amber); }
        .page-link.disabled { opacity: 0.3; pointer-events: none; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .order-card { animation: fadeUp 0.2s ease both; }
    </style>
</head>
<body>

    {{-- ── Top bar ─────────────────────────────────────────────── --}}
    <header style="background: #0b0f1a; border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 20;" class="flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <div style="width:36px;height:36px;border-radius:10px;background:var(--amber-dim);border:1px solid rgba(245,158,11,0.3);" class="flex items-center justify-center">
                <svg style="width:18px;height:18px;color:var(--amber);" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-display" style="font-size:14px;font-weight:700;color:var(--text);line-height:1.2;">Mes commandes du jour</p>
                <p style="font-size:11px;color:var(--text-muted);">{{ $serveur->name }} · {{ now()->translatedFormat('d M Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Serveur badge --}}
            <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:10px;" class="flex items-center gap-2 px-3 py-1.5">
                <div style="width:24px;height:24px;border-radius:50%;background:var(--amber);display:flex;align-items:center;justify-content:center;">
                    <span style="font-family:'DM Sans',sans-serif;font-weight:800;font-size:11px;color:#0b0f1a;">{{ strtoupper(substr($serveur->name, 0, 1)) }}</span>
                </div>
                <span style="font-size:12px;font-weight:500;color:var(--text);">{{ $serveur->name }}</span>
            </div>

            <a href="{{ route('kds', $tenant->slug) }}"
               style="display:flex;align-items:center;gap:6px;padding:7px 14px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;color:var(--text-muted);font-size:12px;font-weight:500;text-decoration:none;transition:color .15s;"
               onmouseover="this.style.color='var(--amber)'" onmouseout="this.style.color='var(--text-muted)'">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21a48.25 48.25 0 0 1-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                KDS
            </a>
            <a href="{{ route('serveur.commande.index', $tenant->slug) }}"
               style="background:var(--amber);border-radius:10px;color:#0b0f1a;font-size:12px;font-weight:700;text-decoration:none;padding:7px 14px;display:flex;align-items:center;gap:6px;transition:background 0.15s;"
               onmouseover="this.style.background='#fcd34d'"
               onmouseout="this.style.background='var(--amber)'">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nouvelle commande
            </a>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" style="background:transparent;border:1px solid var(--border);border-radius:10px;color:var(--text-muted);font-size:12px;padding:6px 12px;cursor:pointer;" class="flex items-center gap-1.5 transition-colors hover:border-red-500/30 hover:text-red-400">
                    <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                    </svg>
                    Sortir
                </button>
            </form>
        </div>
    </header>

    {{-- ── Flash messages ─────────────────────────────────────── --}}
    @if(session('success'))
    <div style="background:#064e3b;border:1px solid #059669;color:#a7f3d0;padding:12px 20px;font-size:13px;font-weight:500;text-align:center;">
        ✓ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#7f1d1d;border:1px solid #dc2626;color:#fca5a5;padding:12px 20px;font-size:13px;font-weight:500;text-align:center;">
        ✗ {{ session('error') }}
    </div>
    @endif

    {{-- ── Content ─────────────────────────────────────────────── --}}
    <main style="max-width: 860px; margin: 0 auto; padding: 32px 24px;">

        {{-- Stats row --}}
        <div class="grid grid-cols-3 gap-4 mb-8">
            @php
                $total     = $orders->total();
                $active    = $orders->getCollection()->filter(fn($o) => in_array($o->status, ['RECU','PREP','PRET']))->count();
                $completed = $orders->getCollection()->filter(fn($o) => $o->status === 'SERVI')->count();
            @endphp
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px 20px;">
                <p style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;" class="font-display">Total</p>
                <p class="font-display" style="font-size:28px;font-weight:800;color:var(--text);">{{ $total }}</p>
            </div>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px 20px;">
                <p style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;" class="font-display">En cours</p>
                <p class="font-display" style="font-size:28px;font-weight:800;color:#60a5fa;">{{ $active }}</p>
            </div>
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:16px;padding:16px 20px;">
                <p style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;" class="font-display">Servis</p>
                <p class="font-display" style="font-size:28px;font-weight:800;color:#34d399;">{{ $completed }}</p>
            </div>
        </div>

        {{-- Orders list --}}
        @if($orders->isEmpty())
            <div style="background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:64px 24px;text-align:center;">
                <svg style="width:48px;height:48px;color:var(--surface-3);margin:0 auto 12px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/>
                </svg>
                <p style="font-size:14px;color:var(--text-muted);">Aucune commande pour l'instant</p>
                <p style="font-size:12px;color:var(--surface-3);margin-top:4px;">Les commandes que vous passez apparaîtront ici</p>
            </div>
        @else
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($orders as $index => $order)
                <div class="order-card" style="animation-delay: {{ $index * 0.04 }}s;">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
                                <p class="font-display" style="font-size:15px;font-weight:700;color:var(--text);">{{ $order->order_number }}</p>
                                @php
                                    $statusClass = match($order->status) {
                                        'RECU'   => 'badge-recu',
                                        'PREP'   => 'badge-prep',
                                        'PRET'   => 'badge-pret',
                                        'SERVI'  => 'badge-servi',
                                        default  => 'badge-annule',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">
                                    <span class="badge-dot"></span>
                                    {{ $order->getStatusLabel() }}
                                </span>
                            </div>
                            <p style="font-size:12px;color:var(--text-muted);">
                                <span style="color:var(--text);">{{ $order->table?->label ?? $order->table?->code ?? 'Sans table' }}</span>
                                <span style="margin:0 6px;opacity:0.3;">·</span>
                                {{ $order->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        <p class="font-display" style="font-size:18px;font-weight:800;color:var(--amber);flex-shrink:0;margin-left:16px;">{{ $order->getFormattedTotal() }}</p>
                    </div>

                    {{-- Items --}}
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($order->items as $item)
                        <div style="background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:4px 10px;display:flex;align-items:center;gap:6px;">
                            <span style="width:18px;height:18px;background:var(--surface-3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;font-size:10px;font-weight:800;color:var(--text);flex-shrink:0;">{{ $item->quantity }}</span>
                            <span style="font-size:12px;color:var(--text-muted);">{{ $item->dish?->name ?? 'Plat supprimé' }}</span>
                        </div>
                        @endforeach
                    </div>

                    @if($order->notes)
                    <div style="margin-top:10px;padding:8px 12px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.15);border-radius:10px;display:flex;align-items:flex-start;gap:8px;">
                        <svg style="width:13px;height:13px;color:var(--amber);flex-shrink:0;margin-top:1px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/>
                        </svg>
                        <p style="font-size:12px;color:var(--text-muted);font-style:italic;">{{ $order->notes }}</p>
                    </div>
                    @endif

                    {{-- Pied de carte : paiement + encaissement --}}
                    <div style="margin-top:12px;padding-top:10px;border-top:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        {{-- Statut paiement --}}
                        @if($order->payment_status === 'PAID')
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(16,185,129,0.12);color:#34d399;">
                                    <span style="width:5px;height:5px;border-radius:50%;background:currentColor;flex-shrink:0;"></span>
                                    Encaissé
                                </span>
                                @if($order->collected_by_name)
                                    <span style="font-size:11px;color:var(--text-muted);">par {{ $order->collected_by_name }}</span>
                                @endif
                            </div>
                        @else
                            <span style="font-size:11px;color:var(--text-muted);">Non encaissé</span>
                        @endif

                        {{-- Bouton encaisser (visible si commande pas payée et pas annulée) --}}
                        @if($order->payment_status !== 'PAID' && $order->status !== 'ANNULE')
                        <form method="POST"
                              action="{{ route('serveur.encaisser', [$tenant->slug, $order->id]) }}"
                              onsubmit="return confirm('Encaisser la commande {{ $order->order_number }} ({{ $order->getFormattedTotal() }}) ?')"
                              style="margin:0;">
                            @csrf
                            <button type="submit"
                                    style="display:flex;align-items:center;gap:5px;padding:6px 14px;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.25);border-radius:9px;color:#34d399;font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;"
                                    onmouseover="this.style.background='rgba(16,185,129,0.22)'"
                                    onmouseout="this.style.background='rgba(16,185,129,0.12)'">
                                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>
                                </svg>
                                Encaisser
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
            <div style="margin-top:28px;display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;">
                {{-- Previous --}}
                @if($orders->onFirstPage())
                    <span class="page-link disabled">
                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" class="page-link">
                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Page numbers --}}
                @foreach($orders->getUrlRange(max(1, $orders->currentPage() - 2), min($orders->lastPage(), $orders->currentPage() + 2)) as $page => $url)
                    @if($page == $orders->currentPage())
                        <span class="page-link active">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-link">{{ $page }}</a>
                    @endif
                @endforeach

                {{-- Next --}}
                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" class="page-link">
                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                    </a>
                @else
                    <span class="page-link disabled">
                        <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                    </span>
                @endif

                <span style="font-size:12px;color:var(--text-muted);margin-left:8px;">
                    Page {{ $orders->currentPage() }} / {{ $orders->lastPage() }}
                    · <span style="color:var(--text);">{{ $orders->total() }} commandes</span>
                </span>
            </div>
            @endif
        @endif
    </main>

</body>
</html>
