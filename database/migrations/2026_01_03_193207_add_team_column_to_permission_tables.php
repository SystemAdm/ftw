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
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamsKey = $columnNames['team_foreign_key'] ?? 'team_id';
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        // 1. Roles Table
        if (! Schema::hasColumn($tableNames['roles'], $teamsKey)) {
            Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamsKey) {
                $table->unsignedBigInteger($teamsKey)->nullable()->after('id');
                $table->index($teamsKey, 'roles_team_foreign_key_index');

                $table->dropUnique(['name', 'guard_name']);
                $table->unique([$teamsKey, 'name', 'guard_name']);
            });
        }

        // 2. Model Has Roles Table
        if (! Schema::hasColumn($tableNames['model_has_roles'], $teamsKey)) {
            Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $teamsKey, $modelKey) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign([config('permission.column_names.role_pivot_key') ?? 'role_id']);
                }

                $table->unsignedBigInteger($teamsKey)->default(0)->after(config('permission.column_names.role_pivot_key') ?? 'role_id');
                $table->index($teamsKey, 'model_has_roles_team_foreign_key_index');

                $table->dropPrimary('model_has_roles_role_model_type_primary');
                $table->primary([$teamsKey, config('permission.column_names.role_pivot_key') ?? 'role_id', $modelKey, 'model_type'], 'model_has_roles_role_model_type_primary');

                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign(config('permission.column_names.role_pivot_key') ?? 'role_id')
                        ->references('id')
                        ->on($tableNames['roles'])
                        ->onDelete('cascade');
                }
            });
        }

        // 3. Model Has Permissions Table
        if (! Schema::hasColumn($tableNames['model_has_permissions'], $teamsKey)) {
            Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $teamsKey, $modelKey) {
                if (DB::getDriverName() !== 'sqlite') {
                    $table->dropForeign([config('permission.column_names.permission_pivot_key') ?? 'permission_id']);
                }

                $table->unsignedBigInteger($teamsKey)->default(0)->after(config('permission.column_names.permission_pivot_key') ?? 'permission_id');
                $table->index($teamsKey, 'model_has_permissions_team_foreign_key_index');

                $table->dropPrimary('model_has_permissions_permission_model_type_primary');
                $table->primary([$teamsKey, config('permission.column_names.permission_pivot_key') ?? 'permission_id', $modelKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');

                if (DB::getDriverName() !== 'sqlite') {
                    $table->foreign(config('permission.column_names.permission_pivot_key') ?? 'permission_id')
                        ->references('id')
                        ->on($tableNames['permissions'])
                        ->onDelete('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teamsKey = $columnNames['team_foreign_key'] ?? 'team_id';
        $modelKey = $columnNames['model_morph_key'] ?? 'model_id';

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames, $teamsKey, $modelKey) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign([config('permission.column_names.permission_pivot_key') ?? 'permission_id']);
            }

            $table->dropPrimary('model_has_permissions_permission_model_type_primary');
            $table->primary([config('permission.column_names.permission_pivot_key') ?? 'permission_id', $modelKey, 'model_type'], 'model_has_permissions_permission_model_type_primary');
            $table->dropColumn($teamsKey);

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign(config('permission.column_names.permission_pivot_key') ?? 'permission_id')
                    ->references('id')
                    ->on($tableNames['permissions'])
                    ->onDelete('cascade');
            }
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames, $teamsKey, $modelKey) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign([config('permission.column_names.role_pivot_key') ?? 'role_id']);
            }

            $table->dropPrimary('model_has_roles_role_model_type_primary');
            $table->primary([config('permission.column_names.role_pivot_key') ?? 'role_id', $modelKey, 'model_type'], 'model_has_roles_role_model_type_primary');
            $table->dropColumn($teamsKey);

            if (DB::getDriverName() !== 'sqlite') {
                $table->foreign(config('permission.column_names.role_pivot_key') ?? 'role_id')
                    ->references('id')
                    ->on($tableNames['roles'])
                    ->onDelete('cascade');
            }
        });

        Schema::table($tableNames['roles'], function (Blueprint $table) use ($teamsKey) {
            $table->dropUnique([$teamsKey, 'name', 'guard_name']);
            $table->unique(['name', 'guard_name']);
            $table->dropColumn($teamsKey);
        });
    }
};
