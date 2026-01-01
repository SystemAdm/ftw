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
            $table->dateTime('verified_user_at')->nullable()->after('pending_contact');
            $table->dateTime('verified_guardian_at')->nullable()->after('verified_user_at');
            $table->renameColumn('confirmed_at', 'verified_at');
            $table->renameColumn('confirmed_by', 'verified_by');
            // confirmed_guardian and confirmed_admin are already there, keep them as status flags if needed or use dates.
            // The issue says: verified_user_at,verified_guardian_at,verified_at,verified_by in pivot
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardian_user', function (Blueprint $table) {
            $table->dropColumn(['verified_user_at', 'verified_guardian_at']);
            $table->renameColumn('verified_at', 'confirmed_at');
            $table->renameColumn('verified_by', 'confirmed_by');
        });
    }
};
