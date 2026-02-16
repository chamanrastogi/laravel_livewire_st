<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Edit Post')]
class Edit extends Component
{
    use WithFileUploads;

    public int $postId;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public $featuredImage = null;

    public bool $removeFeaturedImage = false;

    public ?string $currentFeaturedImagePath = null;

    public string $status = 'draft';

    public ?string $publishedAt = null;

    public array $categoryIds = [];

    public array $tagIds = [];

    public function mount(Post $post): void
    {
        abort_unless(auth()->user()?->can('update posts'), 403);

        $post->load('categories', 'tags');

        $this->postId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content ?? '';
        $this->currentFeaturedImagePath = $post->featured_image_path;
        $this->status = $post->status;
        $this->publishedAt = $post->published_at?->format('Y-m-d\TH:i');
        $this->categoryIds = $post->categories->pluck('id')->all();
        $this->tagIds = $post->tags->pluck('id')->all();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug,'.$this->postId],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'featuredImage' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:draft,published'],
            'publishedAt' => ['nullable', 'date'],
            'categoryIds' => ['array'],
            'categoryIds.*' => ['integer', 'exists:categories,id'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('update posts'), 403);
        $validated = $this->validate();

        $post = Post::findOrFail($this->postId);
        $featuredImagePath = $post->featured_image_path;

        if ($this->removeFeaturedImage && $featuredImagePath) {
            Storage::disk('public')->delete($featuredImagePath);
            $featuredImagePath = null;
        }

        if ($this->featuredImage) {
            if ($featuredImagePath) {
                Storage::disk('public')->delete($featuredImagePath);
            }
            $featuredImagePath = $this->featuredImage->store('posts/'.date('Y/m'), 'public');
        }

        $post->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?: null,
            'content' => $validated['content'],
            'featured_image_path' => $featuredImagePath,
            'status' => $validated['status'],
            'published_at' => $validated['publishedAt'] ?: null,
            'updated_by' => auth()->id(),
        ]);

        $post->categories()->sync($validated['categoryIds'] ?? []);
        $post->tags()->sync($validated['tagIds'] ?? []);

        $this->currentFeaturedImagePath = $featuredImagePath;
        $this->featuredImage = null;
        $this->removeFeaturedImage = false;

        session()->flash('status', __('Post updated successfully.'));
    }

    public function render(): View
    {
        $categories = Cache::remember('admin.categories.list', now()->addHour(), fn () => Category::orderBy('name')->get());
        $tags = Cache::remember('admin.tags.list', now()->addHour(), fn () => Tag::orderBy('name')->get());

        return view('livewire.admin.posts.edit', [
            'categories' => $categories,
            'tags' => $tags,
        ]);
    }
}
