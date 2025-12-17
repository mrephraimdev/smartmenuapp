<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    public function index()
    {
        $themes = Theme::active()->get();
        return view('admin.themes.index', compact('themes'));
    }

    public function show(Theme $theme)
    {
        return view('admin.themes.show', compact('theme'));
    }

    public function assignToTenant(Request $request, Tenant $tenant)
    {
        $request->validate([
            'theme_id' => 'required|exists:themes,id'
        ]);

        $tenant->update(['theme_id' => $request->theme_id]);

        return redirect()->back()->with('success', 'Thème appliqué avec succès au tenant.');
    }

    public function preview(Theme $theme)
    {
        return view('admin.themes.preview', compact('theme'));
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
