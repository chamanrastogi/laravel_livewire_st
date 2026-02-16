<?php

namespace App\Livewire\Admin\Modules;

use App\Models\Module;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Edit Module')]
class Edit extends Component
{
    public int $moduleId;

    public string $name = '';

    public string $slug = '';

    public string $description = '';

    public bool $isActive = true;

    public function mount(Module $module): void
    {
        abort_unless(auth()->user()?->can('update modules'), 403);

        $this->moduleId = $module->id;
        $this->name = $module->name;
        $this->slug = $module->slug;
        $this->description = $module->description ?? '';
        $this->isActive = (bool) $module->is_active;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255', 'unique:modules,slug,'.$this->moduleId],
            'description' => ['nullable', 'string'],
            'isActive' => ['boolean'],
        ];
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('update modules'), 403);
        $validated = $this->validate();

        Module::findOrFail($this->moduleId)->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?: null,
            'is_active' => $validated['isActive'],
        ]);

        Cache::forget('admin.modules.list');
        session()->flash('status', __('Module updated successfully.'));
    }

    public function render(): View
    {
        return view('livewire.admin.modules.edit');
    }
}
