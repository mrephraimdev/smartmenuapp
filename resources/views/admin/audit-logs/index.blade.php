@extends('layouts.admin')

@section('title', "Journal d'Audit")
@section('page-title', "Journal d'Audit")
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-500">Dashboard</a>
    <span class="mx-2">/</span>
    <span>Journal d'Audit</span>
@endsection

@section('content')
    {{-- Export button --}}
    <div class="flex justify-end mb-6">
        <a href="{{ route('admin.audit-logs.export', ['tenantSlug' => $tenant->slug] + request()->query()) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 text-white text-sm font-semibold rounded-xl hover:bg-amber-600 transition shadow-sm">
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            Exporter CSV
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index', $tenant->slug) }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
                           placeholder="Description, utilisateur...">
                </div>

                {{-- Action Filter --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">Action</label>
                    <select name="action" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Toutes les actions</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Entity Type Filter --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">Type d'entite</label>
                    <select name="entity_type" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Tous les types</option>
                        @foreach($entityTypes as $type)
                        <option value="App\Models\{{ $type }}" {{ request('entity_type') === "App\Models\\{$type}" ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- User Filter --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">Utilisateur</label>
                    <select name="user_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Tous les utilisateurs</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Date From --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">Date de debut</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>

                {{-- Date To --}}
                <div>
                    <label class="block text-base font-semibold text-gray-700 mb-1">Date de fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                </div>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.audit-logs.index', $tenant->slug) }}"
                   class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition">
                    Reinitialiser
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    {{-- Audit Logs Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50/80">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Entite</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">IP</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($auditLogs as $log)
                <tr class="hover:bg-amber-50/40 transition-colors cursor-pointer"
                    onclick="window.location='{{ route('admin.audit-logs.show', [$tenant->slug, $log]) }}'">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>{{ $log->created_at->format('d/m/Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-amber-100 flex items-center justify-center mr-3">
                                <span class="text-sm font-medium text-amber-700">
                                    {{ $log->user ? strtoupper(substr($log->user->name, 0, 1)) : 'S' }}
                                </span>
                            </div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $log->user?->name ?? 'Systeme' }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            @switch($log->action)
                                @case('created') bg-green-100 text-green-800 @break
                                @case('updated') bg-blue-100 text-blue-800 @break
                                @case('deleted') bg-red-100 text-red-800 @break
                                @case('restored') bg-purple-100 text-purple-800 @break
                                @case('status_changed') bg-yellow-100 text-yellow-800 @break
                                @case('login') bg-teal-100 text-teal-800 @break
                                @case('logout') bg-gray-100 text-gray-800 @break
                                @default bg-gray-100 text-gray-800
                            @endswitch
                        ">
                            {{ $log->action_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->entity_type_label }}</div>
                        <div class="text-xs text-gray-400">#{{ $log->entity_id }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900 max-w-xs truncate" title="{{ $log->description }}">
                            {{ $log->description ?? '-' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $log->ip_address ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <x-heroicon-o-document-text class="w-12 h-12 mx-auto text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun log trouve</h3>
                        <p class="mt-1 text-sm text-gray-500">Les actions seront enregistrees ici.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($auditLogs->hasPages())
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            {{ $auditLogs->links() }}
        </div>
        @endif
    </div>

    {{-- Stats Summary --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
            <div class="text-sm text-gray-500">Total des actions</div>
            <div class="text-2xl font-bold text-gray-900">{{ $auditLogs->total() }}</div>
        </div>
        <div class="bg-green-50 rounded-2xl shadow-sm border border-green-100 p-4">
            <div class="text-sm text-green-600">Creations</div>
            <div class="text-2xl font-bold text-green-900">
                {{ $auditLogs->where('action', 'created')->count() }}
            </div>
        </div>
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100 p-4">
            <div class="text-sm text-blue-600">Modifications</div>
            <div class="text-2xl font-bold text-blue-900">
                {{ $auditLogs->where('action', 'updated')->count() }}
            </div>
        </div>
        <div class="bg-red-50 rounded-2xl shadow-sm border border-red-100 p-4">
            <div class="text-sm text-red-600">Suppressions</div>
            <div class="text-2xl font-bold text-red-900">
                {{ $auditLogs->where('action', 'deleted')->count() }}
            </div>
        </div>
    </div>
@endsection
