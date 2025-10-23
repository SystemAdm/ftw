<?php

use App\Models\User;
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
        Schema::create('guardian_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('minor_id')->constrained('users')->cascadeOnDelete();
            $table->string('relationship');
            $table->boolean('confirmed_guardian')->default(false);
            $table->boolean('confirmed_admin')->default(false);
            $table->foreignIdFor(User::class,'confirmed_by')->nullable()->constrained('users')->cascadeOnDelete()->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guardian_user');
    }
};
