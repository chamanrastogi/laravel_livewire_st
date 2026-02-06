<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DemoDataSeeder extends Seeder
{
    /**
     * Total records to seed. Set via DEMO_RECORDS env (e.g. 1000) or default 500–2000.
     */
    protected function getScale(): int
    {
        $n = (int) config('demo.records', 1500);
        $n = max(500, min(5000, $n));

        return $n;
    }

    public function run(): void
    {
        $scale = $this->getScale();

        $userCount = (int) round($scale * 0.25);
        $userCount = max(500, min(2000, $userCount));

        $pageCount = (int) round($scale * 0.8);
        $pageCount = max(500, min(3000, $pageCount));

        $postCount = (int) round($scale * 0.8);
        $postCount = max(500, min(3000, $postCount));

        $mediaCount = (int) round($scale * 0.2);
        $mediaCount = max(200, min(1000, $mediaCount));

        $categoryCount = min(50, (int) round($scale * 0.02));
        $tagCount = min(100, (int) round($scale * 0.04));

        $this->command->info("Seeding demo data: {$userCount} users, {$pageCount} pages, {$postCount} posts, {$mediaCount} media, {$categoryCount} categories, {$tagCount} tags.");

        $userIds = $this->seedUsers($userCount);
        $this->seedCategoriesAndTags($categoryCount, $tagCount);
        $this->seedPages($pageCount, $userIds);
        $this->seedPosts($postCount, $userIds);
        $this->seedMedia($mediaCount, $userIds);

        Cache::forget('admin.roles.list');
        Cache::forget('admin.permissions.list');
        Cache::forget('admin.categories.list');
        Cache::forget('admin.tags.list');

        $this->command->info('Demo data seeding complete.');
    }

    /** @return array<int> */
    protected function seedUsers(int $count): array
    {
        $existing = User::count();
        if ($existing >= $count) {
            return User::limit($count)->pluck('id')->all();
        }

        $toCreate = $count - $existing;
        User::factory($toCreate)->create();

        return User::pluck('id')->all();
    }

    protected function seedCategoriesAndTags(int $categoryCount, int $tagCount): void
    {
        if (Category::count() < $categoryCount) {
            Category::factory($categoryCount - Category::count())->create();
        }
        if (Tag::count() < $tagCount) {
            Tag::factory($tagCount - Tag::count())->create();
        }
    }

    /** @param array<int> $userIds */
    protected function seedPages(int $count, array $userIds): void
    {
        $existing = Page::count();
        if ($existing >= $count) {
            return;
        }

        $toCreate = $count - $existing;
        $chunk = 500;

        for ($i = 0; $i < $toCreate; $i += $chunk) {
            $batch = min($chunk, $toCreate - $i);
            $pages = Page::factory($batch)->create();
            foreach ($pages as $page) {
                $page->update([
                    'created_by' => fake()->randomElement($userIds),
                    'updated_by' => fake()->randomElement($userIds),
                ]);
            }
        }
    }

    /** @param array<int> $userIds */
    protected function seedPosts(int $count, array $userIds): void
    {
        $existing = Post::count();
        if ($existing >= $count) {
            return;
        }

        $toCreate = $count - $existing;
        $chunk = 500;
        $categoryIds = Category::pluck('id')->all();
        $tagIds = Tag::pluck('id')->all();

        for ($i = 0; $i < $toCreate; $i += $chunk) {
            $batch = min($chunk, $toCreate - $i);
            $posts = Post::factory($batch)->create();

            foreach ($posts as $post) {
                $post->update([
                    'created_by' => fake()->randomElement($userIds),
                    'updated_by' => fake()->randomElement($userIds),
                ]);
                $cats = fake()->randomElements($categoryIds, min(fake()->numberBetween(0, 3), count($categoryIds)));
                $tags = fake()->randomElements($tagIds, min(fake()->numberBetween(0, 5), count($tagIds)));
                $post->categories()->sync(array_values($cats));
                $post->tags()->sync(array_values($tags));
            }
        }
    }

    /** @param array<int> $userIds */
    protected function seedMedia(int $count, array $userIds): void
    {
        $existing = Media::count();
        if ($existing >= $count) {
            return;
        }

        $media = Media::factory($count - $existing)->create();
        foreach ($media as $m) {
            $m->update(['uploaded_by' => fake()->randomElement($userIds)]);
        }
    }
}
