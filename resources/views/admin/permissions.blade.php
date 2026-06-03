@extends('layouts.admin')

@section('title', 'Permissions des rôles')
@section('page-title', 'Permissions des rôles')

@push('head')
<style>
.toggle-btn { transition: background 0.2s; }
.toggle-btn[data-on="true"] { background:#10b981; }
.toggle-btn[data-on="false"] { background:#d1d5db; }
.toggle-thumb { transition: transform 0.2s; }
.toggle-btn[data-on="true"] .toggle-thumb { transform: translateX(20px); }
</style>
@endpush

@section('content')
<div class="mb-5 flex items-start justify-between gap-4 flex-wrap">
    <div class="flex-1">
        <p class="text-sm text-gray-500 mt-0.5">
            Configurez les permissions pour chaque rôle de votre restaurant. Les cases marquées
            <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded">global</span>
            reflètent la configuration du Super Admin.
        </p>
    </div>
    <form method="POST" action="{{ route('admin.permissions.reset', $tenant->slug) }}" onsubmit="return confirm('Supprimer toutes vos personnalisations et revenir aux valeurs globales ?')">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Réinitialiser mes permissions
        </button>
    </form>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Légende --}}
<div class="flex flex-wrap gap-3 mb-5 text-xs text-gray-500 items-center">
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded-full bg-emerald-500"></div> Activé
    </div>
    <div class="flex items-center gap-1.5">
        <div class="w-4 h-4 rounded-full bg-gray-300"></div> Désactivé
    </div>
    <div class="flex items-center gap-1.5">
        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded">votre config</span> Personnalisé par vous
    </div>
    <div class="flex items-center gap-1.5">
        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-700 rounded">global</span> Défini par le Super Admin
    </div>
    <div class="flex items-center gap-1.5">
        <span class="px-1.5 py-0.5 text-[10px] text-gray-300">défaut</span> Valeur système
    </div>
</div>

{{-- Matrice permissions --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 font-semibold text-gray-700 w-64">Permission</th>
                    @foreach($roles as $role)
                    <th class="px-4 py-3 font-semibold text-center min-w-[120px]">
                        <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold {{ $role->badgeClass() }}">
                            {{ $role->label() }}
                        </span>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($catalog as $group => $permissions)
                <tr class="bg-slate-50/60">
                    <td colspan="{{ count($roles) + 1 }}" class="px-5 py-2">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">{{ $group }}</span>
                    </td>
                </tr>
                @foreach($permissions as $permKey => $permLabel)
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-800">{{ $permLabel }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $permKey }}</div>
                    </td>
                    @foreach($roles as $role)
                    @php
                        $key = $role->value.'|'.$permKey;
                        $tenantOverride = $tenantOverrides->get($key);
                        $globalOverride = $globalOverrides->get($key);
                        $enumDefault = in_array($permKey, $role->permissions());

                        if ($tenantOverride) {
                            $isOn = $tenantOverride->enabled;
                            $source = 'tenant';
                        } elseif ($globalOverride) {
                            $isOn = $globalOverride->enabled;
                            $source = 'global';
                        } else {
                            $isOn = $enumDefault;
                            $source = 'default';
                        }
                    @endphp
                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <button
                                type="button"
                                class="toggle-btn relative inline-flex h-6 w-11 items-center rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-1"
                                data-on="{{ $isOn ? 'true' : 'false' }}"
                                data-role="{{ $role->value }}"
                                data-permission="{{ $permKey }}"
                                data-toggle-url="{{ route('admin.permissions.toggle', $tenant->slug) }}"
                                data-reset-url="{{ route('admin.permissions.reset-permission', $tenant->slug) }}"
                                data-source="{{ $source }}"
                                onclick="toggleAdminPermission(this)"
                                title="{{ $isOn ? 'Actif' : 'Inactif' }}"
                            >
                                <span class="toggle-thumb pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-lg ring-0 ml-1"></span>
                            </button>
                            @if($source === 'tenant')
                            <span class="badge-label text-[9px] font-bold bg-blue-100 text-blue-700 px-1 rounded">votre config</span>
                            @elseif($source === 'global')
                            <span class="badge-label text-[9px] font-bold bg-amber-100 text-amber-700 px-1 rounded">global</span>
                            @else
                            <span class="badge-label text-[9px] text-gray-300">défaut</span>
                            @endif
                        </div>
                    </td>
                    @endforeach
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700 flex gap-2">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
    </svg>
    <div>
        Vos modifications s'appliquent uniquement à <strong>{{ $tenant->name }}</strong> et prennent le dessus sur les valeurs globales.
    </div>
</div>

@push('scripts')
<script>
async function toggleAdminPermission(btn) {
    const isOn = btn.dataset.on === 'true';
    const newState = !isOn;

    btn.dataset.on = newState.toString();
    btn.style.background = newState ? '#10b981' : '#d1d5db';

    const label = btn.nextElementSibling;

    try {
        const res = await fetch(btn.dataset.toggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                role: btn.dataset.role,
                permission: btn.dataset.permission,
                enabled: newState,
            }),
        });
        if (!res.ok) throw new Error('Erreur serveur');

        btn.dataset.source = 'tenant';
        if (label) {
            label.textContent = 'votre config';
            label.className = 'badge-label text-[9px] font-bold bg-blue-100 text-blue-700 px-1 rounded';
        }
    } catch (e) {
        btn.dataset.on = isOn.toString();
        btn.style.background = isOn ? '#10b981' : '#d1d5db';
        alert('Erreur lors de la mise à jour.');
    }
}

document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.style.background = btn.dataset.on === 'true' ? '#10b981' : '#d1d5db';
});
</script>
@endpush
@endsection
