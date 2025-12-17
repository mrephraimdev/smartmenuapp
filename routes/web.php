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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Routes pour les super administrateurs
Route::middleware(['auth', 'role:SUPER_ADMIN'])->group(function () {
    Route::get('/superadmin/dashboard', [SuperAdminController::class, 'dashboard'])->name('superadmin.dashboard');
    Route::resource('/superadmin/tenants', TenantController::class, ['as' => 'superadmin']);
    Route::resource('/superadmin/users', UserController::class, ['as' => 'superadmin']);
});

// Routes pour les administrateurs
Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::prefix('/admin/{tenantId}')->group(function () {
        Route::get('/dashboard', [AdminMenuController::class, 'dashboard'])->name('admin.dashboard');
        Route::resource('/categories', \App\Http\Controllers\CategoryController::class, ['as' => 'admin']);
        Route::resource('/dishes', \App\Http\Controllers\DishController::class, ['as' => 'admin']);
        Route::resource('/menus', AdminMenuController::class, ['as' => 'admin']);
        Route::resource('/tables', TableController::class, ['as' => 'admin']);
        Route::resource('/qrcodes', QrCodeController::class, ['as' => 'admin']);
        Route::resource('/themes', ThemeController::class, ['as' => 'admin']);
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('admin.statistics');
    });
});

// Routes publiques pour les clients
Route::get('/menu/{tenantId}/{tableId}', [AdminMenuController::class, 'showMenu'])->name('menu.client');
Route::post('/order/{tenantId}/{tableId}', [OrderController::class, 'store'])->name('order.store');

// Routes pour les commandes (KDS - Kitchen Display System)
Route::middleware(['auth'])->group(function () {
    Route::get('/kds/{tenantSlug}', [OrderController::class, 'kds'])->name('kds');
    Route::get('/api/orders/tenant/{tenantSlug}', [OrderController::class, 'getOrdersByTenant'])->name('orders.byTenant');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
});
