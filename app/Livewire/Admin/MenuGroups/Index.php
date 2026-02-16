<?php

namespace App\Livewire\Admin\MenuGroups;

use App\Models\MenuGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;

#[Layout('layouts.app')]
#[Title('Menu Groups')]
class Index extends Component
{
    use WithPagination;
    use WithoutUrlPagination;

    public string $search = '';

    public string $sortField = 'title';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public int $sortOrder = 0;

    public bool $isActive = true;

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'name' => ['required', 'string', 'max:50'],
            'slug' => ['required', 'string', 'max:255', 'unique:menugroups,slug,'.($id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read menu groups'), 403);
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
        abort_unless(auth()->user()?->can('create menu groups'), 403);
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update menu groups'), 403);
        $group = MenuGroup::findOrFail($id);
        $this->editingId = $group->id;
        $this->name = $group->title;
        $this->slug = $group->slug;
        $this->description = $group->description ?? '';
        $this->sortOrder = (int) $group->sort_order;
        $this->isActive = (bool) $group->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can($this->editingId ? 'update menu groups' : 'create menu groups'), 403);
        $validated = $this->validate();

        $data = [
            'title' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?: null,
            'sort_order' => $validated['sortOrder'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            MenuGroup::findOrFail($this->editingId)->update($data);
            session()->flash('status', __('Menu group updated successfully.'));
        } else {
            MenuGroup::create($data);
            session()->flash('status', __('Menu group created successfully.'));
        }

        Cache::forget('admin.menu-groups.list');
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete menu groups'), 403);
        MenuGroup::findOrFail($id)->delete();
        Cache::forget('admin.menu-groups.list');
        session()->flash('status', __('Menu group deleted successfully.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->slug = $this->description = '';
        $this->sortOrder = 0;
        $this->isActive = true;
    }

    public function render(): View
    {
        $groups = MenuGroup::query()
            ->select(['id', 'title', 'slug', 'sort_order', 'is_active', 'created_at'])
            ->when($this->search, function (Builder $q): void {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.menu-groups.index', [
            'groups' => $groups,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
