<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4">
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
    </div>

    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"
        wire:loading.class="opacity-60 pointer-events-none">
        <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/80 dark:bg-zinc-900/80"
            wire:target="search,sortBy,perPage">
            <flux:icon name="arrow-path" class="size-8 animate-spin text-neutral-500 dark:text-neutral-400" />
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                    <tr>
                        <th scope="col"
                            class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1">
                                {{ __('Name') }}
                            </button>
                        </th>
                        <th scope="col"
                            class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            <button type="button" wire:click="sortBy('email')" class="inline-flex items-center gap-1">
                                {{ __('Email') }}
                            </button>
                        </th>
                        <th scope="col"
                            class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
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
                                <span class="text-neutral-700 dark:text-neutral-200">
                                    {{ $user->email }}
                                </span>
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
                                        <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                            wire:click="delete({{ $user->id }})">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">
                                {{ __('No users found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div
            class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
            {{ $users->links() }}
        </div>
    </div>

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
