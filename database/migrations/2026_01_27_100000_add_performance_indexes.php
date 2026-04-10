<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if an index exists (compatible SQLite, MySQL, PostgreSQL)
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("SELECT name FROM sqlite_master WHERE type='index' AND name=?", [$indexName]);
            return !empty($result);
        }

        if ($driver === 'mysql') {
            $result = DB::select(
                "SELECT COUNT(*) as cnt FROM information_schema.statistics WHERE table_schema=DATABASE() AND table_name=? AND index_name=?",
                [$table, $indexName]
            );
            return $result[0]->cnt > 0;
        }

        if ($driver === 'pgsql') {
            $result = DB::select(
                "SELECT COUNT(*) as cnt FROM pg_indexes WHERE tablename=? AND indexname=?",
                [$table, $indexName]
            );
            return $result[0]->cnt > 0;
        }

        return false;
    }

    /**
     * Safely create an index, skipping if columns don't exist or index already exists.
     */
    private function safeIndex(string $table, array $columns, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // Index already exists or column missing — skip silently
        }
    }

    public function up(): void
    {
        // Orders
        $this->safeIndex('orders', ['tenant_id', 'created_at'], 'orders_tenant_created_idx');
        $this->safeIndex('orders', ['tenant_id', 'status'], 'orders_tenant_status_idx');
        $this->safeIndex('orders', ['table_id', 'status'], 'orders_table_status_idx');
        $this->safeIndex('orders', ['tenant_id', 'status', 'created_at'], 'orders_tenant_status_created_idx');
        $this->safeIndex('orders', ['tenant_id', 'payment_status'], 'orders_tenant_payment_idx');
        $this->safeIndex('orders', ['tenant_id', 'created_at', 'payment_status'], 'orders_tenant_date_payment_idx');

        // Dishes
        $this->safeIndex('dishes', ['tenant_id', 'category_id'], 'dishes_tenant_category_idx');
        $this->safeIndex('dishes', ['tenant_id', 'active'], 'dishes_tenant_active_idx');
        $this->safeIndex('dishes', ['category_id', 'active'], 'dishes_category_active_idx');

        // Order items
        $this->safeIndex('order_items', ['order_id', 'dish_id'], 'order_items_order_dish_idx');
        $this->safeIndex('order_items', ['dish_id', 'created_at'], 'order_items_dish_created_idx');

        // Users
        $this->safeIndex('users', ['tenant_id', 'email'], 'users_tenant_email_idx');
        $this->safeIndex('users', ['tenant_id', 'role'], 'users_tenant_role_idx');

        // Tables
        $this->safeIndex('tables', ['tenant_id', 'is_active'], 'tables_tenant_active_idx');

        // Categories
        $this->safeIndex('categories', ['menu_id', 'position'], 'categories_menu_position_idx');

        // Menus
        $this->safeIndex('menus', ['tenant_id', 'active'], 'menus_tenant_active_idx');

        // Waiter calls
        if (Schema::hasTable('waiter_calls')) {
            $this->safeIndex('waiter_calls', ['tenant_id', 'status'], 'waiter_calls_tenant_status_idx');
            $this->safeIndex('waiter_calls', ['table_id', 'status', 'created_at'], 'waiter_calls_table_status_idx');
        }

        // Reservations
        if (Schema::hasTable('reservations')) {
            $this->safeIndex('reservations', ['tenant_id', 'reservation_date'], 'reservations_tenant_date_idx');
            $this->safeIndex('reservations', ['tenant_id', 'status'], 'reservations_tenant_status_idx');
        }

        // Payments
        if (Schema::hasTable('payments')) {
            $this->safeIndex('payments', ['order_id', 'payment_method'], 'payments_order_method_idx');
        }
    }

    public function down(): void
    {
        $drop = function (string $table, string $indexName) {
            if (!$this->indexExists($table, $indexName)) return;
            try {
                Schema::table($table, fn (Blueprint $t) => $t->dropIndex($indexName));
            } catch (\Exception $e) {}
        };

        $drop('orders', 'orders_tenant_created_idx');
        $drop('orders', 'orders_tenant_status_idx');
        $drop('orders', 'orders_table_status_idx');
        $drop('orders', 'orders_tenant_status_created_idx');
        $drop('orders', 'orders_tenant_payment_idx');
        $drop('orders', 'orders_tenant_date_payment_idx');
        $drop('dishes', 'dishes_tenant_category_idx');
        $drop('dishes', 'dishes_tenant_active_idx');
        $drop('dishes', 'dishes_category_active_idx');
        $drop('order_items', 'order_items_order_dish_idx');
        $drop('order_items', 'order_items_dish_created_idx');
        $drop('users', 'users_tenant_email_idx');
        $drop('users', 'users_tenant_role_idx');
        $drop('tables', 'tables_tenant_active_idx');
        $drop('categories', 'categories_menu_position_idx');
        $drop('menus', 'menus_tenant_active_idx');

        if (Schema::hasTable('waiter_calls')) {
            $drop('waiter_calls', 'waiter_calls_tenant_status_idx');
            $drop('waiter_calls', 'waiter_calls_table_status_idx');
        }

        if (Schema::hasTable('reservations')) {
            $drop('reservations', 'reservations_tenant_date_idx');
            $drop('reservations', 'reservations_tenant_status_idx');
        }

        if (Schema::hasTable('payments')) {
            $drop('payments', 'payments_order_method_idx');
        }
    }
};
