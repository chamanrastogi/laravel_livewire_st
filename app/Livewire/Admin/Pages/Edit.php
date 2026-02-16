<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('Edit Page')]
class Edit extends Component
{
    use WithFileUploads;

    public int $pageId;

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

    public function mount(Page $page): void
    {
        abort_unless(auth()->user()?->can('update pages'), 403);

        $this->pageId = $page->id;
        $this->title = $page->title;
        $this->slug = $page->slug;
        $this->content = $page->content ?? '';
        $this->currentFeaturedImagePath = $page->featured_image_path;
        $this->status = $page->status;
        $this->publishedAt = $page->published_at?->format('Y-m-d\TH:i');
        $this->metaTitle = $page->meta_title ?? '';
        $this->metaDescription = $page->meta_description ?? '';
        $this->metaKeywords = $page->meta_keywords ?? '';
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug,'.$this->pageId],
            'content' => ['nullable', 'string'],
            'featuredImage' => ['nullable', 'image', 'max:2048'],
            'status' => ['required', 'in:draft,published'],
            'publishedAt' => ['nullable', 'date'],
            'metaTitle' => ['nullable', 'string', 'max:255'],
            'metaDescription' => ['nullable', 'string'],
            'metaKeywords' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('update pages'), 403);
        $validated = $this->validate();

        $page = Page::findOrFail($this->pageId);
        $featuredImagePath = $page->featured_image_path;

        if ($this->removeFeaturedImage && $featuredImagePath) {
            Storage::disk('public')->delete($featuredImagePath);
            $featuredImagePath = null;
        }

        if ($this->featuredImage) {
            if ($featuredImagePath) {
                Storage::disk('public')->delete($featuredImagePath);
            }
            $featuredImagePath = $this->featuredImage->store('pages/'.date('Y/m'), 'public');
        }

        $page->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'content' => $validated['content'],
            'featured_image_path' => $featuredImagePath,
            'status' => $validated['status'],
            'published_at' => $validated['publishedAt'] ?: null,
            'meta_title' => $validated['metaTitle'] ?: null,
            'meta_description' => $validated['metaDescription'] ?: null,
            'meta_keywords' => $validated['metaKeywords'] ?: null,
            'updated_by' => auth()->id(),
        ]);

        $this->currentFeaturedImagePath = $featuredImagePath;
        $this->featuredImage = null;
        $this->removeFeaturedImage = false;

        session()->flash('status', __('Page updated successfully.'));
    }

    public function render(): View
    {
        return view('livewire.admin.pages.edit');
    }
}
