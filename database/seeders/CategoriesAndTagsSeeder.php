<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriesAndTagsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ['News', 'Tutorials', 'Updates'];
        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }

        $tags = ['laravel', 'livewire', 'cms', 'php'];
        foreach ($tags as $name) {
            Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name],
            );
        }
    }
}
