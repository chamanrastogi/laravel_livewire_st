<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index('name');
            $table->index('email');
            $table->index('created_at');
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->index('title');
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->index('title');
            $table->index('status');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->index('original_name');
            $table->index('created_at');
            $table->index(['disk', 'created_at']);
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('tags', function (Blueprint $table): void {
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['name']);
            $table->dropIndex(['email']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('pages', function (Blueprint $table): void {
            $table->dropIndex(['title']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex(['title']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex(['original_name']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['disk', 'created_at']);
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('tags', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });
    }
};
