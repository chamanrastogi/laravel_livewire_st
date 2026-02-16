<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <x-data-table columns="7" loading-target="search,sortBy,perPage,nextPage,previousPage,gotoPage,setPage"
        :empty="__('No menus found.')">
        <x-slot:toolbar>
            <div class="flex flex-1 min-w-[200px] items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search menus...') }}" />
                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
            </div>

            @can('create menus')
                <flux:button icon="plus" wire:click="create">
                    {{ __('New Menu') }}
                </flux:button>
            @endcan
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('title')" class="inline-flex items-center gap-1">
                        {{ __('Title') }}
                    </button>
                </th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Group') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Module') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Page') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('URL') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @forelse ($menus as $menu)
                <tr wire:key="menu-{{ $menu->id }}">
                    <td class="px-4 py-2">
                        <div class="font-medium text-neutral-900 dark:text-neutral-50">{{ $menu->title }}</div>
                        <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $menu->slug }}</div>
                    </td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $menu->menuGroup->title ?? '-' }}</td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $menu->module->name ?? '-' }}</td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $menu->page->title ?? '-' }}</td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $menu->url ?: '-' }}</td>
                    <td class="px-4 py-2">
                        <flux:badge :color="$menu->is_active ? 'success' : 'neutral'">
                            {{ $menu->is_active ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            @can('update menus')
                                <flux:button size="xs" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $menu->id }})">{{ __('Edit') }}</flux:button>
                            @endcan
                            @can('delete menus')
                                <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                    wire:click="delete({{ $menu->id }})">{{ __('Delete') }}</flux:button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-slot:rows>

        <x-slot:pagination>
            {{ $menus->links() }}
        </x-slot:pagination>
    </x-data-table>

    <flux:modal wire:model="showModal" focusable class="max-w-2xl w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? __('Edit Menu') : __('New Menu') }}</flux:heading>
                <flux:subheading>{{ __('Assign menu group and module.') }}</flux:subheading>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <flux:field>
                    <flux:label>{{ __('Menu Group') }}</flux:label>
                    <flux:select wire:model.live="menuGroupId">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($menuGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->title }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error for="menuGroupId" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Module') }}</flux:label>
                    <flux:select wire:model.live="moduleId">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error for="moduleId" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Page') }}</flux:label>
                    <flux:select wire:model.live="pageId">
                        <option value="">{{ __('None') }}</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->title }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error for="pageId" />
                </flux:field>
            </div>

            <flux:input wire:model.live="title" :label="__('Title')" autofocus />
            <flux:error for="title" />

            <flux:input wire:model.live="slug" :label="__('Slug')" />
            <flux:error for="slug" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:input wire:model.live="url" :label="__('URL')" placeholder="/about" />
                <flux:input wire:model.live="icon" :label="__('Icon')" placeholder="home" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>{{ __('Target') }}</flux:label>
                    <flux:select wire:model.live="target">
                        <option value="_self">{{ __('Same tab') }}</option>
                        <option value="_blank">{{ __('New tab') }}</option>
                    </flux:select>
                    <flux:error for="target" />
                </flux:field>
                <flux:input type="number" min="0" wire:model.live="sortOrder" :label="__('Sort order')" />
            </div>
            <flux:error for="sortOrder" />

            <flux:checkbox wire:model.live="isActive" :label="__('Active')" />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse pt-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" icon="check" wire:loading.attr="disabled">
                    {{ $editingId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
