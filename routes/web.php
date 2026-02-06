<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::get('dashboard', \App\Livewire\Admin\Dashboard::class)
        ->name('dashboard');

    Route::prefix('admin')
        ->as('admin.')
        ->group(function (): void {
            Route::get('/users', \App\Livewire\Admin\Users\Index::class)
                ->middleware('permission:read users')
                ->name('users.index');

            Route::get('/roles', \App\Livewire\Admin\Roles\Index::class)
                ->middleware('permission:read roles')
                ->name('roles.index');

            Route::get('/permissions', \App\Livewire\Admin\Permissions\Index::class)
                ->middleware('permission:read permissions')
                ->name('permissions.index');

            Route::get('/pages', \App\Livewire\Admin\Pages\Index::class)
                ->middleware('permission:read pages')
                ->name('pages.index');

            Route::get('/posts', \App\Livewire\Admin\Posts\Index::class)
                ->middleware('permission:read posts')
                ->name('posts.index');

            Route::get('/categories', \App\Livewire\Admin\Categories\Index::class)
                ->middleware('permission:read categories')
                ->name('categories.index');

            Route::get('/tags', \App\Livewire\Admin\Tags\Index::class)
                ->middleware('permission:read tags')
                ->name('tags.index');

            Route::get('/media', \App\Livewire\Admin\Media\Index::class)
                ->middleware('permission:read media')
                ->name('media.index');

            Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)
                ->middleware('permission:read settings')
                ->name('settings.index');
        });
});

require __DIR__.'/settings.php';
