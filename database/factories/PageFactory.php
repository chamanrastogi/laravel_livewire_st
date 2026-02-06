<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        $slug = Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999);

        return [
            'title' => $title,
            'slug' => $slug,
            'content' => fake()->paragraphs(4, true),
            'status' => fake()->randomElement(['draft', 'published']),
            'published_at' => fake()->optional(0.6)->dateTimeBetween('-2 years'),
            'meta_title' => fake()->optional(0.7)->sentence(6),
            'meta_description' => fake()->optional(0.7)->paragraph(),
            'meta_keywords' => fake()->optional(0.5)->words(5, true),
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year'),
        ]);
    }
}
