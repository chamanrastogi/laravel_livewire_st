<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    private static array $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx'];

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $ext = fake()->randomElement(self::$extensions);
        $originalName = Str::slug($name).'.'.$ext;

        return [
            'disk' => 'public',
            'path' => 'media/'.fake()->date('Y/m').'/'.fake()->uuid().'.'.$ext,
            'original_name' => $originalName,
            'mime_type' => $this->mimeForExt($ext),
            'size' => fake()->numberBetween(1024, 5 * 1024 * 1024),
            'alt_text' => fake()->optional(0.4)->sentence(4),
            'collection' => fake()->optional(0.3)->randomElement(['images', 'documents', 'downloads']),
            'uploaded_by' => null,
        ];
    }

    private function mimeForExt(string $ext): string
    {
        return match (strtolower($ext)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            default => 'application/octet-stream',
        };
    }
}
