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
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Cashier

            // Session identifiers
            $table->string('session_number')->unique(); // e.g., POS-20260128-001
            $table->enum('status', ['OPEN', 'CLOSED'])->default('OPEN');

            // Session timing
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();

            // Cash management
            $table->decimal('opening_float', 10, 2)->default(0); // Cash given at opening
            $table->decimal('expected_cash', 10, 2)->default(0); // Calculated expected cash
            $table->decimal('actual_cash', 10, 2)->nullable(); // Counted cash at closing
            $table->decimal('cash_difference', 10, 2)->nullable(); // Difference (short/over)

            // Session totals
            $table->decimal('total_sales', 10, 2)->default(0); // Total revenue
            $table->integer('total_orders')->default(0); // Number of orders
            $table->integer('total_items')->default(0); // Total items sold

            // Payment method breakdown
            $table->decimal('cash_sales', 10, 2)->default(0);
            $table->decimal('card_sales', 10, 2)->default(0);
            $table->decimal('mobile_sales', 10, 2)->default(0);

            // Cancellations and refunds
            $table->integer('cancelled_orders')->default(0);
            $table->decimal('refunds_total', 10, 2)->default(0);

            // Notes
            $table->text('opening_notes')->nullable();
            $table->text('closing_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'opened_at']);
            $table->index('session_number');
        });

        // Add pos_session_id to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('pos_session_id')->nullable()->after('tenant_id')->constrained('pos_sessions')->onDelete('set null');
            $table->index('pos_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['pos_session_id']);
            $table->dropColumn('pos_session_id');
        });

        Schema::dropIfExists('pos_sessions');
    }
};
