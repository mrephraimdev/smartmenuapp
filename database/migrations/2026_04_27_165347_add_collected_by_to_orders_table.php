<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('collected_by_id')->nullable()->constrained('users')->onDelete('set null')->after('serveur_id');
            $table->string('collected_by_name')->nullable()->after('collected_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collected_by_id');
            $table->dropColumn('collected_by_name');
        });
    }
};
