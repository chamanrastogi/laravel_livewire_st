<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('menus') && ! Schema::hasColumn('menus', 'page_id')) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->foreignId('page_id')->nullable()->after('module_id')->constrained('pages')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('menus') && Schema::hasColumn('menus', 'page_id')) {
            Schema::table('menus', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('page_id');
            });
        }
    }
};
