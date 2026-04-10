<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Statut de paiement
            $table->string('payment_status', 20)->default('PENDING')->after('status');
            // PENDING, PAID, PARTIAL, REFUNDED, CANCELLED

            // Montant payé (peut être différent du total si paiement partiel)
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');

            // Date du paiement complet
            $table->timestamp('paid_at')->nullable()->after('paid_amount');

            // Index pour les filtres
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['payment_status']);
            $table->dropColumn(['payment_status', 'paid_amount', 'paid_at']);
        });
    }
};
