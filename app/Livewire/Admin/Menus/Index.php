<?php

namespace App\Livewire\Admin\Menus;

use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\Module;
use App\Models\Page;
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
#[Title('Menus')]
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

    public ?int $menuGroupId = null;

    public ?int $moduleId = null;

    public ?int $pageId = null;

    public string $title = '';

    public string $slug = '';

    public string $url = '';

    public string $icon = '';

    public string $target = '_self';

    public int $sortOrder = 0;

    public bool $isActive = true;

    public bool $showModal = false;

    protected function rules(): array
    {
        $id = $this->editingId;

        return [
            'menuGroupId' => ['nullable', 'integer', 'exists:menugroups,id'],
            'moduleId' => ['nullable', 'integer', 'exists:modules,id'],
            'pageId' => ['nullable', 'integer', 'exists:pages,id'],
            'title' => ['required', 'string', 'max:50'],
            'slug' => ['required', 'string', 'max:255', 'unique:menus,slug,'.($id ?? 'NULL')],
            'url' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:100'],
            'target' => ['required', 'in:_self,_blank'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('read menus'), 403);
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
        abort_unless(auth()->user()?->can('create menus'), 403);
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        abort_unless(auth()->user()?->can('update menus'), 403);
        $menu = Menu::findOrFail($id);
        $this->editingId = $menu->id;
        $this->menuGroupId = $menu->menugroup_id;
        $this->moduleId = $menu->module_id;
        $this->pageId = $menu->page_id;
        $this->title = $menu->title;
        $this->slug = $menu->slug;
        $this->url = $menu->url ?? '';
        $this->icon = $menu->icon ?? '';
        $this->target = $menu->target;
        $this->sortOrder = (int) $menu->sort_order;
        $this->isActive = (bool) $menu->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can($this->editingId ? 'update menus' : 'create menus'), 403);
        $validated = $this->validate();

        $data = [
            'menugroup_id' => $validated['menuGroupId'] ?? null,
            'module_id' => $validated['moduleId'] ?? null,
            'page_id' => $validated['pageId'] ?? null,
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'url' => $validated['url'] ?: null,
            'icon' => $validated['icon'] ?: null,
            'target' => $validated['target'],
            'sort_order' => $validated['sortOrder'],
            'is_active' => $validated['isActive'],
        ];

        if ($this->editingId) {
            Menu::findOrFail($this->editingId)->update($data);
            session()->flash('status', __('Menu updated successfully.'));
        } else {
            Menu::create($data);
            session()->flash('status', __('Menu created successfully.'));
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        abort_unless(auth()->user()?->can('delete menus'), 403);
        Menu::findOrFail($id)->delete();
        session()->flash('status', __('Menu deleted successfully.'));
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->menuGroupId = null;
        $this->moduleId = null;
        $this->pageId = null;
        $this->title = $this->slug = $this->url = $this->icon = '';
        $this->target = '_self';
        $this->sortOrder = 0;
        $this->isActive = true;
    }

    public function render(): View
    {
        $menus = Menu::query()
            ->select(['id', 'menugroup_id', 'module_id', 'page_id', 'title', 'slug', 'url', 'target', 'sort_order', 'is_active'])
            ->with(['menuGroup:id,title', 'module:id,name', 'page:id,title'])
            ->when($this->search, function (Builder $q): void {
                $q->where(function (Builder $inner): void {
                    $inner->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%')
                        ->orWhere('url', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $menuGroups = Cache::remember('admin.menu-groups.list', now()->addHour(), fn () => MenuGroup::query()
            ->select(['id', 'title'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get());

        $modules = Cache::remember('admin.modules.list', now()->addHour(), fn () => Module::query()
            ->select(['id', 'name'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get());

        $pages = Cache::remember('admin.pages.for-menus.list', now()->addHour(), fn () => Page::query()
            ->select(['id', 'title'])
            ->orderBy('title')
            ->get());

        return view('livewire.admin.menus.index', [
            'menus' => $menus,
            'menuGroups' => $menuGroups,
            'modules' => $modules,
            'pages' => $pages,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
