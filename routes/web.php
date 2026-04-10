<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ComptourController;
use App\Http\Controllers\SuiviController;
use App\Http\Controllers\MenuImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Hiérarchie des rôles:
| - SUPER_ADMIN: Accès total à tous les tenants
| - ADMIN: Gestion complète de son tenant
| - CAISSIER: POS, paiements, commandes
| - CHEF: KDS, préparation des commandes
| - SERVEUR: Commandes, tables, service
| - CLIENT: Menu public, commandes
|
*/

// Health check endpoints (no auth required)
Route::get('/health', [HealthController::class, 'index'])->name('health');
Route::get('/ping', [HealthController::class, 'ping'])->name('ping');

Route::get('/', function () {
    return redirect()->route('home');
})->middleware('auth');

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Onboarding dismiss (auth only)
Route::post('/admin/onboarding/dismiss', function () {
    session(['onboarding_dismissed' => true]);
    return response()->json(['ok' => true]);
})->middleware('auth')->name('admin.onboarding.dismiss');

// =============================================================================
// SUPER ADMIN - Accès global à tous les tenants
// =============================================================================
Route::middleware(['auth', 'role:SUPER_ADMIN'])->group(function () {
    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::resource('/superadmin/tenants', TenantController::class, ['as' => 'superadmin']);
    Route::resource('/superadmin/users', UserController::class, ['as' => 'superadmin']);
});

// =============================================================================
// ADMIN - Gestion complète du tenant (menu, stats, users, etc.)
// =============================================================================
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::prefix('/admin/{tenantSlug}')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminMenuController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/dashboard-stats', [AdminMenuController::class, 'statistics'])->name('admin.dashboard.stats');

        // Menus
        Route::get('/menus', [AdminMenuController::class, 'menus'])->name('admin.menus');
        Route::post('/menus', [AdminMenuController::class, 'storeMenu'])->name('admin.menus.store');
        Route::patch('/menus/{menu}', [AdminMenuController::class, 'updateMenu'])->name('admin.menus.update');
        Route::delete('/menus/{menu}', [AdminMenuController::class, 'destroyMenu'])->name('admin.menus.destroy');

        // Categories
        Route::get('/menus/{menuId}/categories', [AdminMenuController::class, 'categories'])->name('admin.categories');
        Route::post('/menus/{menuId}/categories', [AdminMenuController::class, 'storeCategory'])->name('admin.categories.store');

        // Dishes
        Route::get('/categories/{categoryId}/dishes', [AdminMenuController::class, 'dishes'])->name('admin.dishes');
        Route::get('/dishes/{dishId}', [AdminMenuController::class, 'getDish'])->name('admin.dishes.show');
        Route::post('/categories/{categoryId}/dishes', [AdminMenuController::class, 'storeDish'])->name('admin.dishes.store');
        Route::put('/dishes/{dishId}', [AdminMenuController::class, 'updateDish'])->name('admin.dishes.update');
        Route::delete('/dishes/{dishId}', [AdminMenuController::class, 'destroyDish'])->name('admin.dishes.destroy');
        Route::post('/dishes/{dishId}/toggle', [AdminMenuController::class, 'toggleDish'])->name('admin.dishes.toggle');
        Route::post('/dishes/{dishId}/photo', [AdminMenuController::class, 'uploadDishPhoto'])->name('admin.dishes.upload-photo');
        Route::delete('/dishes/{dishId}/photo', [AdminMenuController::class, 'deleteDishPhoto'])->name('admin.dishes.delete-photo');

        // Import Excel
        Route::get('/import', [MenuImportController::class, 'index'])->name('admin.menu.import');
        Route::post('/import', [MenuImportController::class, 'import'])->name('admin.menu.import.store');
        Route::get('/import/template', [MenuImportController::class, 'template'])->name('admin.menu.import.template');

        // Tables
        Route::patch('/tables/{id}/toggle', [TableController::class, 'toggle'])->name('admin.tables.toggle');
        Route::post('/tables/generate', [TableController::class, 'generate'])->name('admin.tables.generate');
        Route::resource('/tables', TableController::class, ['as' => 'admin']);

        // QR Codes
        Route::get('/qrcodes/download-all-pdf', [QrCodeController::class, 'downloadAllPdf'])->name('admin.qrcodes.download-all-pdf');
        Route::resource('/qrcodes', QrCodeController::class, ['as' => 'admin']);


        // Statistics (Admin only)
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('admin.statistics');
        Route::get('/statistics/chart-data', [StatisticsController::class, 'chartData'])->name('admin.statistics.chartData');

        // Reports
        Route::get('/reports', [ExportController::class, 'index'])->name('admin.reports');

        // Reservations
        Route::get('/reservations', [ReservationController::class, 'index'])->name('admin.reservations.index');
        Route::get('/reservations/calendar', [ReservationController::class, 'calendar'])->name('admin.reservations.calendar');
        Route::get('/reservations/create', [ReservationController::class, 'create'])->name('admin.reservations.create');
        Route::post('/reservations', [ReservationController::class, 'store'])->name('admin.reservations.store');
        Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('admin.reservations.show');
        Route::get('/reservations/{reservation}/edit', [ReservationController::class, 'edit'])->name('admin.reservations.edit');
        Route::put('/reservations/{reservation}', [ReservationController::class, 'update'])->name('admin.reservations.update');
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy'])->name('admin.reservations.destroy');
        Route::post('/reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('admin.reservations.confirm');
        Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('admin.reservations.cancel');
        Route::post('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('admin.reservations.complete');
        Route::post('/reservations/{reservation}/no-show', [ReservationController::class, 'noShow'])->name('admin.reservations.noShow');

        // Reviews (Admin)
        Route::get('/reviews', [ReviewController::class, 'index'])->name('admin.reviews.index');
        Route::get('/reviews/{review}', [ReviewController::class, 'show'])->name('admin.reviews.show');
        Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('admin.reviews.approve');
        Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('admin.reviews.reject');
        Route::post('/reviews/{review}/respond', [ReviewController::class, 'respond'])->name('admin.reviews.respond');
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('admin.reviews.destroy');

        // Exports (CSV)
        Route::get('/exports/orders', [ExportController::class, 'exportOrders'])->name('admin.exports.orders');
        Route::get('/exports/menu', [ExportController::class, 'exportMenu'])->name('admin.exports.menu');
        Route::get('/exports/reservations', [ExportController::class, 'exportReservations'])->name('admin.exports.reservations');
        Route::get('/exports/reviews', [ExportController::class, 'exportReviews'])->name('admin.exports.reviews');

        // Exports (PDF)
        Route::get('/exports/orders-pdf', [ExportController::class, 'exportOrdersPDF'])->name('admin.exports.orders.pdf');
        Route::get('/exports/statistics-pdf', [ExportController::class, 'exportStatisticsPDF'])->name('admin.exports.statistics.pdf');
        Route::get('/exports/menu-pdf', [ExportController::class, 'exportMenuPDF'])->name('admin.exports.menu.pdf');

        // Exports (Excel)
        Route::get('/exports/orders-excel', [ExportController::class, 'exportOrdersExcel'])->name('admin.exports.orders.excel');
        Route::get('/exports/statistics-excel', [ExportController::class, 'exportStatisticsExcel'])->name('admin.exports.statistics.excel');

        // Audit Logs (Admin only)
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');
        Route::get('/audit-logs/export', [AuditLogController::class, 'export'])->name('admin.audit-logs.export');
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('admin.audit-logs.show');

        // Staff Management (Admin can manage CAISSIER, CHEF, SERVEUR for their restaurant)
        Route::get('/staff', [App\Http\Controllers\AdminStaffController::class, 'index'])->name('admin.staff.index');
        Route::get('/staff/create', [App\Http\Controllers\AdminStaffController::class, 'create'])->name('admin.staff.create');
        Route::post('/staff', [App\Http\Controllers\AdminStaffController::class, 'store'])->name('admin.staff.store');
        Route::get('/staff/{user}', [App\Http\Controllers\AdminStaffController::class, 'show'])->name('admin.staff.show');
        Route::get('/staff/{user}/edit', [App\Http\Controllers\AdminStaffController::class, 'edit'])->name('admin.staff.edit');
        Route::put('/staff/{user}', [App\Http\Controllers\AdminStaffController::class, 'update'])->name('admin.staff.update');
        Route::delete('/staff/{user}', [App\Http\Controllers\AdminStaffController::class, 'destroy'])->name('admin.staff.destroy');
    });
});

// =============================================================================
// CAISSIER + ADMIN - Paiements, Comptoir, Suivi
// =============================================================================
Route::middleware(['auth', 'role:ADMIN,CAISSIER'])->group(function () {
    Route::prefix('/caisse/{tenantSlug}')->group(function () {
        // Payments Management
        Route::get('/payments', [PaymentController::class, 'index'])->name('caisse.payments.index');
        Route::get('/payments/stats', [PaymentController::class, 'stats'])->name('caisse.payments.stats');
        Route::get('/payments/unpaid', [PaymentController::class, 'unpaidOrders'])->name('caisse.payments.unpaid');
        Route::get('/payments/order/{order}', [PaymentController::class, 'getOrderForPayment'])->name('caisse.payments.order');
        Route::post('/payments/order/{order}/pay', [PaymentController::class, 'processPayment'])->name('caisse.payments.process');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('caisse.payments.receipt');

        // Orders (view and update status)
        Route::get('/orders', [OrderController::class, 'index'])->name('caisse.orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('caisse.orders.show');
        Route::post('/orders/{order}/progress', [OrderController::class, 'progress'])->name('caisse.orders.progress');

        // Print Routes
        Route::get('/print/order/{order}/receipt', [PrintController::class, 'receipt'])->name('caisse.print.receipt');
        Route::get('/print/daily-report', [PrintController::class, 'dailyReport'])->name('caisse.print.daily-report');
    });

    // Routes admin : Comptoir + Suivi (accessibles par ADMIN et CAISSIER)
    Route::prefix('/admin/{tenantSlug}')->group(function () {
        // ── Comptoir (prise de commande en présentiel)
        Route::get('/comptoir', [ComptourController::class, 'index'])->name('admin.comptoir.index');
        Route::post('/comptoir/order', [ComptourController::class, 'store'])->name('admin.comptoir.store');
        Route::get('/comptoir/receipt/{order}', [ComptourController::class, 'receipt'])->name('admin.comptoir.receipt');

        // ── Suivi des commandes (board kanban)
        Route::get('/suivi', [SuiviController::class, 'index'])->name('admin.suivi.index');
        Route::get('/suivi/data', [SuiviController::class, 'data'])->name('admin.suivi.data');
        Route::post('/suivi/{order}/progress', [SuiviController::class, 'progress'])->name('admin.suivi.progress');
        Route::post('/suivi/{order}/cancel', [SuiviController::class, 'cancel'])->name('admin.suivi.cancel');

        // Orders Management
        Route::get('/orders', [OrderController::class, 'index'])->name('admin.orders.index');
        // Notification endpoint – must be BEFORE /orders/{order} to avoid route conflict
        Route::get('/orders-notify', function (string $tenantSlug) {
            $tenant = \App\Models\Tenant::where('slug', $tenantSlug)->firstOrFail();
            return response()->json([
                'latest_id'     => \App\Models\Order::where('tenant_id', $tenant->id)->max('id') ?? 0,
                'pending_count' => \App\Models\Order::where('tenant_id', $tenant->id)
                                        ->where('status', 'EN_ATTENTE')->count(),
            ]);
        })->name('admin.orders.notify');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/orders/{order}/progress', [OrderController::class, 'progress'])->name('admin.orders.progress');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');

        // Payments Management
        Route::get('/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
        Route::get('/payments/stats', [PaymentController::class, 'stats'])->name('admin.payments.stats');
        Route::get('/payments/unpaid', [PaymentController::class, 'unpaidOrders'])->name('admin.payments.unpaid');
        Route::get('/payments/order/{order}', [PaymentController::class, 'getOrderForPayment'])->name('admin.payments.order');
        Route::post('/payments/order/{order}/pay', [PaymentController::class, 'processPayment'])->name('admin.payments.process');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('admin.payments.receipt');

        // Print Routes
        Route::get('/print/order/{order}/kitchen', [PrintController::class, 'kitchenTicket'])->name('admin.print.kitchen');
        Route::get('/print/order/{order}/receipt', [PrintController::class, 'receipt'])->name('admin.print.receipt');
        Route::get('/print/daily-report', [PrintController::class, 'dailyReport'])->name('admin.print.daily-report');
    });
});

// =============================================================================
// KDS - Kitchen Display System (ADMIN, CHEF, SERVEUR)
// =============================================================================
Route::middleware(['auth', 'role:ADMIN,CHEF,SERVEUR'])->group(function () {
    Route::get('/kds/{tenantSlug}', [OrderController::class, 'kds'])->name('kds');
    Route::get('/api/orders/tenant/{tenantSlug}', [OrderController::class, 'getOrdersByTenant'])->name('orders.byTenant');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});

// =============================================================================
// ROUTES PUBLIQUES - Clients
// =============================================================================

// Menu client
Route::get('/menu', function () {
    return view('menu-client');
})->name('menu');

Route::get('/menu/{tenantId}/{tableId}', [AdminMenuController::class, 'showMenu'])->name('menu.client');
Route::post('/order/{tenantId}/{tableId}', [OrderController::class, 'store'])->name('order.store');

// QR Code page publique pour impression
Route::get('/qrcode/{tenantId}/{tableCode}', [QrCodeController::class, 'publicShow'])->name('qrcode.show');
Route::get('/qrcode/generate/{tenantId}/{tableCode}', [QrCodeController::class, 'generate'])->name('qrcode.generate');
Route::get('/qrcode/{tenantId}/{tableCode}/pdf', [QrCodeController::class, 'downloadPdf'])->name('qrcode.pdf');

// Réservations publiques (rate limited)
Route::get('/reservation/{tenantSlug}', [ReservationController::class, 'publicForm'])->name('reservation.form');
Route::post('/reservation/{tenantSlug}', [ReservationController::class, 'publicStore'])
    ->middleware('throttle:reservations')
    ->name('reservation.store');
Route::get('/reservation/{tenantSlug}/confirmation/{code}', [ReservationController::class, 'confirmation'])->name('reservation.confirmation');
Route::get('/api/reservation/{tenantSlug}/availability', [ReservationController::class, 'checkAvailability'])->name('reservation.availability');

// Avis publics (rate limited)
Route::get('/review/{tenantSlug}', [ReviewController::class, 'publicForm'])->name('review.form');
Route::post('/review/{tenantSlug}', [ReviewController::class, 'publicStore'])
    ->middleware('throttle:reviews')
    ->name('review.store');
Route::get('/reviews/{tenantSlug}', [ReviewController::class, 'publicList'])->name('reviews.public');
