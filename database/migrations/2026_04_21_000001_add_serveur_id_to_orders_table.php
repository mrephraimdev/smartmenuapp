<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'serveur_id')) {
                $table->unsignedBigInteger('serveur_id')->nullable()->after('pos_session_id');
                $table->foreign('serveur_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'serveur_id')) {
                $table->dropForeign(['serveur_id']);
                $table->dropColumn('serveur_id');
            }
        });
    }
};
