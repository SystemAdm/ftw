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
        Schema::table('postal_codes', function (Blueprint $table) {
            if (Schema::hasColumn('postal_codes', 'county') && ! Schema::hasColumn('postal_codes', 'municipality')) {
                $table->renameColumn('county', 'municipality');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('postal_codes', function (Blueprint $table) {
            if (Schema::hasColumn('postal_codes', 'municipality') && ! Schema::hasColumn('postal_codes', 'county')) {
                $table->renameColumn('municipality', 'county');
            }
        });
    }
};
