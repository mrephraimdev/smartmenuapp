@extends('layouts.admin')

@section('title', 'Import Excel — Menus & Plats')
@section('page-title', 'Import Excel')

@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-amber-600">Dashboard</a>
    <span class="mx-2">/</span>
    <a href="{{ route('admin.menus', $tenant->slug) }}" class="hover:text-amber-600">Menus & Plats</a>
    <span class="mx-2">/</span>
    <span>Import Excel</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ── Page title ───────────────────────────────────────────── --}}
    <div class="flex items-center justify-between">
        <p class="text-gray-500 text-sm">Importez des catégories et des plats depuis un fichier Excel ou CSV.</p>
        <a href="{{ route('admin.menu.import.template', $tenant->slug) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-900 text-gray-300 hover:text-white hover:bg-gray-800 text-sm font-semibold transition-colors border border-gray-700">
            <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Télécharger modèle CSV
        </a>
    </div>

    {{-- ── Flash result ─────────────────────────────────────────── --}}
    @if(session('import_result'))
        @php $result = session('import_result'); @endphp

        <div class="rounded-2xl border {{ count($result['errors']) > 0 ? 'border-amber-500/30 bg-amber-500/10' : 'border-emerald-500/30 bg-emerald-500/10' }} p-5 space-y-3">
            <div class="flex items-center gap-3">
                @if(count($result['errors']) > 0)
                    <div class="w-9 h-9 rounded-xl bg-amber-400/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-amber-400">Import terminé avec des avertissements</p>
                        <p class="text-sm text-slate-300">
                            <span class="text-emerald-400 font-medium">{{ $result['imported'] }} plat(s) importé(s)</span>
                            &nbsp;·&nbsp;
                            <span class="text-slate-400">{{ $result['skipped'] }} ignoré(s)</span>
                        </p>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-emerald-400/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-emerald-400">Import réussi</p>
                        <p class="text-sm text-slate-300">
                            <span class="text-emerald-400 font-medium">{{ $result['imported'] }} plat(s) importé(s)</span>
                            &nbsp;·&nbsp;
                            <span class="text-slate-400">{{ $result['skipped'] }} ignoré(s)</span>
                        </p>
                    </div>
                @endif
            </div>

            @if(count($result['errors']) > 0)
                <div class="rounded-xl bg-red-500/10 border border-red-500/20 p-4">
                    <p class="text-sm font-semibold text-red-400 mb-2">Erreurs rencontrées :</p>
                    <ul class="space-y-1">
                        @foreach($result['errors'] as $error)
                            <li class="text-sm text-red-300 flex items-start gap-2">
                                <svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                                {{ $error }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    {{-- ── Validation errors ────────────────────────────────────── --}}
    @if($errors->any())
        <div class="rounded-2xl border border-red-500/30 bg-red-500/10 p-5">
            <p class="text-sm font-semibold text-red-400 mb-2">Veuillez corriger les erreurs suivantes :</p>
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="text-sm text-red-300 flex items-start gap-2">
                        <svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Import form ──────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50">
            <h2 class="font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                </svg>
                Importer un fichier
            </h2>
        </div>

        <form action="{{ route('admin.menu.import.store', $tenant->slug) }}"
              method="POST"
              enctype="multipart/form-data"
              x-data="fileDropzone()"
              class="p-6 space-y-6">
            @csrf

            {{-- File drag-zone --}}
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">
                    Fichier Excel / CSV <span class="text-red-400">*</span>
                </label>

                <div
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    :class="isDragging ? 'border-amber-400 bg-amber-400/10' : 'border-slate-600 hover:border-amber-400/50 hover:bg-slate-700/30'"
                    class="relative rounded-2xl border-2 border-dashed transition-colors cursor-pointer p-8 text-center"
                    @click="$refs.fileInput.click()"
                >
                    <input
                        type="file"
                        name="file"
                        accept=".xlsx,.xls,.csv"
                        x-ref="fileInput"
                        @change="handleFileChange($event)"
                        class="sr-only"
                    >

                    <template x-if="!fileName">
                        <div class="space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-slate-700 flex items-center justify-center mx-auto">
                                <svg class="w-7 h-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-slate-300 font-medium">Glissez votre fichier ici</p>
                                <p class="text-slate-500 text-sm mt-1">ou cliquez pour parcourir</p>
                                <p class="text-slate-600 text-xs mt-2">XLSX, XLS ou CSV — max 10 Mo</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="fileName">
                        <div class="flex items-center justify-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-400/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-white font-medium text-sm" x-text="fileName"></p>
                                <p class="text-slate-400 text-xs" x-text="fileSize"></p>
                            </div>
                            <button type="button"
                                    @click.stop="clearFile()"
                                    class="ml-2 p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-400/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Menu select --}}
            <div>
                <label for="menu_id" class="block text-sm font-medium text-slate-300 mb-2">
                    Menu cible <span class="text-red-400">*</span>
                </label>
                @if($menus->isEmpty())
                    <div class="rounded-xl bg-amber-500/10 border border-amber-500/30 px-4 py-3 text-sm text-amber-300">
                        Aucun menu actif trouvé. Veuillez d'abord
                        <a href="{{ route('admin.menus', $tenant->slug) }}" class="underline hover:text-amber-400">créer un menu</a>.
                    </div>
                @else
                    <select name="menu_id" id="menu_id" required
                            class="w-full rounded-xl bg-slate-700/60 border border-slate-600 text-white px-4 py-2.5 text-sm focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400/30 transition-colors appearance-none">
                        <option value="" disabled selected>-- Choisir un menu --</option>
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}" {{ old('menu_id') == $menu->id ? 'selected' : '' }}>
                                {{ $menu->title }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.menus', $tenant->slug) }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-700 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        :disabled="!fileName"
                        :class="fileName ? 'bg-amber-400 hover:bg-amber-300 text-slate-900 cursor-pointer' : 'bg-slate-700 text-slate-500 cursor-not-allowed'"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                    </svg>
                    Importer
                </button>
            </div>
        </form>
    </div>

    {{-- ── Column format reference ──────────────────────────────── --}}
    <div class="rounded-2xl bg-slate-800/60 border border-slate-700/50 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/50">
            <h2 class="font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                </svg>
                Format des colonnes
            </h2>
        </div>

        <div class="p-6">
            <p class="text-sm text-slate-400 mb-4">
                La première ligne doit contenir les en-têtes exactement comme indiqué ci-dessous.
                Le séparateur pour CSV doit être le point-virgule <code class="bg-slate-700 text-amber-300 px-1.5 py-0.5 rounded text-xs">;</code>.
            </p>

            <div class="overflow-x-auto rounded-xl border border-slate-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-700/60 border-b border-slate-700">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Colonne</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Obligatoire</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-300 uppercase tracking-wider">Exemple</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <code class="bg-slate-700 text-amber-300 px-2 py-0.5 rounded text-xs font-mono">categorie</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-red-400">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>
                                    Oui
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">Nom de la catégorie (sera créée si elle n'existe pas)</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">Entrées</td>
                        </tr>
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <code class="bg-slate-700 text-amber-300 px-2 py-0.5 rounded text-xs font-mono">nom_plat</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-red-400">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>
                                    Oui
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">Nom du plat (ignoré si existe déjà dans la catégorie)</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">Soupe à l'oignon</td>
                        </tr>
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <code class="bg-slate-700 text-amber-300 px-2 py-0.5 rounded text-xs font-mono">description</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                                    Non
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">Description courte du plat</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">Gratinée au four</td>
                        </tr>
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <code class="bg-slate-700 text-amber-300 px-2 py-0.5 rounded text-xs font-mono">prix</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-red-400">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/></svg>
                                    Oui
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">Prix en chiffres, virgule ou point décimal acceptés. Doit être &gt; 0.</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">8.50 ou 8,50</td>
                        </tr>
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <code class="bg-slate-700 text-amber-300 px-2 py-0.5 rounded text-xs font-mono">actif</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                                    Non
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300"><code class="text-xs bg-slate-700 px-1 rounded">1</code> = visible, <code class="text-xs bg-slate-700 px-1 rounded">0</code> = masqué. Par défaut : 1.</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">1</td>
                        </tr>
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-4 py-3">
                                <code class="bg-slate-700 text-amber-300 px-2 py-0.5 rounded text-xs font-mono">image_url</code>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                                    Non
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">URL publique de l'image du plat (sera téléchargée automatiquement)</td>
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs">https://…/photo.jpg</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Tip --}}
            <div class="mt-4 flex items-start gap-3 rounded-xl bg-slate-700/30 border border-slate-700 px-4 py-3">
                <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                </svg>
                <p class="text-sm text-slate-300">
                    <span class="font-semibold text-amber-400">Astuce :</span>
                    Les catégories sont créées automatiquement si elles n'existent pas encore dans le menu cible.
                    Les plats déjà présents (même nom dans la même catégorie) sont ignorés pour éviter les doublons.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('head')
<script>
function fileDropzone() {
    return {
        isDragging: false,
        fileName: null,
        fileSize: null,

        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            if (files.length > 0) {
                this.setFile(files[0]);
                // Assign to actual input
                const dt = new DataTransfer();
                dt.items.add(files[0]);
                this.$refs.fileInput.files = dt.files;
            }
        },

        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) this.setFile(file);
        },

        setFile(file) {
            this.fileName = file.name;
            const kb = file.size / 1024;
            this.fileSize = kb >= 1024
                ? (kb / 1024).toFixed(2) + ' Mo'
                : Math.round(kb) + ' Ko';
        },

        clearFile() {
            this.fileName = null;
            this.fileSize = null;
            this.$refs.fileInput.value = '';
        }
    };
}
</script>
@endpush
