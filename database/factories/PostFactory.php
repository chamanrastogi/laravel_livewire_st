<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);
        $slug = Str::slug($title).'-'.fake()->unique()->numberBetween(1, 99999);

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => fake()->optional(0.8)->passthrough(Str::limit(fake()->paragraph(), 252, '')),
            'content' => fake()->paragraphs(6, true),
            'status' => fake()->randomElement(['draft', 'published']),
            'published_at' => fake()->optional(0.6)->dateTimeBetween('-2 years'),
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
