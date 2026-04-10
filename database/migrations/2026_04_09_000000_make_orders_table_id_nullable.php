<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Rend orders.table_id nullable pour permettre les commandes au comptoir
 * (sans table assignée : vente directe, emporter, etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite ne supporte pas ALTER COLUMN.
            // On recrée la table avec table_id nullable.
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement('
                CREATE TABLE orders_new (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    tenant_id INTEGER NOT NULL,
                    table_id INTEGER NULL,
                    status varchar NOT NULL DEFAULT \'RECU\',
                    total numeric NOT NULL DEFAULT \'0\',
                    notes TEXT NULL,
                    created_at datetime NULL,
                    updated_at datetime NULL,
                    order_number varchar NULL,
                    deleted_at datetime NULL,
                    pos_session_id INTEGER NULL,
                    payment_status varchar NOT NULL DEFAULT \'PENDING\',
                    paid_amount numeric NOT NULL DEFAULT \'0\',
                    paid_at datetime NULL,
                    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
                    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE CASCADE
                )
            ');

            DB::statement('
                INSERT INTO orders_new
                    (id, tenant_id, table_id, status, total, notes,
                     created_at, updated_at, order_number, deleted_at,
                     pos_session_id, payment_status, paid_amount, paid_at)
                SELECT
                    id, tenant_id, table_id, status, total, notes,
                    created_at, updated_at, order_number, deleted_at,
                    pos_session_id, payment_status, paid_amount, paid_at
                FROM orders
            ');

            DB::statement('DROP TABLE orders');
            DB::statement('ALTER TABLE orders_new RENAME TO orders');

            DB::statement('PRAGMA foreign_keys = ON');

        } elseif ($driver === 'mysql') {
            Schema::table('orders', function (Blueprint $table) {
                $table->foreignId('table_id')->nullable()->change();
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE orders ALTER COLUMN table_id DROP NOT NULL');
        }
    }

    public function down(): void
    {
        // Ne pas remettre NOT NULL en rollback : risque de casser les commandes comptoir existantes.
    }
};
