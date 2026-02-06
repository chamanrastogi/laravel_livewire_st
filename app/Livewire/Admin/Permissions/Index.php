<?php

namespace App\Livewire\Admin\Permissions;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;

#[Layout('layouts.app')]
#[Title('Permissions')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    protected array $perPageOptions = [15, 25, 50, 100];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read permissions'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $permissions = Permission::query()
            ->select(['id', 'name', 'guard_name'])
            ->when($this->search, function (Builder $query): void {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.admin.permissions.index', [
            'permissions' => $permissions,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
