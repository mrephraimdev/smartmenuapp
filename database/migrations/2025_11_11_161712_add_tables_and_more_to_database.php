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
        // Tables
        Schema::dropIfExists('tables');
        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code')->unique(); // A01, A02, etc.
            $table->string('label'); // Table 1, Table VIP, etc.
            $table->integer('capacity')->default(4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Menus
        Schema::dropIfExists('menus');
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Catégories
        Schema::dropIfExists('categories');
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Plats
        Schema::dropIfExists('dishes');
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_base', 8, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Variantes de plats
        Schema::dropIfExists('variants');
        Schema::create('variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('extra_price', 8, 2)->default(0);
            $table->timestamps();
        });

        // Options de plats
        Schema::dropIfExists('options');
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('kind')->default('toggle'); // toggle, select, quantity
            $table->decimal('extra_price', 8, 2)->default(0);
            $table->timestamps();
        });

        // Commandes
        Schema::dropIfExists('orders');
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, confirmed, preparing, ready, served, paid
            $table->decimal('total', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Items de commande
        Schema::dropIfExists('order_items');
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('dish_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained()->onDelete('set null');
            $table->json('options')->nullable(); // Options sélectionnées
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 8, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table pivot rôles-utilisateurs
        Schema::dropIfExists('role_user');
        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->unique(['user_id', 'role_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('options');
        Schema::dropIfExists('variants');
        Schema::dropIfExists('dishes');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('tables');
        Schema::dropIfExists('role_user');
    }
};
