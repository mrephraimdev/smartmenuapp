<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Table;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function getMenu(Request $request)
    {
        $request->validate([
            'tenant' => 'required|exists:tenants,id',
            'table' => 'required|exists:tables,code'
        ]);

        $tenant = Tenant::findOrFail($request->tenant);
        $table = Table::where('code', $request->table)
                     ->where('tenant_id', $request->tenant)
                     ->firstOrFail();

        $menu = $tenant->menus()
                      ->with(['categories.dishes' => function($query) {
                          $query->where('active', true);
                      }, 'categories.dishes.variants', 'categories.dishes.options'])
                      ->where('active', true)
                      ->first();

        return response()->json([
            'success' => true,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'branding' => $tenant->branding,
                'type' => $tenant->type
            ],
            'table' => $table,
            'menu' => $menu
        ]);
    }
}