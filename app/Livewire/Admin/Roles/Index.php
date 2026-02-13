<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Roles')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $name = '';

    public array $permissionIds = [];

    public string $color = '#6B7280';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.($id ?? 'NULL')],
            'color' => ['required', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'permissionIds' => ['array'],
            'permissionIds.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read roles'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function create(): void
    {
        abort_unless(auth()->user()?->can('create roles'), 403);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update roles'), 403);

        $role = Role::findOrFail($id);

        $this->editingId = $role->id;
        $this->name = $role->name;
        $this->color = $role->color ?: '#6B7280';
        $this->permissionIds = $role->permissions()->pluck('id')->all();
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->editingId ? 'update roles' : 'create roles'),
            403,
        );

        $validated = $this->validate();

        if ($this->editingId) {
            $role = Role::findOrFail($this->editingId);
            $role->name = $validated['name'];
            $role->color = $validated['color'];
            $role->save();
        } else {
            $role = Role::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'color' => $validated['color'],
            ]);
        }

        $role->syncPermissions($validated['permissionIds'] ?? []);

        Cache::forget('admin.roles.list');

        session()->flash('status', 'Role saved successfully.');

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete roles'), 403);

        $role = Role::findOrFail($id);

        if ($role->name === 'Super Admin') {
            session()->flash('status', 'You cannot delete the Super Admin role.');

            return;
        }

        $role->delete();

        session()->flash('status', 'Role deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->color = '#6B7280';
        $this->permissionIds = [];
    }

    public function render(): View
    {
        $roles = Role::query()
            ->withCount('permissions')
            ->when($this->search, function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $permissions = Cache::remember('admin.permissions.list', now()->addHour(), fn () => Permission::orderBy('name')->get());

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}

