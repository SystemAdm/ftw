<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('user_bans')) {
            Schema::create('user_bans', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(User::class, 'user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('banned_at');
                $table->timestamp('banned_to')->nullable();
                $table->foreignIdFor(User::class, 'banned_by')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reason')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_bans');
    }
};
