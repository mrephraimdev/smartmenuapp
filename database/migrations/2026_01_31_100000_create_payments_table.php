<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');

            // Montant et méthode
            $table->decimal('amount', 12, 2);
            $table->string('method', 20); // CASH, CARD, ORANGE_MONEY, MTN_MOMO, MOOV_MONEY, WAVE
            $table->string('status', 20)->default('PENDING'); // PENDING, SUCCESS, FAILED, REFUNDED

            // Référence transaction (pour mobile money)
            $table->string('transaction_id')->nullable()->index();
            $table->string('external_reference')->nullable(); // Référence du provider

            // Détails du paiement
            $table->decimal('amount_received', 12, 2)->nullable(); // Montant reçu (pour cash)
            $table->decimal('change_given', 12, 2)->nullable(); // Rendu monnaie

            // Réponse du provider (pour paiements mobiles)
            $table->json('provider_response')->nullable();

            // Qui a traité le paiement
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();

            // Notes et métadonnées
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index pour les recherches
            $table->index(['tenant_id', 'created_at']);
            $table->index(['order_id', 'status']);
            $table->index(['method', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
