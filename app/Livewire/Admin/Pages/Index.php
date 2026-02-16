<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

#[Layout('layouts.app')]
#[Title('Pages')]
class Index extends Component
{
    use WithFileUploads;
    use WithPagination;
    use WithoutUrlPagination;

    public string $search = '';

    public string $sortField = 'title';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public string $title = '';

    public string $slug = '';

    public string $content = '';

    public $featuredImage = null;

    public bool $removeFeaturedImage = false;

    public ?string $currentFeaturedImagePath = null;

    public string $status = 'draft';

    public ?string $publishedAt = null;

    public string $metaTitle = '';

    public string $metaDescription = '';

    public string $metaKeywords = '';

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,'.($id ?? 'NULL')],
            'content' => ['nullable', 'string'],
            'featuredImage' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:draft,published'],
            'publishedAt' => ['nullable', 'date'],
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string'],
            'metaKeywords' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read pages'), 403);
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
        abort_unless(auth()->user()?->can('create pages'), 403);
        $this->resetForm();
        $this->showModal = true;
        $this->dispatch('quill:set-content', instance: 'page-content-editor', content: '');
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update pages'), 403);
        $page = Page::findOrFail($id);
        $this->editingId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->content = $page->content ?? '';
        $this->featuredImage = null;
        $this->removeFeaturedImage = false;
        $this->currentFeaturedImagePath = $page->featured_image_path;
        $this->status = $page->status;
        $this->publishedAt = $page->published_at?->format('Y-m-d\TH:i');
        $this->metaTitle = $page->meta_title ?? '';
        $this->metaDescription = $page->meta_description ?? '';
        $this->metaKeywords = $page->meta_keywords ?? '';
        $this->showModal = true;
        $this->dispatch('quill:set-content', instance: 'page-content-editor', content: $this->content);
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->editingId ? 'update pages' : 'create pages'),
            403,
        );
        $validated = $this->validate();
        $featuredImagePath = null;

        if ($this->editingId) {
            $existing = Page::findOrFail($this->editingId);
            $featuredImagePath = $existing->featured_image_path;

            if ($this->removeFeaturedImage && $featuredImagePath) {
                Storage::disk('public')->delete($featuredImagePath);
                $featuredImagePath = null;
            }
        }

        if ($this->featuredImage) {
            if ($featuredImagePath) {
                Storage::disk('public')->delete($featuredImagePath);
            }
            $featuredImagePath = $this->featuredImage->store('pages/'.date('Y/m'), 'public');
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'featured_image_path' => $featuredImagePath,
            'status' => $validated['status'],
            'published_at' => $validated['publishedAt'] ? $validated['publishedAt'] : null,
            'meta_title' => $validated['metaTitle'] ?: null,
            'meta_description' => $validated['metaDescription'] ?: null,
            'meta_keywords' => $validated['metaKeywords'] ?: null,
            'updated_by' => auth()->id(),
        ];
        if (! $this->editingId) {
            $data['created_by'] = auth()->id();
        }
        if ($this->editingId) {
            $existing->update($data);
            $this->currentFeaturedImagePath = $featuredImagePath;
            session()->flash('status', __('Page updated successfully.'));
        } else {
            Page::create($data);
            $this->currentFeaturedImagePath = $featuredImagePath;
            session()->flash('status', __('Page created successfully.'));
        }
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete pages'), 403);
        $page = Page::findOrFail($id);
        if ($page->featured_image_path) {
            Storage::disk('public')->delete($page->featured_image_path);
        }
        $page->delete();
        session()->flash('status', __('Page deleted successfully.'));
    }

    public function togglePublish(int $id): void
    {
        abort_unless(auth()->user()?->can('update pages'), 403);
        $page = Page::findOrFail($id);
        $wasPublished = $page->status === 'published';
        $page->update([
            'status' => $wasPublished ? 'draft' : 'published',
            'published_at' => $wasPublished ? null : now(),
            'updated_by' => auth()->id(),
        ]);
        session()->flash('status', $wasPublished ? __('Page unpublished.') : __('Page published.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->title = $this->slug = $this->content = $this->metaTitle = $this->metaDescription = $this->metaKeywords = '';
        $this->featuredImage = null;
        $this->removeFeaturedImage = false;
        $this->currentFeaturedImagePath = null;
        $this->status = 'draft';
        $this->publishedAt = null;
    }

    public function render(): View
    {
        $pages = Page::query()
            ->select(['id', 'title', 'slug', 'featured_image_path', 'status', 'published_at', 'updated_at'])
            ->when($this->search, function (Builder $q): void {
                $q->where(function (Builder $inner): void {
                    $inner->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.pages.index', [
            'pages' => $pages,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
