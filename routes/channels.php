<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * Private channel for tenant-specific events.
 * Only users belonging to the tenant can listen.
 */
Broadcast::channel('tenant.{tenantId}', function (User $user, int $tenantId) {
    // Super admin can access all tenants
    if ($user->hasRole('SUPER_ADMIN')) {
        return true;
    }

    // User must belong to the tenant
    return $user->tenant_id === $tenantId;
});

/**
 * Private channel for KDS (Kitchen Display System).
 * Only users with kitchen access can listen.
 */
Broadcast::channel('kds.{tenantId}', function (User $user, int $tenantId) {
    // Super admin can access all tenants
    if ($user->hasRole('SUPER_ADMIN')) {
        return true;
    }

    // User must belong to the tenant and have kitchen access
    if ($user->tenant_id !== $tenantId) {
        return false;
    }

    // Roles with KDS access: ADMIN, CHEF, SERVEUR
    return in_array($user->role, ['ADMIN', 'CHEF', 'SERVEUR']);
});

/**
 * Public channel for menu updates (dish availability).
 * Anyone can listen to menu changes.
 */
Broadcast::channel('menu.{tenantId}', function () {
    // Public channel - no authentication required
    return true;
});

/**
 * Private channel for order tracking by table.
 * Used for customer-facing order status updates.
 */
Broadcast::channel('table.{tableId}', function (User $user = null, int $tableId) {
    // Allow anonymous access for customers tracking their orders
    return true;
});

/**
 * Private channel for specific order updates.
 * Used for real-time order status tracking.
 */
Broadcast::channel('order.{orderId}', function (User $user = null, int $orderId) {
    // Allow access for order tracking
    return true;
});
