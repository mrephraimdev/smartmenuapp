<?php

namespace App\Http\Controllers;

use App\Imports\MenuImport;
use App\Models\Menu;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MenuImportController extends Controller
{
    /**
     * Validate that the authenticated user has access to the given tenant.
     */
    protected function validateTenantAccess(Tenant $tenant): void
    {
        $user = auth()->user();

        if ($user->hasRole('SUPER_ADMIN')) {
            return;
        }

        if ($user->tenant_id !== $tenant->id) {
            abort(403, 'Accès refusé à ce tenant.');
        }
    }

    /**
     * Show the import form.
     */
    public function index(string $tenantSlug): View
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $menus = Menu::where('tenant_id', $tenant->id)
            ->where('active', true)
            ->orderBy('title')
            ->get();

        return view('admin.menus.import', [
            'tenant'     => $tenant,
            'menus'      => $menus,
            'tenantSlug' => $tenantSlug,
        ]);
    }

    /**
     * Handle the uploaded file and run the import.
     */
    public function import(Request $request, string $tenantSlug): RedirectResponse
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $this->validateTenantAccess($tenant);

        $request->validate([
            'file'    => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'menu_id' => ['required', 'integer', 'exists:menus,id'],
        ], [
            'file.required'    => 'Veuillez sélectionner un fichier à importer.',
            'file.file'        => 'Le fichier est invalide.',
            'file.mimes'       => 'Le fichier doit être au format XLSX, XLS ou CSV.',
            'file.max'         => 'Le fichier ne doit pas dépasser 10 Mo.',
            'menu_id.required' => 'Veuillez sélectionner un menu.',
            'menu_id.exists'   => 'Le menu sélectionné est invalide.',
        ]);

        // Verify the menu belongs to this tenant
        $menu = Menu::where('id', $request->menu_id)
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $import = new MenuImport($tenant, $menu->id);

        Excel::import($import, $request->file('file'));

        return redirect()
            ->route('admin.menu.import', $tenantSlug)
            ->with('import_result', [
                'imported' => $import->getImported(),
                'skipped'  => $import->getSkipped(),
                'errors'   => $import->getErrors(),
            ]);
    }

    /**
     * Return a downloadable CSV template with example rows.
     */
    public function template(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_menu.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens it correctly
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row — must match the normalized keys used in MenuImport
            fputcsv($handle, ['categorie', 'nom_plat', 'description', 'prix', 'actif', 'image_url'], ';');

            // Example rows
            fputcsv($handle, ['Entrées', 'Soupe à l\'oignon', 'Soupe gratinée au four avec croûtons', '8.50', '1', ''], ';');
            fputcsv($handle, ['Plats principaux', 'Steak frites', 'Steak de bœuf 200g avec frites maison', '18.00', '1', 'https://exemple.com/steak.jpg'], ';');
            fputcsv($handle, ['Desserts', 'Tiramisu', 'Dessert italien au café et mascarpone', '6.50', '0', ''], ';');

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
