<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Users')]
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

    public string $email = '';

    public ?string $password = null;

    public array $roleIds = [];

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.($id ?? 'NULL')],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read users'), 403);
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
        abort_unless(auth()->user()?->can('create users'), 403);

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update users'), 403);

        $user = User::findOrFail($id);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = null;
        $this->roleIds = $user->roles()->pluck('id')->all();
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can($this->editingId ? 'update users' : 'create users'),
            403,
        );

        $validated = $this->validate();

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->save();
            $user->roles()->sync($this->roleIds ?? []);
            \Illuminate\Support\Facades\Cache::forget('admin.roles.list');

            session()->flash('status', 'User updated successfully.');
        } else {
            $user = User::create($validated);
            $user->roles()->sync($this->roleIds ?? []);

            session()->flash('status', 'User created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete users'), 403);

        $user = User::findOrFail($id);

        if ($user->getKey() === auth()->id()) {
            session()->flash('status', 'You cannot delete your own account.');

            return;
        }

        $user->delete();

        session()->flash('status', 'User deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = null;
        $this->roleIds = [];
    }

    public function render(): View
    {
        $users = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $roles = Cache::remember('admin.roles.list', now()->addHour(), fn () => Role::orderBy('name')->get());

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}

