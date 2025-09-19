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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            // Reference postal_codes.postal_code (custom PK)
            $table->unsignedBigInteger('postal_code');
            $table->foreign('postal_code')->references('postal_code')->on('postal_codes');
            $table->string('name');
            $table->boolean('active')->default(false);
            $table->text('description')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('google_maps_url')->nullable();
            $table->text('images')->nullable();
            $table->string('street_address')->nullable();
            $table->string('street_number')->nullable();
            $table->string('link')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
