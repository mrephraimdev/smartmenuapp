<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\Tenant;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    public function index($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $themes = Theme::where('is_active', true)->get();
        return view('admin.themes.index', compact('themes', 'tenant'));
    }

    /**
     * Show theme selection interface.
     */
    public function select($tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $themes = Theme::where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $currentTheme = $tenant->theme;

        return view('admin.themes.select', compact('themes', 'tenant', 'currentTheme'));
    }

    /**
     * Apply a theme to the tenant.
     */
    public function apply($tenantSlug, Theme $theme)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();

        // Update tenant's theme
        $tenant->update(['theme_id' => $theme->id]);

        // Invalidate cache
        $this->cacheService->forgetTheme($tenant->id);
        $this->cacheService->forgetMenu($tenant->id);

        return redirect()
            ->route('admin.themes.select', $tenantSlug)
            ->with('success', "Le thème \"{$theme->name}\" a été appliqué avec succès.");
    }

    public function show($tenantSlug, Theme $theme)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        return view('admin.themes.show', compact('theme', 'tenant'));
    }

    public function assignToTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id'
        ]);

        $tenant->update(['theme_id' => $request->theme_id]);

        // Invalidate cache
        $this->cacheService->forgetTheme($tenant->id);

        return redirect()->back()->with('success', 'Thème appliqué avec succès au tenant.');
    }

    /**
     * Preview a theme with sample menu data.
     */
    public function preview($tenantSlug, Theme $theme)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        return view('admin.themes.preview', compact('theme', 'tenant'));
    }

    // API methods for client-side theme loading
    public function getTheme(Tenant $tenant)
    {
        $theme = $tenant->getTheme();

        return response()->json([
            'theme' => [
                'name' => $theme->name,
                'colors' => $theme->colors,
                'fonts' => $theme->fonts,
                'category' => $theme->category
            ]
        ]);
    }

    public function getAvailableThemes(Request $request)
    {
        $category = $request->get('category');
        $query = Theme::active();

        if ($category) {
            $query->byCategory($category);
        }

        $themes = $query->get(['id', 'name', 'slug', 'description', 'colors', 'fonts', 'category']);

        return response()->json(['themes' => $themes]);
    }
}
