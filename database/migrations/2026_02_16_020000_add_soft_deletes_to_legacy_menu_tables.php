<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus') && ! Schema::hasColumn('menus', 'deleted_at')) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('modules') && ! Schema::hasColumn('modules', 'deleted_at')) {
            Schema::table('modules', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('menugroups') && ! Schema::hasColumn('menugroups', 'deleted_at')) {
            Schema::table('menugroups', function (Blueprint $table): void {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menus') && Schema::hasColumn('menus', 'deleted_at')) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'deleted_at')) {
            Schema::table('modules', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasTable('menugroups') && Schema::hasColumn('menugroups', 'deleted_at')) {
            Schema::table('menugroups', function (Blueprint $table): void {
                $table->dropSoftDeletes();
            });
        }
    }
};
