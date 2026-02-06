<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Categories')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:categories,slug,'.($id ?? 'NULL')],
            'description' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read categories'), 403);
    }

    public function updatedName($value): void
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
        abort_unless(auth()->user()?->can('create categories'), 403);
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update categories'), 403);
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->editingId ? 'update categories' : 'create categories'),
            403,
        );
        $this->validate();
        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description ?: null,
            ]);
            session()->flash('status', __('Category updated successfully.'));
        } else {
            Category::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'description' => $this->description ?: null,
            ]);
            session()->flash('status', __('Category created successfully.'));
        }
        Cache::forget('admin.categories.list');
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete categories'), 403);
        Category::findOrFail($id)->delete();
        Cache::forget('admin.categories.list');
        session()->flash('status', __('Category deleted successfully.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->slug = $this->description = '';
    }

    public function render(): View
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'description', 'created_at'])
            ->when($this->search, function (Builder $q): void {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.categories.index', [
            'categories' => $categories,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
