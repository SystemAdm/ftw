<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'banned_to')) {
                $table->timestamp('banned_to')->nullable()->after('banned_at');
            }
            if (! Schema::hasColumn('users', 'ban_reason')) {
                // In case older DBs don't have it yet
                $table->string('ban_reason')->nullable()->after('banned_by');
            }
        });

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
        if (Schema::hasTable('user_bans')) {
            Schema::dropIfExists('user_bans');
        }
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'banned_to')) {
                $table->dropColumn('banned_to');
            }
            // Keep ban_reason column, do not drop as it may exist from original migration
        });
    }
};
