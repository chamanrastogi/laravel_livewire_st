<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('modules')) {
            Schema::table('modules', function (Blueprint $table): void {
                if (! Schema::hasColumn('modules', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('name');
                }
                if (! Schema::hasColumn('modules', 'description')) {
                    $table->text('description')->nullable()->after('small_text');
                }
                if (! Schema::hasColumn('modules', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('status');
                }
                if (! Schema::hasColumn('modules', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('menugroups')) {
            Schema::table('menugroups', function (Blueprint $table): void {
                if (! Schema::hasColumn('menugroups', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('title');
                }
                if (! Schema::hasColumn('menugroups', 'description')) {
                    $table->text('description')->nullable()->after('slug');
                }
                if (! Schema::hasColumn('menugroups', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('description');
                }
                if (! Schema::hasColumn('menugroups', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('sort_order');
                }
                if (! Schema::hasColumn('menugroups', 'created_at')) {
                    $table->timestamps();
                }
                if (! Schema::hasColumn('menugroups', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        if (Schema::hasTable('menus')) {
            Schema::table('menus', function (Blueprint $table): void {
                if (! Schema::hasColumn('menus', 'menugroup_id')) {
                    $table->foreignId('menugroup_id')->nullable()->after('group_id')->constrained('menugroups')->nullOnDelete();
                }
                if (! Schema::hasColumn('menus', 'module_id')) {
                    $table->foreignId('module_id')->nullable()->after('menugroup_id')->constrained('modules')->nullOnDelete();
                }
                if (! Schema::hasColumn('menus', 'slug')) {
                    $table->string('slug')->nullable()->unique()->after('title');
                }
                if (! Schema::hasColumn('menus', 'icon')) {
                    $table->string('icon')->nullable()->after('url');
                }
                if (! Schema::hasColumn('menus', 'target')) {
                    $table->enum('target', ['_self', '_blank'])->default('_self')->after('icon');
                }
                if (! Schema::hasColumn('menus', 'sort_order')) {
                    $table->unsignedInteger('sort_order')->default(0)->after('position');
                }
                if (! Schema::hasColumn('menus', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('status');
                }
                if (! Schema::hasColumn('menus', 'created_at')) {
                    $table->timestamps();
                }
                if (! Schema::hasColumn('menus', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        // Intentionally left empty to avoid destructive rollback of upgraded legacy schemas.
    }
};
