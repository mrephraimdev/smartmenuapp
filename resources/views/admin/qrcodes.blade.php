@extends('layouts.admin')

@section('title', 'QR Codes')
@section('page-title', 'QR Codes')
@section('breadcrumb')
    <a href="{{ route('admin.dashboard', $tenant->slug) }}" class="hover:text-indigo-600">Dashboard</a>
    <span class="mx-2">/</span>
    <span>QR Codes</span>
@endsection

@section('content')
<div x-data="qrcodesStore()">
    <!-- Actions Bar -->
    <x-ui.card class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
                <x-heroicon-o-qr-code class="w-6 h-6 text-indigo-500" />
                <span class="text-gray-600">Générez et imprimez les QR codes pour vos tables</span>
            </div>
            <div class="flex flex-wrap gap-3">
                <x-ui.button variant="primary" @click="printAllQRCodes()">
                    <x-heroicon-o-printer class="w-5 h-5 mr-2" />
                    Imprimer tous
                </x-ui.button>
                <x-ui.button variant="success" @click="downloadAllQRCodes()">
                    <x-heroicon-o-arrow-down-tray class="w-5 h-5 mr-2" />
                    Télécharger PDF
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <!-- QR Codes Grid -->
    @if(count($qrCodes) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($qrCodes as $qrCode)
                <x-ui.card hover class="group">
                    <!-- Table Header -->
                    <div class="text-center mb-4">
                        <div class="w-12 h-12 mx-auto bg-indigo-100 rounded-xl flex items-center justify-center mb-2 group-hover:bg-indigo-200 transition-colors">
                            <x-heroicon-o-qr-code class="w-6 h-6 text-indigo-600" />
                        </div>
                        <h3 class="font-bold text-lg text-gray-800">{{ $qrCode['table']->label }}</h3>
                        <p class="text-sm text-gray-500">Code: {{ $qrCode['table']->code }}</p>
                    </div>

                    <!-- QR Code Image -->
                    <div class="text-center mb-4">
                        <div class="inline-block p-4 bg-white border-2 border-gray-100 rounded-xl shadow-inner">
                            <img src="{{ url('/qrcode/generate/' . $tenant->id . '/' . $qrCode['table']->code) }}"
                                 alt="QR Code Table {{ $qrCode['table']->code }}"
                                 class="w-32 h-32">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex space-x-2 mb-3">
                        <a href="{{ $qrCode['url'] }}" target="_blank"
                           class="flex-1 bg-indigo-50 text-indigo-700 text-center py-2.5 rounded-lg hover:bg-indigo-100 font-medium transition-colors text-sm">
                            <x-heroicon-o-eye class="w-4 h-4 inline mr-1" />
                            Voir
                        </a>
                        <button @click="printSingleQRCode('{{ $qrCode['table']->code }}')"
                                class="flex-1 bg-green-50 text-green-700 text-center py-2.5 rounded-lg hover:bg-green-100 font-medium transition-colors text-sm">
                            <x-heroicon-o-printer class="w-4 h-4 inline mr-1" />
                            Imprimer
                        </button>
                    </div>

                    <!-- URL Details -->
                    <details class="group/details">
                        <summary class="text-xs text-gray-500 cursor-pointer hover:text-indigo-600 flex items-center justify-center space-x-1">
                            <x-heroicon-o-link class="w-3 h-3" />
                            <span>Afficher l'URL</span>
                        </summary>
                        <div class="mt-2 p-2 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-600 break-all font-mono">{{ $qrCode['menu_url'] }}</p>
                            <button @click="copyToClipboard('{{ $qrCode['menu_url'] }}')"
                                    class="mt-2 w-full text-xs text-indigo-600 hover:text-indigo-700 font-medium flex items-center justify-center space-x-1">
                                <x-heroicon-o-clipboard-document class="w-3 h-3" />
                                <span>Copier</span>
                            </button>
                        </div>
                    </details>
                </x-ui.card>
            @endforeach
        </div>
    @else
        <x-ui.empty-state
            icon="qr-code"
            title="Aucune table trouvée"
            description="Créez d'abord des tables pour générer les QR codes."
        >
            <x-slot name="action">
                <a href="{{ route('admin.tables.index', $tenant->slug) }}">
                    <x-ui.button variant="primary">
                        <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                        Créer des tables
                    </x-ui.button>
                </a>
            </x-slot>
        </x-ui.empty-state>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('qrcodesStore', () => ({
        printSingleQRCode(tableCode) {
            const printWindow = window.open(`/qrcode/{{ $tenant->id }}/${tableCode}`, '_blank');
            printWindow.onload = function() {
                printWindow.print();
            };
        },

        printAllQRCodes() {
            const tables = @json(collect($qrCodes)->pluck('table.code'));
            let index = 0;

            const printNext = () => {
                if (index < tables.length) {
                    const printWindow = window.open(`/qrcode/{{ $tenant->id }}/${tables[index]}`, '_blank');
                    printWindow.onload = function() {
                        printWindow.print();
                        printWindow.close();
                        index++;
                        setTimeout(printNext, 1000);
                    };
                }
            };

            printNext();
        },

        downloadAllQRCodes() {
            window.location.href = '{{ route("admin.qrcodes.download-all-pdf", $tenant->slug) }}';
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('URL copiée !');
            }).catch(() => {
                alert('Erreur lors de la copie');
            });
        }
    }));
});
</script>
@endpush
@endsection
