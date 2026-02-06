<?php

namespace App\Livewire\Admin\Posts;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Posts')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'title';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $excerpt = '';

    public string $content = '';

    public string $status = 'draft';

    public ?string $publishedAt = null;

    public array $categoryIds = [];

    public array $tagIds = [];

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:posts,slug,'.($id ?? 'NULL')],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published'],
            'publishedAt' => ['nullable', 'date'],
            'categoryIds' => ['array'],
            'categoryIds.*' => ['integer', 'exists:categories,id'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read posts'), 403);
    }

    public function updatedTitle($value): void
    {
        if (! $this->editingId) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create(): void
    {
        abort_unless(auth()->user()?->can('create posts'), 403);
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update posts'), 403);
        $post = Post::with('categories', 'tags')->findOrFail($id);
        $this->editingId = $post->id;
        $this->title = $post->title;
        $this->slug = $post->slug;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content ?? '';
        $this->status = $post->status;
        $this->publishedAt = $post->published_at?->format('Y-m-d\TH:i');
        $this->categoryIds = $post->categories->pluck('id')->all();
        $this->tagIds = $post->tags->pluck('id')->all();
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->editingId ? 'update posts' : 'create posts'),
            403,
        );
        $validated = $this->validate();
        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?: null,
            'content' => $validated['content'],
            'status' => $validated['status'],
            'published_at' => $validated['publishedAt'] ?: null,
            'updated_by' => auth()->id(),
        ];
        if (! $this->editingId) {
            $data['created_by'] = auth()->id();
        }
        if ($this->editingId) {
            $post = Post::findOrFail($this->editingId);
            $post->update($data);
            $post->categories()->sync($validated['categoryIds'] ?? []);
            $post->tags()->sync($validated['tagIds'] ?? []);
            session()->flash('status', __('Post updated successfully.'));
        } else {
            $post = Post::create($data);
            $post->categories()->sync($validated['categoryIds'] ?? []);
            $post->tags()->sync($validated['tagIds'] ?? []);
            session()->flash('status', __('Post created successfully.'));
        }
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete posts'), 403);
        Post::findOrFail($id)->delete();
        session()->flash('status', __('Post deleted successfully.'));
    }

    public function togglePublish(int $id): void
    {
        abort_unless(auth()->user()?->can('update posts'), 403);
        $post = Post::findOrFail($id);
        $wasPublished = $post->status === 'published';
        $post->update([
            'status' => $wasPublished ? 'draft' : 'published',
            'published_at' => $wasPublished ? null : now(),
            'updated_by' => auth()->id(),
        ]);
        session()->flash('status', $wasPublished ? __('Post unpublished.') : __('Post published.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = $this->slug = $this->excerpt = $this->content = '';
        $this->status = 'draft';
        $this->publishedAt = null;
        $this->categoryIds = $this->tagIds = [];
    }

    public function render(): View
    {
        $posts = Post::query()
            ->select(['id', 'title', 'slug', 'status', 'published_at', 'updated_at'])
            ->when($this->search, function (Builder $q): void {
                $q->where(function (Builder $inner): void {
                    $inner->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $categories = Cache::remember('admin.categories.list', now()->addHour(), fn () => Category::orderBy('name')->get());
        $tags = Cache::remember('admin.tags.list', now()->addHour(), fn () => Tag::orderBy('name')->get());

        return view('livewire.admin.posts.index', [
            'posts' => $posts,
            'categories' => $categories,
            'tags' => $tags,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
