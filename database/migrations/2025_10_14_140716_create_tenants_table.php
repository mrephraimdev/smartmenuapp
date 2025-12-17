<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table des clients (tenants)
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url')->nullable();
            $table->json('branding')->nullable();
            $table->string('type')->default('restaurant');
            $table->string('currency')->default('FCFA');
            $table->string('locale')->default('fr');
            $table->timestamps();
        });

        // Modifier la table users existante pour ajouter tenant_id et role
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->enum('role', ['SUPER_ADMIN', 'ADMIN', 'CHEF', 'SERVEUR'])->default('SERVEUR')->after('password');
        });

        // Table des tables (pour restaurants)
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code');
            $table->string('label');
            $table->timestamps();
        });

        // Table des menus
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Table des catégories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Table des plats
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_base', 10, 2);
            $table->string('photo_url')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Table des variantes
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->timestamps();
        });

        // Table des options
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('kind')->default('toggle');
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->timestamps();
        });

        // Table des commandes
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['RECU', 'PREP', 'PRET', 'SERVI'])->default('RECU');
            $table->decimal('total', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table des items de commande
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained()->onDelete('cascade');
            $table->json('options')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('options');
        Schema::dropIfExists('variants');
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('tables');
        
        // Retirer les colonnes ajoutées à users
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role']);
        });
        
        Schema::dropIfExists('tenants');
    }
};