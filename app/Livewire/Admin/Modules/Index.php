<?php

namespace App\Livewire\Admin\Modules;

use App\Models\Module;
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
#[Title('Modules')]
class Index extends Component
{
    use WithPagination;
    use WithoutUrlPagination;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $isActive = true;

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255', 'unique:modules,slug,'.($id ?? 'NULL')],
            'description' => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read modules'), 403);
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
        abort_unless(auth()->user()?->can('create modules'), 403);
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update modules'), 403);
        $module = Module::findOrFail($id);
        $this->editingId = $module->id;
        $this->name = $module->name;
        $this->slug = $module->slug;
        $this->description = $module->description ?? '';
        $this->isActive = (bool) $module->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can($this->editingId ? 'update modules' : 'create modules'), 403);
        $validated = $this->validate();

        $data = [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?: null,
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            Module::findOrFail($this->editingId)->update($data);
            session()->flash('status', __('Module updated successfully.'));
        } else {
            Module::create($data);
            session()->flash('status', __('Module created successfully.'));
        }

        Cache::forget('admin.modules.list');
        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete modules'), 403);
        Module::findOrFail($id)->delete();
        Cache::forget('admin.modules.list');
        session()->flash('status', __('Module deleted successfully.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = $this->slug = $this->description = '';
        $this->isActive = true;
    }

    public function render(): View
    {
        $modules = Module::query()
            ->select(['id', 'name', 'slug', 'is_active', 'created_at'])
            ->when($this->search, function (Builder $q): void {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('slug', 'like', '%'.$this->search.'%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.modules.index', [
            'modules' => $modules,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
