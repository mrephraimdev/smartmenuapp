<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Audit logging for Phase 2 - tracking all critical actions
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('action', 50); // created, updated, deleted, restored, login, logout
            $table->string('entity_type', 100); // App\Models\Dish, App\Models\Order, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('changed_fields')->nullable(); // List of fields that changed
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable(); // GET, POST, PUT, DELETE
            $table->text('description')->nullable(); // Human-readable description
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['tenant_id', 'created_at'], 'audit_logs_tenant_created_idx');
            $table->index(['entity_type', 'entity_id'], 'audit_logs_entity_idx');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
            $table->index(['action', 'created_at'], 'audit_logs_action_created_idx');
            $table->index('created_at', 'audit_logs_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
