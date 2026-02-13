<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <x-data-table
        columns="5"
        loading-target="search,sortBy,perPage,nextPage,previousPage,gotoPage,setPage,toggleStatus,deleteConfirmed"
        :empty="__('No users found.')"
    >
        <x-slot:toolbar>
            <div class="flex flex-1 min-w-[200px] items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search users...') }}" />
                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
            </div>

            @can('create users')
                <flux:button icon="plus" wire:click="create">
                    {{ __('New User') }}
                </flux:button>
            @endcan
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1">
                        {{ __('Name') }}
                        @if ($sortField === 'name')
                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                class="size-3.5" />
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('role')" class="inline-flex items-center gap-1">
                        {{ __('Role') }}
                        @if ($sortField === 'role')
                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                class="size-3.5" />
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('email')" class="inline-flex items-center gap-1">
                        {{ __('Email') }}
                        @if ($sortField === 'email')
                            <flux:icon name="{{ $sortDirection === 'asc' ? 'chevron-up' : 'chevron-down' }}"
                                class="size-3.5" />
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Status') }}
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Actions') }}
                </th>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @forelse ($users as $user)
                <tr wire:key="user-{{ $user->id }}">
                    <td class="px-4 py-2 align-middle">
                        <div class="flex items-center gap-2">
                            <flux:avatar :name="$user->name" :initials="$user->initials()" />
                            <div class="flex flex-col">
                                <span class="font-medium text-neutral-900 dark:text-neutral-50">
                                    {{ $user->name }}
                                </span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-2 align-middle">
                        @if ($user->roles->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-2">
                                @foreach ($user->roles as $role)
                                    <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-medium"
                                        style="background-color: {{ $role->color ?: '#6B7280' }}1f; border-color: {{ $role->color ?: '#6B7280' }}66; color: {{ $role->color ?: '#6B7280' }};">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-neutral-700 dark:text-neutral-200">
                                {{ __('No role') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 align-middle">
                        <span class="text-neutral-700 dark:text-neutral-200">
                            {{ $user->email }}
                        </span>
                    </td>
                    <td class="px-4 py-2 align-middle">
                        @can('update users')
                            <button type="button" wire:click="toggleStatus({{ $user->id }})"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-medium transition disabled:opacity-60 {{ $user->is_active
                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                                    : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300' }}">
                                {{ $user->is_active ? __('Active') : __('Deactive') }}
                            </button>
                        @else
                            <flux:badge :color="$user->is_active ? 'green' : 'red'">
                                {{ $user->is_active ? __('Active') : __('Deactive') }}
                            </flux:badge>
                        @endcan
                    </td>
                    <td class="px-4 py-2 align-middle">
                        <div class="flex items-center gap-2">
                            @can('update users')
                                <flux:button size="xs" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $user->id }})">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan

                            @can('delete users')
                                @if ($user->roles->contains('name', 'Super Admin'))
                                    <flux:button size="xs" variant="ghost" color="danger" icon="trash" disabled>
                                        {{ __('Delete') }}
                                    </flux:button>
                                @else
                                    <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                        wire:click="confirmDelete({{ $user->id }})">
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
            {{ $users->links() }}
        </x-slot:pagination>
    </x-data-table>

    <flux:modal wire:model="showDeleteModal" focusable class="max-w-md w-full">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete user?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Are you sure you want to delete') }}
                    <span class="font-semibold">{{ $confirmingDeleteName }}</span>?
                </flux:subheading>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled" wire:click="closeDeleteModal">
                        {{ __('No') }}
                    </flux:button>
                </flux:modal.close>
                <flux:button color="danger" icon="trash" wire:click="deleteConfirmed" wire:loading.attr="disabled">
                    {{ __('Yes') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal wire:model="showModal" focusable class="max-w-xl w-full">
        <form wire:submit="save" class="space-y-6">

            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit User') : __('New User') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Manage user details for your CMS.') }}
                </flux:subheading>
            </div>

            {{-- Name --}}
            <flux:input id="name" type="text" wire:model.live="name" :label="__('Name')" autofocus />
            <flux:error for="name" />

            {{-- Email --}}
            <flux:input id="email" type="email" wire:model.live="email" :label="__('Email')" />
            <flux:error for="email" />

            {{-- Password --}}
            <flux:input id="password" type="password" wire:model.live="password"
                :label="$editingId
                    ?
                    __('Password (leave blank to keep current)') :
                    __('Password')" />
            <flux:error for="password" />

            {{-- Roles --}}
            <flux:field>
                <flux:label>{{ __('Roles') }}</flux:label>

                <div class="flex flex-wrap gap-2">
                    @foreach ($roles as $role)
                        <flux:checkbox wire:model.live="roleIds" :value="$role->id" :label="$role->name" />
                    @endforeach
                </div>
            </flux:field>

            {{-- Actions --}}
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
