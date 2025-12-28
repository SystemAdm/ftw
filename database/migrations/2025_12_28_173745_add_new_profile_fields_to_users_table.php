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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('name_public')->default(true)->after('name');
            $table->string('birthday_visibility')->default('off')->after('birthday');
            $table->text('about')->nullable()->after('postal_code');
            $table->string('header_image')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name_public', 'birthday_visibility', 'about', 'header_image']);
        });
    }
};
