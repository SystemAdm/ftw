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
            $table->foreignId('guardian_id')->nullable()->change();
            $table->string('pending_contact')->nullable()->after('relationship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guardian_user', function (Blueprint $table) {
            $table->foreignId('guardian_id')->nullable(false)->change();
            $table->dropColumn('pending_contact');
        });
    }
};
