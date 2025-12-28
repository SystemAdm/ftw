<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('phone_number_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phone_number_id')->constrained('phone_numbers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Extra pivot attributes
            $table->boolean('primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->foreignIdFor(\App\Models\User::class, 'verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['phone_number_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_number_user');
    }
};
