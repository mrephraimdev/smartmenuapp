<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add soft deletes to critical models for data recovery
     */
    public function up(): void
    {
        // Add soft deletes to dishes
        if (!Schema::hasColumn('dishes', 'deleted_at')) {
            Schema::table('dishes', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to categories
        if (!Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to menus
        if (!Schema::hasColumn('menus', 'deleted_at')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to tables
        if (!Schema::hasColumn('tables', 'deleted_at')) {
            Schema::table('tables', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to orders
        if (!Schema::hasColumn('orders', 'deleted_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to variants
        if (!Schema::hasColumn('variants', 'deleted_at')) {
            Schema::table('variants', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to options
        if (!Schema::hasColumn('options', 'deleted_at')) {
            Schema::table('options', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['dishes', 'categories', 'menus', 'tables', 'orders', 'variants', 'options'];

        foreach ($tables as $tableName) {
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
