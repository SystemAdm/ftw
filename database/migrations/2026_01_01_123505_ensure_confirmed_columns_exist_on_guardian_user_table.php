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
        Schema::table('guardian_user', function (Blueprint $table) {
            if (! Schema::hasColumn('guardian_user', 'confirmed_guardian')) {
                $table->boolean('confirmed_guardian')->default(false)->after('relationship');
            }
            if (! Schema::hasColumn('guardian_user', 'confirmed_admin')) {
                $table->boolean('confirmed_admin')->default(false)->after('confirmed_guardian');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardian_user', function (Blueprint $table) {
            // We shouldn't necessarily drop them if they were supposed to be there,
            // but for a clean rollback:
            if (Schema::hasColumn('guardian_user', 'confirmed_guardian')) {
                $table->dropColumn('confirmed_guardian');
            }
            if (Schema::hasColumn('guardian_user', 'confirmed_admin')) {
                $table->dropColumn('confirmed_admin');
            }
        });
    }
};
