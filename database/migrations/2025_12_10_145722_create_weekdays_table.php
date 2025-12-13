<?php

use App\Models\Location;
use App\Models\Team;
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
        Schema::create('weekdays', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->smallInteger('weekday')->comment('1 = Monday, 0 = Sunday');
            $table->foreignIdFor(Team::class)->nullable();
            $table->boolean('active')->default(true);
            $table->foreignIdFor(Location::class)->nullable();
            $table->date('event_start')->nullable();
            $table->date('event_end')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekdays');
    }
};
