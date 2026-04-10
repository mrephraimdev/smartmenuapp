<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SQLite doesn't support ALTER COLUMN, so we recreate the users table
     * with the updated CHECK constraint that includes CAISSIER.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return; // MySQL handles this via the existing migration
        }

        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE "users_new" (
                "id" integer primary key autoincrement not null,
                "name" varchar not null,
                "username" varchar,
                "email" varchar not null,
                "email_verified_at" datetime,
                "password" varchar not null,
                "remember_token" varchar,
                "created_at" datetime,
                "updated_at" datetime,
                "tenant_id" integer,
                "role" varchar check ("role" in (\'SUPER_ADMIN\', \'ADMIN\', \'CHEF\', \'SERVEUR\', \'CAISSIER\')) not null default \'SERVEUR\',
                foreign key("tenant_id") references "tenants"("id") on delete cascade
            )
        ');

        DB::statement('
            INSERT INTO "users_new"
                ("id","name","username","email","email_verified_at","password","remember_token","created_at","updated_at","tenant_id","role")
            SELECT
                "id","name","username","email","email_verified_at","password","remember_token","created_at","updated_at","tenant_id","role"
            FROM "users"
        ');

        DB::statement('DROP TABLE "users"');
        DB::statement('ALTER TABLE "users_new" RENAME TO "users"');

        // Restore unique index on email and username
        DB::statement('CREATE UNIQUE INDEX "users_email_unique" ON "users" ("email")');
        DB::statement('CREATE UNIQUE INDEX "users_username_unique" ON "users" ("username")');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // Not reversible without data loss risk — leave as-is
    }
};
