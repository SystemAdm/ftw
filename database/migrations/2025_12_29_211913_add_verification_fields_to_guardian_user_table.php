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
            if (! Schema::hasColumn('guardian_user', 'verified_user_at')) {
                $table->dateTime('verified_user_at')->nullable()->after('pending_contact');
            }
            if (! Schema::hasColumn('guardian_user', 'verified_guardian_at')) {
                $table->dateTime('verified_guardian_at')->nullable()->after('verified_user_at');
            }
            if (Schema::hasColumn('guardian_user', 'confirmed_at') && ! Schema::hasColumn('guardian_user', 'verified_at')) {
                $table->renameColumn('confirmed_at', 'verified_at');
            }
            if (Schema::hasColumn('guardian_user', 'confirmed_by') && ! Schema::hasColumn('guardian_user', 'verified_by')) {
                $table->renameColumn('confirmed_by', 'verified_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardian_user', function (Blueprint $table) {
            $table->dropColumn(['verified_user_at', 'verified_guardian_at']);
            if (Schema::hasColumn('guardian_user', 'verified_at') && ! Schema::hasColumn('guardian_user', 'confirmed_at')) {
                $table->renameColumn('verified_at', 'confirmed_at');
            }
            if (Schema::hasColumn('guardian_user', 'verified_by') && ! Schema::hasColumn('guardian_user', 'confirmed_by')) {
                $table->renameColumn('verified_by', 'confirmed_by');
            }
        });
    }
};
