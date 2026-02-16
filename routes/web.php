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
            Route::get('/pages/{page}/edit', \App\Livewire\Admin\Pages\Edit::class)
                ->middleware('permission:update pages')
                ->name('pages.edit');

            Route::get('/posts', \App\Livewire\Admin\Posts\Index::class)
                ->middleware('permission:read posts')
                ->name('posts.index');
            Route::get('/posts/{post}/edit', \App\Livewire\Admin\Posts\Edit::class)
                ->middleware('permission:update posts')
                ->name('posts.edit');

            Route::get('/categories', \App\Livewire\Admin\Categories\Index::class)
                ->middleware('permission:read categories')
                ->name('categories.index');

            Route::get('/tags', \App\Livewire\Admin\Tags\Index::class)
                ->middleware('permission:read tags')
                ->name('tags.index');

            Route::get('/media', \App\Livewire\Admin\Media\Index::class)
                ->middleware('permission:read media')
                ->name('media.index');

            Route::get('/modules', \App\Livewire\Admin\Modules\Index::class)
                ->middleware('permission:read modules')
                ->name('modules.index');
            Route::get('/modules/{module}/edit', \App\Livewire\Admin\Modules\Edit::class)
                ->middleware('permission:update modules')
                ->name('modules.edit');

            Route::get('/menu-groups', \App\Livewire\Admin\MenuGroups\Index::class)
                ->middleware('permission:read menu groups')
                ->name('menu-groups.index');

            Route::get('/menus', \App\Livewire\Admin\Menus\Index::class)
                ->middleware('permission:read menus')
                ->name('menus.index');

            Route::get('/settings', \App\Livewire\Admin\Settings\Index::class)
                ->middleware('permission:read settings')
                ->name('settings.index');
        });
});

require __DIR__.'/settings.php';
