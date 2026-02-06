<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);
        $name = Str::title($name);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->numberBetween(1, 9999),
            'description' => fake()->optional(0.6)->paragraph(),
        ];
    }
}
