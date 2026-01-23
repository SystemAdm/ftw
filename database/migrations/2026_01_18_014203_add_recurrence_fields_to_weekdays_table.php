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
        Schema::table('weekdays', function (Blueprint $table) {
            $table->string('week_type')->default('all')->after('weekday'); // all, odd, even
            $table->string('month_occurrence')->default('all')->after('week_type'); // all, first, second, third, fourth, last
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekdays', function (Blueprint $table) {
            $table->dropColumn(['week_type', 'month_occurrence']);
        });
    }
};
