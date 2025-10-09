<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable(); // Markdown supported content
            $table->string('image_path')->nullable(); // Uploaded or selected image path

            // Location (optional relation to locations table if exists)
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();

            // Event timing
            $table->dateTime('event_start');
            $table->dateTime('event_end')->nullable();

            // Signup
            $table->boolean('signup_needed')->default(false);
            $table->dateTime('signup_start')->nullable();
            $table->dateTime('signup_end')->nullable();

            // Age restrictions
            $table->unsignedInteger('age_min')->nullable();
            $table->unsignedInteger('age_max')->nullable();

            // Capacity
            $table->unsignedInteger('number_of_seats')->nullable();

            // Status: draft, active, or null
            $table->enum('status', ['draft', 'active'])->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('event_reservation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('event_attendee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::create('event_inside',function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reservation');
        Schema::dropIfExists('event_attendee');
        Schema::dropIfExists('event_inside');
        Schema::dropIfExists('events');
    }
};
