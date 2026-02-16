<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <x-data-table columns="5" loading-target="search,sortBy,perPage,nextPage,previousPage,gotoPage,setPage"
        :empty="__('No menu groups found.')">
        <x-slot:toolbar>
            <div class="flex flex-1 min-w-[200px] items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search menu groups...') }}" />
                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
            </div>

            @can('create menu groups')
                <flux:button icon="plus" wire:click="create">
                    {{ __('New Group') }}
                </flux:button>
            @endcan
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1">
                        {{ __('Name') }}
                    </button>
                </th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Slug') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Order') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @forelse ($groups as $group)
                <tr wire:key="menu-group-{{ $group->id }}">
                    <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">{{ $group->title }}</td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $group->slug }}</td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $group->sort_order }}</td>
                    <td class="px-4 py-2">
                        <flux:badge :color="$group->is_active ? 'success' : 'neutral'">
                            {{ $group->is_active ? __('Active') : __('Inactive') }}
                        </flux:badge>
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            @can('update menu groups')
                                <flux:button size="xs" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $group->id }})">{{ __('Edit') }}</flux:button>
                            @endcan
                            @can('delete menu groups')
                                <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                    wire:click="delete({{ $group->id }})">{{ __('Delete') }}</flux:button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-slot:rows>

        <x-slot:pagination>
            {{ $groups->links() }}
        </x-slot:pagination>
    </x-data-table>

    <flux:modal wire:model="showModal" focusable class="max-w-xl w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? __('Edit Menu Group') : __('New Menu Group') }}</flux:heading>
                <flux:subheading>{{ __('Group menus for display areas.') }}</flux:subheading>
            </div>

            <flux:input wire:model.live="name" :label="__('Name')" autofocus />
            <flux:error for="name" />

            <flux:input wire:model.live="slug" :label="__('Slug')" />
            <flux:error for="slug" />

            <flux:textarea wire:model.live="description" :label="__('Description')" rows="3" />

            <flux:input type="number" min="0" wire:model.live="sortOrder" :label="__('Sort order')" />
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
