<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">
            {{ session('status') }}
        </flux:callout>
    @endif

    <x-data-table columns="4" loading-target="search,perPage,nextPage,previousPage,gotoPage,setPage"
        :empty="__('No roles found.')">
        <x-slot:toolbar>
            <div class="flex flex-1 min-w-[240px] items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search roles...') }}" />

                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>

                <span class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ __('per page') }}
                </span>
            </div>

            @can('create roles')
                <flux:button icon="plus" wire:click="create">
                    {{ __('New Role') }}
                </flux:button>
            @endcan
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Role') }}
                </th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Color') }}
                </th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Permissions') }}
                </th>
                <th class="px-4 py-2 text-right font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Actions') }}
                </th>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @forelse ($roles as $role)
                <tr wire:key="role-{{ $role->id }}" class="transition hover:bg-neutral-50 dark:hover:bg-neutral-900/40">
                    <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">
                        {{ $role->name }}
                    </td>

                    <td class="px-4 py-2">
                        <span class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-medium"
                            style="background-color: {{ $role->color ?: '#6B7280' }}1f; border-color: {{ $role->color ?: '#6B7280' }}66; color: {{ $role->color ?: '#6B7280' }};">
                            <span class="h-2.5 w-2.5 rounded-full"
                                style="background-color: {{ $role->color ?: '#6B7280' }};"></span>
                            {{ $role->color ?: '#6B7280' }}
                        </span>
                    </td>

                    <td class="px-4 py-2">
                        <flux:badge size="sm">
                            {{ $role->permissions_count }} {{ __('permissions') }}
                        </flux:badge>
                    </td>

                    <td class="px-4 py-2">
                        <div class="flex justify-end gap-2">
                            @can('update roles')
                                <flux:button size="xs" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $role->id }})">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan

                            @can('delete roles')
                                @if ($role->name !== 'Super Admin')
                                    <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                        wire:click="delete({{ $role->id }})">
                                        {{ __('Delete') }}
                                    </flux:button>
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-slot:rows>

        <x-slot:pagination>
            {{ $roles->links() }}
        </x-slot:pagination>
    </x-data-table>

    <flux:modal wire:model="showModal" focusable class="max-w-2xl w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Role') : __('New Role') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Assign a name and permissions to this role.') }}
                </flux:subheading>
            </div>

            <flux:input id="role-name" wire:model.live="name" :label="__('Name')" autofocus />
            <flux:error for="name" />

            <flux:field>
                <flux:label>{{ __('Badge color') }}</flux:label>
                <div class="flex items-center gap-3">
                    <input id="role-color" type="color" wire:model.live="color"
                        class="h-10 w-16 cursor-pointer rounded-md border border-neutral-300 bg-white p-1 dark:border-neutral-700 dark:bg-neutral-900" />
                    <flux:input id="role-color-hex" wire:model.live="color" placeholder="#6B7280" />
                </div>
                <flux:error for="color" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Permissions') }}</flux:label>

                <div class="max-h-56 space-y-4 overflow-y-auto rounded-lg border border-neutral-200 p-3 dark:border-neutral-700">
                    @foreach ($permissions->groupBy(fn ($p) => explode(' ', $p->name)[1] ?? 'other') as $module => $perms)
                        <div>
                            <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-neutral-500 dark:text-neutral-400">
                                {{ $module }}
                            </div>

                            <div class="flex flex-wrap gap-2">
                                @foreach ($perms as $perm)
                                    <flux:checkbox wire:model.live="permissionIds" :value="$perm->id" :label="$perm->name" />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:field>

            <div class="flex justify-end space-x-2 rtl:space-x-reverse pt-2">
                <flux:modal.close>
                    <flux:button variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button type="submit" icon="check" wire:loading.attr="disabled">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

</div>
