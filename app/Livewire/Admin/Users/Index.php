<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Users')]
class Index extends Component
{
    use WithFileUploads;
    use WithPagination;
    use WithoutUrlPagination;

    public string $search = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 15;

    protected array $perPageOptions = [15, 25, 50, 100];

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public ?string $password = null;

    public $avatarImage = null;

    public bool $removeAvatar = false;

    public ?string $currentAvatarPath = null;

    public array $roleIds = [];

    public bool $showModal = false;

    public ?int $confirmingDeleteId = null;

    public ?string $confirmingDeleteName = null;

    public bool $showDeleteModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.($id ?? 'NULL')],
            'password' => [$id ? 'nullable' : 'required', 'string', 'min:8'],
            'avatarImage' => ['nullable', 'image', 'max:2048'],
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
        $this->avatarImage = null;
        $this->removeAvatar = false;
        $this->currentAvatarPath = $user->avatar_path;
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
            $avatarPath = $user->avatar_path;

            if ($this->removeAvatar && $avatarPath) {
                Storage::disk('public')->delete($avatarPath);
                $avatarPath = null;
            }

            if ($this->avatarImage) {
                if ($avatarPath) {
                    Storage::disk('public')->delete($avatarPath);
                }
                $avatarPath = $this->avatarImage->store('avatars/'.date('Y/m'), 'public');
            }

            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'avatar_path' => $avatarPath,
            ]);

            if (! empty($validated['password'])) {
                $user->password = $validated['password'];
            }

            $user->save();
            $this->currentAvatarPath = $avatarPath;
            $user->roles()->sync($this->roleIds ?? []);
            \Illuminate\Support\Facades\Cache::forget('admin.roles.list');

            session()->flash('status', 'User updated successfully.');
        } else {
            $avatarPath = $this->avatarImage?->store('avatars/'.date('Y/m'), 'public');

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
            $user->avatar_path = $avatarPath;
            $user->save();
            $this->currentAvatarPath = $avatarPath;
            $user->roles()->sync($this->roleIds ?? []);

            session()->flash('status', 'User created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->confirmDelete($id);
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete users'), 403);

        $user = User::findOrFail($id);

        if ($user->getKey() === auth()->id()) {
            session()->flash('status', 'You cannot delete your own account.');

            return;
        }

        if ($this->isUserProtectedFromDelete($user)) {
            session()->flash('status', 'You cannot delete a Super Admin user.');

            return;
        }

        $this->confirmingDeleteId = $user->id;
        $this->confirmingDeleteName = $user->name;
        $this->showDeleteModal = true;
    }

    public function deleteConfirmed(): void
    {
        abort_unless(auth()->user()?->can('delete users'), 403);

        if (! $this->confirmingDeleteId) {
            return;
        }

        $user = User::with('roles:id,name')->findOrFail($this->confirmingDeleteId);

        if ($user->getKey() === auth()->id()) {
            session()->flash('status', 'You cannot delete your own account.');
            $this->closeDeleteModal();

            return;
        }

        if ($this->isUserProtectedFromDelete($user)) {
            session()->flash('status', 'You cannot delete a Super Admin user.');
            $this->closeDeleteModal();

            return;
        }

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        session()->flash('status', 'User deleted successfully.');

        $this->closeDeleteModal();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->confirmingDeleteId = null;
        $this->confirmingDeleteName = null;
    }

    public function toggleStatus(int $id): void
    {
        abort_unless(auth()->user()?->can('update users'), 403);

        $user = User::findOrFail($id);
        $user->is_active = ! $user->is_active;
        $user->save();

        session()->flash('status', 'User status updated successfully.');
    }

    protected function isUserProtectedFromDelete(User $user): bool
    {
        return $user->roles->contains('name', 'Super Admin');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = null;
        $this->avatarImage = null;
        $this->removeAvatar = false;
        $this->currentAvatarPath = null;
        $this->roleIds = [];
    }

    public function render(): View
    {
        $sortField = in_array($this->sortField, ['name', 'email', 'role'], true) ? $this->sortField : 'name';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $users = User::query()
            ->select(['id', 'name', 'email', 'avatar_path', 'is_active', 'created_at'])
            ->with('roles:id,name,color')
            ->when($this->search, function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when(
                $sortField === 'role',
                function (Builder $query) use ($sortDirection): void {
                    $query->orderByRaw(
                        '(select min(roles.name) from roles
                        inner join model_has_roles on roles.id = model_has_roles.role_id
                        where model_has_roles.model_type = ?
                        and model_has_roles.model_id = users.id) '.$sortDirection,
                        [User::class]
                    );
                },
                function (Builder $query) use ($sortField, $sortDirection): void {
                    $query->orderBy($sortField, $sortDirection);
                }
            )
            ->paginate($this->perPage);

        $roles = Cache::remember('admin.roles.list', now()->addHour(), fn () => Role::orderBy('name')->get());

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
