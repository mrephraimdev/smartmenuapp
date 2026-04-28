<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prise de commande — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; background: #0b0f1a; color: #f9fafb; font-family: 'DM Sans', sans-serif; overflow: hidden; }

        :root {
            --amber: #f59e0b; --amber-l: #fcd34d; --amber-dim: rgba(245,158,11,0.13);
            --s1: #0d1120; --s2: #111827; --s3: #1f2937; --s4: #374151;
            --bd: rgba(255,255,255,0.07); --txt: #f9fafb; --mut: #9ca3af;
        }

        .app  { display: flex; flex-direction: column; height: 100%; }
        .topbar { flex-shrink: 0; display: flex; align-items: center; justify-content: space-between; padding: 10px 16px; background: var(--s1); border-bottom: 1px solid var(--bd); gap: 10px; }
        .body { flex: 1; display: flex; overflow: hidden; }

        /* Sidebar desktop */
        .cat-sidebar { width: 190px; flex-shrink: 0; background: var(--s1); border-right: 1px solid var(--bd); display: flex; flex-direction: column; padding: 12px 10px; gap: 2px; overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--s4) transparent; }

        /* Category strip mobile */
        .cat-strip { display: none; flex-shrink: 0; background: var(--s1); border-bottom: 1px solid var(--bd); padding: 8px 12px; gap: 6px; overflow-x: auto; white-space: nowrap; }
        .cat-strip::-webkit-scrollbar { display: none; }

        /* Dish area */
        .dish-area { flex: 1; overflow-y: auto; padding: 16px; scrollbar-width: thin; scrollbar-color: var(--s4) transparent; }
        .dish-area::-webkit-scrollbar { width: 4px; }
        .dish-area::-webkit-scrollbar-thumb { background: var(--s4); border-radius: 4px; }

        /* Cart panel desktop */
        .cart-panel { width: 270px; flex-shrink: 0; background: var(--s1); border-left: 1px solid var(--bd); display: flex; flex-direction: column; }

        /* Cart mobile */
        .cart-fab     { display: none; position: fixed; bottom: 20px; right: 16px; z-index: 50; }
        .cart-overlay { display: none; position: fixed; inset: 0; z-index: 40; background: rgba(0,0,0,.6); }
        .cart-drawer  { display: none; position: fixed; bottom: 0; left: 0; right: 0; z-index: 45; background: var(--s2); border-top: 1px solid var(--bd); border-radius: 20px 20px 0 0; max-height: 82vh; flex-direction: column; }

        /* Category pills */
        .cpill { display: inline-flex; align-items: center; gap: 7px; padding: 8px 12px; border-radius: 10px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all .15s; color: var(--mut); border: 1px solid transparent; white-space: nowrap; background: transparent; width: 100%; text-align: left; }
        .cpill:hover { background: var(--s3); color: var(--txt); }
        .cpill.active { background: var(--amber-dim); color: var(--amber); border-color: rgba(245,158,11,.28); }
        .cpill .dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

        /* Dish card */
        .dcard { background: var(--s2); border: 1px solid var(--bd); border-radius: 14px; cursor: pointer; transition: all .15s; overflow: hidden; position: relative; }
        .dcard:hover { border-color: rgba(245,158,11,.35); transform: translateY(-1px); }
        .dcard.in-cart { border-color: var(--amber); }
        .dcard.in-cart::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg,rgba(245,158,11,.06),transparent 60%); pointer-events: none; }
        .dcard-badge { position: absolute; top: 8px; right: 8px; width: 21px; height: 21px; border-radius: 50%; background: var(--amber); color: #0b0f1a; font-size: 10px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
        .dcard-body { padding: 12px; }
        .dcard-img   { width: 100%; aspect-ratio: 16/9; object-fit: cover; background: var(--s3); }
        .dcard-noimg { width: 100%; aspect-ratio: 16/9; background: var(--s3); display: flex; align-items: center; justify-content: center; }

        /* Qty */
        .qbtn   { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px; cursor: pointer; border: none; outline: none; transition: all .1s; flex-shrink: 0; }
        .qbtn-m { background: var(--s4); color: var(--mut); }
        .qbtn-m:hover { background: #4b5563; color: var(--txt); }
        .qbtn-p { background: var(--amber); color: #0b0f1a; }
        .qbtn-p:hover { background: var(--amber-l); }

        /* Cart items */
        .citem { display: flex; align-items: center; gap: 8px; padding: 9px 0; border-bottom: 1px solid var(--bd); }
        .citem:last-child { border-bottom: none; }

        /* Inputs */
        .sel { background: var(--s3); border: 1px solid var(--bd); border-radius: 10px; padding: 9px 32px 9px 12px; color: var(--txt); font-family: 'DM Sans', sans-serif; font-size: 13px; outline: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19 9-7 7-7-7'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 10px center; background-size: 14px; cursor: pointer; }
        .sel:focus { border-color: rgba(245,158,11,.5); }
        .sel option { background: #1f2937; }
        .ta  { background: var(--s3); border: 1px solid var(--bd); border-radius: 10px; padding: 9px 12px; color: var(--txt); font-family: 'DM Sans', sans-serif; font-size: 12px; width: 100%; resize: none; outline: none; }
        .ta::placeholder { color: var(--mut); }
        .ta:focus { border-color: rgba(245,158,11,.4); }

        /* Submit */
        .btn-send { width: 100%; padding: 13px; background: var(--amber); color: #0b0f1a; font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: 13px; letter-spacing: .04em; text-transform: uppercase; border-radius: 12px; border: none; cursor: pointer; transition: all .15s; }
        .btn-send:hover:not(:disabled) { background: var(--amber-l); box-shadow: 0 6px 20px rgba(245,158,11,.3); }
        .btn-send:disabled { opacity: .35; cursor: not-allowed; }

        /* Scroll util */
        .scroll { overflow-y: auto; scrollbar-width: thin; scrollbar-color: var(--s4) transparent; }
        .scroll::-webkit-scrollbar { width: 3px; }
        .scroll::-webkit-scrollbar-thumb { background: var(--s4); border-radius: 3px; }

        /* Section heading */
        .sec-heading { display: flex; align-items: baseline; gap: 8px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid var(--bd); }
        .sec-heading h2 { font-size: 13px; font-weight: 700; color: var(--amber); text-transform: uppercase; letter-spacing: .06em; }
        .sec-heading span { font-size: 11px; color: var(--mut); }

        /* Toast */
        .toast { position: fixed; top: 16px; left: 50%; transform: translateX(-50%); z-index: 100; padding: 10px 18px; border-radius: 12px; font-size: 13px; font-weight: 500; min-width: 240px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,.5); animation: slideDown .18s ease; }
        .tok  { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; }
        .terr { background: #7f1d1d; border: 1px solid #dc2626; color: #fca5a5; }
        @keyframes slideDown { from { opacity:0; transform:translateX(-50%) translateY(-8px); } to { opacity:1; transform:translateX(-50%) translateY(0); } }

        /* Responsive */
        @media (max-width: 900px) {
            .cat-sidebar { display: none; }
            .cat-strip   { display: flex; }
            body, html   { height: auto; min-height: 100%; overflow: auto; }
            .app  { height: auto; min-height: 100vh; }
            .body { overflow: visible; flex-direction: column; }
            .dish-area   { overflow: visible; padding: 12px; }
            .cart-panel  { display: none; }
            .cart-fab    { display: flex; }
            .cart-overlay.open { display: block; }
            .cart-drawer.open  { display: flex; }
            .dcard-img, .dcard-noimg { aspect-ratio: 3/1; }
        }
        @media (max-width: 480px) {
            .topbar { padding: 8px 10px; }
            .tb-hide { display: none; }
            .dish-area { padding: 10px; }
        }
    </style>
</head>
<body>

<div x-data="serveurCommande()" class="app">

    {{-- Toast --}}
    <div x-show="toast" x-cloak :class="toastOk ? 'tok' : 'terr'" class="toast" x-text="toast"
         x-transition:leave="transition duration-150" x-transition:leave-end="opacity-0"></div>

    {{-- ── Topbar ─────────────────────────────────────────── --}}
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
            <div style="width:34px;height:34px;border-radius:9px;background:var(--amber-dim);border:1px solid rgba(245,158,11,.3);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg style="width:16px;height:16px;color:var(--amber);" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2"/></svg>
            </div>
            <div>
                <p style="font-size:13px;font-weight:700;color:var(--txt);line-height:1.1;">Prise de commande</p>
                <p style="font-size:10px;color:var(--mut);" class="tb-hide">{{ $tenant->name }}</p>
            </div>
        </div>

        {{-- Table selector --}}
        <div style="flex:1;max-width:220px;margin:0 12px;">
            <select x-model="tableId" @change="onTableChange()" class="sel" style="width:100%;font-size:12px;">
                <option value="">— Choisir une table —</option>
                @foreach($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->label ?? $table->code }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">

            {{-- ── Bouton appels clients ── --}}
            <button @click="showCallsPanel = !showCallsPanel"
                    style="position:relative;display:flex;align-items:center;gap:5px;padding:6px 10px;border-radius:9px;font-size:11px;font-weight:600;border:none;cursor:pointer;transition:all .15s;"
                    :style="pendingCallsCount > 0
                        ? 'background:rgba(245,158,11,.15);border:1px solid rgba(245,158,11,.4);color:var(--amber);'
                        : 'background:var(--s3);border:1px solid var(--bd);color:var(--mut);'">
                <svg style="width:14px;height:14px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                </svg>
                <span class="tb-hide" x-text="pendingCallsCount > 0 ? pendingCallsCount + ' appel(s)' : 'Appels'"></span>
                <span x-show="pendingCallsCount > 0"
                      x-text="pendingCallsCount > 9 ? '9+' : pendingCallsCount"
                      style="position:absolute;top:-6px;right:-6px;background:#f59e0b;color:#0b0f1a;font-size:9px;font-weight:800;border-radius:50%;min-width:16px;height:16px;display:flex;align-items:center;justify-content:center;padding:0 2px;animation:pulse 2s infinite;"></span>
            </button>

            {{-- Panel appels (slide-in) --}}
            <div x-show="showCallsPanel" x-cloak @click.outside="showCallsPanel = false"
                 style="position:fixed;top:52px;right:12px;width:280px;background:var(--s2);border:1px solid var(--bd);border-radius:14px;box-shadow:0 20px 40px rgba(0,0,0,.6);z-index:200;overflow:hidden;">
                <div style="padding:12px 16px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;">
                    <p style="font-size:13px;font-weight:700;color:var(--txt);">Appels clients</p>
                    <span x-show="pendingCallsCount > 0" x-text="pendingCallsCount + ' en attente'"
                          style="font-size:11px;font-weight:700;color:var(--amber);background:rgba(245,158,11,.12);padding:2px 8px;border-radius:20px;"></span>
                </div>
                <div style="max-height:320px;overflow-y:auto;">
                    <template x-if="waiterCalls.length === 0">
                        <div style="padding:32px 16px;text-align:center;">
                            <p style="font-size:12px;color:var(--mut);">Aucun appel en cours</p>
                        </div>
                    </template>
                    <template x-for="call in waiterCalls" :key="call.id">
                        <div style="padding:12px 16px;border-bottom:1px solid var(--bd);display:flex;align-items:start;gap:10px;"
                             :style="call.status === 'PENDING' ? 'background:rgba(245,158,11,.05);' : ''">
                            <div style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;"
                                 :style="call.call_type === 'URGENCE' ? 'background:rgba(239,68,68,.15)' : call.call_type === 'QUESTION' ? 'background:rgba(245,158,11,.15)' : 'background:rgba(59,130,246,.15)'">
                                <span x-text="call.call_type === 'URGENCE' ? '🚨' : call.call_type === 'QUESTION' ? '❓' : '🔔'"></span>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:12px;font-weight:600;color:var(--txt);">Table <span x-text="call.table_name || call.table_code"></span></p>
                                <p style="font-size:11px;color:var(--mut);" x-text="call.call_type_label"></p>
                                <p style="font-size:10px;color:var(--s4);" x-text="call.time_ago"></p>
                            </div>
                            <button x-show="call.status === 'PENDING'"
                                    @click="resolveWaiterCall(call.id)"
                                    style="flex-shrink:0;font-size:11px;font-weight:700;color:#34d399;background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.2);border-radius:7px;padding:4px 8px;cursor:pointer;transition:all .15s;"
                                    onmouseover="this.style.background='rgba(52,211,153,.2)'" onmouseout="this.style.background='rgba(52,211,153,.1)'">
                                ✓ OK
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div style="display:flex;align-items:center;gap:7px;padding:5px 10px;background:var(--s3);border:1px solid var(--bd);border-radius:9px;" class="tb-hide">
                <div style="width:22px;height:22px;border-radius:50%;background:var(--amber);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="font-weight:800;font-size:10px;color:#0b0f1a;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                </div>
                <span style="font-size:12px;font-weight:500;color:var(--txt);">{{ Auth::user()->name }}</span>
            </div>
            <a href="{{ route('kds', $tenant->slug) }}"
               style="display:flex;align-items:center;gap:5px;padding:6px 10px;background:var(--s3);border:1px solid var(--bd);border-radius:9px;color:var(--mut);font-size:11px;font-weight:500;text-decoration:none;transition:color .15s;"
               onmouseover="this.style.color='var(--amber)'" onmouseout="this.style.color='var(--mut)'">
                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 0 1 4.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0 1 12 15a9.065 9.065 0 0 0-6.23-.693L5 14.5m14.8.8 1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0 1 12 21a48.25 48.25 0 0 1-8.135-.687c-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/></svg>
                <span class="tb-hide">KDS</span>
            </a>
            <a href="{{ route('serveur.historique.index', $tenant->slug) }}"
               style="display:flex;align-items:center;gap:5px;padding:6px 10px;background:var(--s3);border:1px solid var(--bd);border-radius:9px;color:var(--mut);font-size:11px;font-weight:500;text-decoration:none;transition:color .15s;"
               onmouseover="this.style.color='var(--amber)'" onmouseout="this.style.color='var(--mut)'">
                <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0z"/></svg>
                <span class="tb-hide">Historique</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" style="display:flex;align-items:center;gap:5px;padding:6px 10px;background:transparent;border:1px solid var(--bd);border-radius:9px;color:var(--mut);font-size:11px;cursor:pointer;transition:all .15s;"
                        onmouseover="this.style.color='#f87171';this.style.borderColor='rgba(239,68,68,.35)'" onmouseout="this.style.color='var(--mut)';this.style.borderColor='var(--bd)'">
                    <svg style="width:13px;height:13px;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                </button>
            </form>
        </div>
    </header>

    {{-- ── Mobile category strip ───────────────────────────── --}}
    <div class="cat-strip">
        {{-- "Tous" pill --}}
        <button @click="activeCat = null" :class="activeCat === null ? 'active' : ''" class="cpill" style="width:auto;">
            <svg style="width:11px;height:11px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
            Tous
        </button>
        @foreach($menus as $menu)
            @foreach($menu->categories as $category)
            <button @click="activeCat = {{ $category->id }}" :class="activeCat === {{ $category->id }} ? 'active' : ''" class="cpill" style="width:auto;">
                <span class="dot"></span>{{ $category->name }}
            </button>
            @endforeach
        @endforeach
    </div>

    {{-- ── Body ─────────────────────────────────────────────── --}}
    <div class="body">

        {{-- Desktop sidebar --}}
        <aside class="cat-sidebar">
            <p style="font-size:9px;font-weight:700;letter-spacing:.12em;color:var(--mut);text-transform:uppercase;padding:2px 4px 8px;">Catégories</p>

            {{-- Tous les plats --}}
            <button @click="activeCat = null" :class="activeCat === null ? 'active' : ''" class="cpill">
                <svg style="width:12px;height:12px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                <span style="flex:1;">Tous les plats</span>
                <span style="font-size:10px;opacity:.5;">{{ $menus->sum(fn($m) => $m->categories->sum(fn($c) => $c->dishes->count())) }}</span>
            </button>

            @foreach($menus as $menu)
                @if($menus->count() > 1)
                    <p style="font-size:9px;font-weight:600;letter-spacing:.08em;color:var(--mut);text-transform:uppercase;padding:8px 4px 4px;border-top:1px solid var(--bd);margin-top:4px;">{{ $menu->title }}</p>
                @endif
                @foreach($menu->categories as $category)
                <button @click="activeCat = {{ $category->id }}" :class="activeCat === {{ $category->id }} ? 'active' : ''" class="cpill">
                    <span class="dot"></span>
                    <span style="flex:1;overflow:hidden;text-overflow:ellipsis;">{{ $category->name }}</span>
                    <span style="font-size:10px;opacity:.5;flex-shrink:0;">{{ $category->dishes->count() }}</span>
                </button>
                @endforeach
            @endforeach
        </aside>

        {{-- Dish area --}}
        <main class="dish-area">
            @foreach($menus as $menu)
                @foreach($menu->categories as $category)
                {{-- show when "Tous" OR this specific category --}}
                <div x-show="activeCat === null || activeCat === {{ $category->id }}">
                    <div class="sec-heading">
                        <h2>{{ $category->name }}</h2>
                        <span>{{ $category->dishes->count() }} plat(s)</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:10px;margin-bottom:24px;">
                        @foreach($category->dishes as $dish)
                        <div class="dcard" :class="getQty({{ $dish->id }}) > 0 ? 'in-cart' : ''"
                             @click="increment({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})">
                            <span x-show="getQty({{ $dish->id }}) > 0" x-cloak class="dcard-badge" x-text="getQty({{ $dish->id }})"></span>
                            @if($dish->photo_url)
                                <img src="{{ $dish->photo_url }}" alt="{{ $dish->name }}" class="dcard-img" loading="lazy"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <div class="dcard-noimg" style="display:none;"><svg style="width:20px;height:20px;color:var(--s4);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg></div>
                            @else
                                <div class="dcard-noimg"><svg style="width:20px;height:20px;color:var(--s4);" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Z"/></svg></div>
                            @endif
                            <div class="dcard-body">
                                <p style="font-size:12px;font-weight:700;color:var(--txt);line-height:1.3;margin-bottom:3px;">{{ $dish->name }}</p>
                                @if($dish->description)
                                    <p style="font-size:10px;color:var(--mut);line-height:1.3;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;margin-bottom:5px;">{{ $dish->description }}</p>
                                @endif
                                <p style="font-size:13px;font-weight:800;color:var(--amber);">{{ number_format($dish->price_base, 0, ',', ' ') }}<span style="font-size:9px;opacity:.65;font-weight:600;margin-left:2px;">FCFA</span></p>
                                <div x-show="getQty({{ $dish->id }}) > 0" x-cloak @click.stop
                                     style="display:flex;align-items:center;justify-content:flex-end;gap:7px;margin-top:8px;">
                                    <button class="qbtn qbtn-m" style="width:24px;height:24px;font-size:14px;" @click.stop="decrement({{ $dish->id }})">−</button>
                                    <span style="font-size:13px;font-weight:700;color:var(--txt);min-width:16px;text-align:center;" x-text="getQty({{ $dish->id }})"></span>
                                    <button class="qbtn qbtn-p" style="width:24px;height:24px;font-size:14px;" @click.stop="increment({{ $dish->id }}, '{{ addslashes($dish->name) }}', {{ $dish->price_base }})">+</button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            @endforeach
        </main>

        {{-- Desktop cart --}}
        <aside class="cart-panel">
            <div style="padding:14px 16px;border-bottom:1px solid var(--bd);flex-shrink:0;display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:13px;font-weight:700;color:var(--txt);">Commande</p>
                <span x-show="items.length > 0" x-cloak style="background:var(--amber);border-radius:20px;padding:2px 9px;font-size:10px;font-weight:800;color:#0b0f1a;" x-text="items.length + ' art.'"></span>
            </div>
            <div x-show="tableId" x-cloak style="padding:7px 16px;background:rgba(245,158,11,.08);border-bottom:1px solid var(--bd);font-size:11px;color:var(--amber);">
                Table : <strong x-text="tableLabel"></strong>
            </div>
            <div class="scroll" style="flex:1;padding:10px 14px;">
                <template x-if="items.length === 0">
                    <div style="padding:32px 0;text-align:center;">
                        <svg style="width:30px;height:30px;color:var(--s4);margin:0 auto 8px;" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                        <p style="font-size:11px;color:var(--mut);">Panier vide</p>
                    </div>
                </template>
                <template x-for="item in items" :key="item.dish_id">
                    <div class="citem">
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:11px;font-weight:600;color:var(--txt);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.name"></p>
                            <p style="font-size:10px;color:var(--mut);margin-top:1px;" x-text="fmt(item.price * item.quantity) + ' FCFA'"></p>
                        </div>
                        <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
                            <button class="qbtn qbtn-m" style="width:22px;height:22px;font-size:13px;" @click="decrement(item.dish_id)">−</button>
                            <span style="font-size:12px;font-weight:700;color:var(--txt);min-width:16px;text-align:center;" x-text="item.quantity"></span>
                            <button class="qbtn qbtn-p" style="width:22px;height:22px;font-size:13px;" @click="increment(item.dish_id, item.name, item.price)">+</button>
                        </div>
                    </div>
                </template>
            </div>
            <div style="padding:12px 14px;border-top:1px solid var(--bd);flex-shrink:0;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                    <span style="font-size:12px;color:var(--mut);">Total</span>
                    <span style="font-size:18px;font-weight:800;color:var(--amber);" x-text="fmt(total) + ' FCFA'"></span>
                </div>
                <textarea x-model="notes" rows="2" placeholder="Notes cuisine..." class="ta" style="margin-bottom:10px;"></textarea>
                <button class="btn-send" @click="submit()" :disabled="items.length === 0 || !tableId || sending">
                    <span x-show="!sending">
                        <svg style="display:inline;width:13px;height:13px;margin-right:5px;vertical-align:-2px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        Envoyer en cuisine
                    </span>
                    <span x-show="sending" x-cloak>Envoi...</span>
                </button>
                <button x-show="items.length > 0" x-cloak @click="items = []"
                        style="width:100%;margin-top:7px;padding:7px;background:transparent;border:1px solid var(--bd);border-radius:9px;color:var(--mut);font-size:11px;cursor:pointer;transition:all .15s;"
                        onmouseover="this.style.color='#f87171';this.style.borderColor='rgba(239,68,68,.35)'" onmouseout="this.style.color='var(--mut)';this.style.borderColor='var(--bd)'">
                    Vider le panier
                </button>
            </div>
        </aside>
    </div>

    {{-- Mobile FAB --}}
    <div class="cart-fab">
        <button @click="cartOpen = true"
                style="display:flex;align-items:center;gap:8px;padding:12px 18px;background:var(--amber);color:#0b0f1a;font-size:13px;font-weight:700;border-radius:50px;border:none;cursor:pointer;box-shadow:0 8px 24px rgba(245,158,11,.4);">
            <svg style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
            Panier
            <span x-show="items.length > 0" x-cloak style="width:20px;height:20px;background:#0b0f1a;color:var(--amber);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;" x-text="items.length"></span>
        </button>
    </div>

    <div class="cart-overlay" :class="cartOpen ? 'open' : ''" @click="cartOpen = false"></div>

    <div class="cart-drawer" :class="cartOpen ? 'open' : ''">
        <div style="padding:12px 16px 10px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div>
                <p style="font-size:14px;font-weight:700;color:var(--txt);">Commande</p>
                <p x-show="tableId" x-cloak style="font-size:11px;color:var(--amber);margin-top:1px;">Table : <strong x-text="tableLabel"></strong></p>
            </div>
            <button @click="cartOpen = false" style="width:30px;height:30px;background:var(--s3);border:none;border-radius:8px;color:var(--mut);cursor:pointer;display:flex;align-items:center;justify-content:center;">
                <svg style="width:15px;height:15px;" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="scroll" style="flex:1;padding:10px 14px;overflow-y:auto;">
            <template x-if="items.length === 0">
                <p style="text-align:center;padding:24px 0;font-size:12px;color:var(--mut);">Panier vide</p>
            </template>
            <template x-for="item in items" :key="item.dish_id">
                <div class="citem">
                    <div style="flex:1;min-width:0;"><p style="font-size:12px;font-weight:600;color:var(--txt);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" x-text="item.name"></p><p style="font-size:10px;color:var(--mut);" x-text="fmt(item.price * item.quantity) + ' FCFA'"></p></div>
                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                        <button class="qbtn qbtn-m" style="width:26px;height:26px;" @click="decrement(item.dish_id)">−</button>
                        <span style="font-size:13px;font-weight:700;color:var(--txt);min-width:16px;text-align:center;" x-text="item.quantity"></span>
                        <button class="qbtn qbtn-p" style="width:26px;height:26px;" @click="increment(item.dish_id, item.name, item.price)">+</button>
                    </div>
                </div>
            </template>
        </div>
        <div style="padding:12px 14px;border-top:1px solid var(--bd);flex-shrink:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <span style="font-size:12px;color:var(--mut);">Total</span>
                <span style="font-size:20px;font-weight:800;color:var(--amber);" x-text="fmt(total) + ' FCFA'"></span>
            </div>
            <textarea x-model="notes" rows="2" placeholder="Notes cuisine..." class="ta" style="margin-bottom:10px;"></textarea>
            <button class="btn-send" @click="submit()" :disabled="items.length === 0 || !tableId || sending">
                <span x-show="!sending">Envoyer en cuisine</span>
                <span x-show="sending" x-cloak>Envoi...</span>
            </button>
        </div>
    </div>

</div>

<script>
function serveurCommande() {
    return {
        tableId: '',
        tableLabel: '',
        items: [],
        notes: '',
        sending: false,
        toast: '',
        toastOk: true,
        activeCat: null,
        cartOpen: false,

        // Appels serveur
        waiterCalls: [],
        pendingCallsCount: 0,
        showCallsPanel: false,
        _waiterPollInterval: null,

        tables:   @json($tables->map(fn($t) => ['id' => $t->id, 'label' => $t->label ?? $t->code])),
        storeUrl: '{{ route("serveur.commande.store", $tenant->slug) }}',
        tenantId: {{ $tenant->id }},

        init() {
            this.loadWaiterCalls();
            this._waiterPollInterval = setInterval(() => this.loadWaiterCalls(), 6000);
        },

        onTableChange() {
            const t = this.tables.find(t => t.id == this.tableId);
            this.tableLabel = t ? t.label : '';
        },

        getQty(dishId) {
            const i = this.items.find(i => i.dish_id === dishId);
            return i ? i.quantity : 0;
        },

        increment(dishId, name, price) {
            const i = this.items.find(i => i.dish_id === dishId);
            if (i) { i.quantity++; }
            else   { this.items.push({ dish_id: dishId, name, price: parseFloat(price), quantity: 1 }); }
        },

        decrement(dishId) {
            const idx = this.items.findIndex(i => i.dish_id === dishId);
            if (idx === -1) return;
            if (this.items[idx].quantity <= 1) { this.items.splice(idx, 1); }
            else { this.items[idx].quantity--; }
        },

        get total() { return this.items.reduce((s, i) => s + i.price * i.quantity, 0); },

        fmt(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n || 0)); },

        showToast(msg, ok = true) {
            this.toastOk = ok; this.toast = msg;
            setTimeout(() => { this.toast = ''; }, 4000);
        },

        async loadWaiterCalls() {
            try {
                const res = await fetch(`/api/waiter-calls?tenant_id=${this.tenantId}`, {
                    credentials: 'include',
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) {
                    this.waiterCalls = data.calls;
                    this.pendingCallsCount = data.pending_count;
                }
            } catch (e) {}
        },

        async resolveWaiterCall(callId) {
            try {
                await fetch(`/api/waiter-calls/${callId}/resolve`, {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });
                await this.loadWaiterCalls();
            } catch (e) {}
        },

        async submit() {
            if (this.items.length === 0 || !this.tableId || this.sending) return;
            this.sending = true;
            try {
                const res = await fetch(this.storeUrl, {
                    method: 'POST', credentials: 'include',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ table_id: this.tableId, notes: this.notes, items: this.items.map(i => ({ dish_id: i.dish_id, quantity: i.quantity })) }),
                });
                const data = await res.json();
                if (data.success) {
                    this.showToast(data.message || 'Commande envoyée !', true);
                    this.items = []; this.tableId = ''; this.tableLabel = ''; this.notes = ''; this.cartOpen = false;
                } else {
                    this.showToast(data.message || 'Erreur.', false);
                }
            } catch (e) { this.showToast('Erreur réseau.', false); }
            finally { this.sending = false; }
        },
    };
}
</script>

</body>
</html>
