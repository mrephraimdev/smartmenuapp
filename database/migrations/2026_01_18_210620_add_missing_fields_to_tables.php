<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ajouter des champs manquants à la table tenants
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'cover_url')) {
                $table->string('cover_url')->nullable()->after('logo_url');
            }
        });

        // Ajouter des champs manquants à la table tables
        Schema::table('tables', function (Blueprint $table) {
            if (!Schema::hasColumn('tables', 'qr_code_url')) {
                $table->string('qr_code_url')->nullable()->after('capacity');
            }
        });

        // Ajouter des champs manquants à la table dishes
        Schema::table('dishes', function (Blueprint $table) {
            if (!Schema::hasColumn('dishes', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('dishes', 'photo_url')) {
                $table->string('photo_url')->nullable()->after('description');
            }
            if (!Schema::hasColumn('dishes', 'allergens')) {
                $table->json('allergens')->nullable()->after('photo_url')->comment('Liste des allergènes (arachides, gluten, lactose, etc.)');
            }
            if (!Schema::hasColumn('dishes', 'tags')) {
                $table->json('tags')->nullable()->after('allergens')->comment('Tags nutritionnels (végétarien, vegan, sans gluten, halal, etc.)');
            }
            if (!Schema::hasColumn('dishes', 'stock_quantity')) {
                $table->integer('stock_quantity')->nullable()->after('active')->comment('Quantité en stock (null = illimité)');
            }
            if (!Schema::hasColumn('dishes', 'preparation_time_minutes')) {
                $table->integer('preparation_time_minutes')->nullable()->after('stock_quantity')->comment('Temps de préparation estimé en minutes');
            }
        });

        // Ajouter un champ order_number unique aux commandes
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'order_number')) {
                $table->string('order_number')->unique()->nullable()->after('id')->comment('Numéro de commande unique (ex: 20260118-TENANT-0001)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['cover_url']);
        });

        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn(['qr_code_url']);
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'photo_url', 'allergens', 'tags', 'stock_quantity', 'preparation_time_minutes']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_number']);
        });
    }
};
