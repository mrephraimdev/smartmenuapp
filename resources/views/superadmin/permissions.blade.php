@extends('layouts.superadmin')

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
<div class="mb-5 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <p class="text-sm text-gray-500 mt-0.5">Définissez les permissions par défaut pour chaque rôle. Ces valeurs s'appliquent à tous les restaurants sauf si un admin a configuré des permissions spécifiques.</p>
    </div>
    <form method="POST" action="{{ route('superadmin.permissions.reset') }}" onsubmit="return confirm('Remettre toutes les permissions aux valeurs par défaut du système ?')">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 border border-red-200 rounded-xl hover:bg-red-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
            </svg>
            Réinitialiser aux défauts
        </button>
    </form>
</div>

@if(session('success'))
<div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Légende des rôles --}}
<div class="flex flex-wrap gap-2 mb-5">
    @foreach($roles as $role)
    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $role->badgeClass() }}">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
        </svg>
        {{ $role->label() }}
    </span>
    @endforeach
</div>

{{-- Matrice permissions --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-5 py-3 font-semibold text-gray-700 w-64">Permission</th>
                    @foreach($roles as $role)
                    <th class="px-4 py-3 font-semibold text-center min-w-[110px]">
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
                @php
                    $isLastInGroup = $loop->last;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="font-medium text-gray-800">{{ $permLabel }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $permKey }}</div>
                    </td>
                    @foreach($roles as $role)
                    @php
                        $key = $role->value.'|'.$permKey;
                        $override = $overrides->get($key);
                        $enumDefault = in_array($permKey, $role->permissions());
                        $isOn = $override ? $override->enabled : $enumDefault;
                        $isCustom = $override !== null;
                    @endphp
                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col items-center gap-1">
                            <button
                                type="button"
                                class="toggle-btn relative inline-flex h-6 w-11 items-center rounded-full cursor-pointer focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-1"
                                data-on="{{ $isOn ? 'true' : 'false' }}"
                                data-role="{{ $role->value }}"
                                data-permission="{{ $permKey }}"
                                data-url="{{ route('superadmin.permissions.toggle') }}"
                                onclick="togglePermission(this)"
                                title="{{ $isOn ? 'Actif' : 'Inactif' }}{{ $isCustom ? ' (personnalisé)' : ' (défaut)' }}"
                            >
                                <span class="toggle-thumb pointer-events-none inline-block h-4 w-4 rounded-full bg-white shadow-lg ring-0 ml-1"></span>
                            </button>
                            @if($isCustom)
                            <span class="text-[9px] text-violet-500 font-semibold">personnalisé</span>
                            @else
                            <span class="text-[9px] text-gray-300">défaut</span>
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

<div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 flex gap-2">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
    </svg>
    <div>
        <strong>Note :</strong> Les permissions marquées "personnalisé" écrasent les valeurs par défaut du système. Les admins de chaque restaurant peuvent aussi configurer leurs propres permissions. L'ordre de priorité est : <strong>tenant &gt; global &gt; défaut système</strong>.
    </div>
</div>

@push('scripts')
<script>
async function togglePermission(btn) {
    const isOn = btn.dataset.on === 'true';
    const newState = !isOn;

    btn.dataset.on = newState.toString();
    btn.style.background = newState ? '#10b981' : '#d1d5db';

    try {
        const res = await fetch(btn.dataset.url, {
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

        const label = btn.nextElementSibling;
        if (label) {
            label.textContent = 'personnalisé';
            label.className = 'text-[9px] text-violet-500 font-semibold';
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
