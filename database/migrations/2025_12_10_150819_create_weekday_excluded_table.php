<?php

use App\Models\Weekday;
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
        Schema::create('weekday_excluded', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Weekday::class)->constrained()->cascadeOnDelete();
            $table->date('excluded_date');
            $table->timestamps();
            $table->unique(['weekday_id', 'excluded_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekday_excluded');
    }
};
