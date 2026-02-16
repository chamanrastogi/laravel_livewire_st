<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'users',
            'roles',
            'permissions',
            'pages',
            'posts',
            'categories',
            'tags',
            'media',
            'modules',
            'menu groups',
            'menus',
            'settings',
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        $permissions = collect($modules)
            ->flatMap(function (string $module) use ($actions) {
                return collect($actions)->map(function (string $action) use ($module) {
                    return Permission::firstOrCreate(
                        ['name' => "{$action} {$module}", 'guard_name' => 'web'],
                    );
                });
            });

        $superAdmin = Role::firstOrCreate(
            ['name' => 'Super Admin', 'guard_name' => 'web'],
            ['color' => '#DC2626'],
        );
        $admin = Role::firstOrCreate(
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['color' => '#2563EB'],
        );
        $editor = Role::firstOrCreate(
            ['name' => 'Editor', 'guard_name' => 'web'],
            ['color' => '#D97706'],
        );
        $viewer = Role::firstOrCreate(
            ['name' => 'Viewer', 'guard_name' => 'web'],
            ['color' => '#6B7280'],
        );

        $superAdmin->color = $superAdmin->color ?: '#DC2626';
        $superAdmin->save();
        $admin->color = $admin->color ?: '#2563EB';
        $admin->save();
        $editor->color = $editor->color ?: '#D97706';
        $editor->save();
        $viewer->color = $viewer->color ?: '#6B7280';
        $viewer->save();

        $superAdmin->syncPermissions($permissions);

        $admin->syncPermissions(
            $permissions->filter(function (Permission $permission) {
                return ! str_contains($permission->name, 'roles')
                    && ! str_contains($permission->name, 'permissions');
            }),
        );

        $editor->syncPermissions(
            $permissions->filter(function (Permission $permission) {
                return str_contains($permission->name, 'pages')
                    || str_contains($permission->name, 'posts')
                    || str_contains($permission->name, 'categories')
                    || str_contains($permission->name, 'tags')
                    || str_contains($permission->name, 'media')
                    || str_contains($permission->name, 'menus')
                    || str_contains($permission->name, 'menu groups')
                    || str_contains($permission->name, 'modules');
            }),
        );

        $viewer->syncPermissions(
            $permissions->filter(fn (Permission $permission) => str_starts_with($permission->name, 'read ')),
        );
    }
}
