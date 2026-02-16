<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('avatar_path')->nullable()->after('email');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->string('featured_image_path')->nullable()->after('content');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('image_path')->nullable()->after('description');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->string('featured_image_path')->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropColumn('featured_image_path');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropColumn('image_path');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn('featured_image_path');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('avatar_path');
        });
    }
};
