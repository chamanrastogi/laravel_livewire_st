<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    {{-- Success Message --}}
    @if (session('status'))
        <flux:callout variant="success">
            {{ session('status') }}
        </flux:callout>
    @endif

    {{-- Header / Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-1 min-w-[240px] items-center gap-3">
            <flux:input
                icon="magnifying-glass"
                wire:model.live.debounce.500ms="search"
                placeholder="{{ __('Search roles…') }}"
            />

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
    </div>

    {{-- Table --}}
    <div
        class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"
        wire:loading.class="opacity-60 pointer-events-none"
    >
        {{-- Loading Overlay --}}
        <div
            wire:loading.flex
            wire:target="search,perPage"
            class="absolute inset-0 z-10 items-center justify-center bg-white/80 dark:bg-zinc-900/80"
        >
            <flux:icon
                name="arrow-path"
                class="size-8 animate-spin text-neutral-500 dark:text-neutral-400"
            />
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Role') }}
                        </th>
                        <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Permissions') }}
                        </th>
                        <th class="px-4 py-2 text-right font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($roles as $role)
                        <tr
                            wire:key="role-{{ $role->id }}"
                            class="transition hover:bg-neutral-50 dark:hover:bg-neutral-900/40"
                        >
                            <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">
                                {{ $role->name }}
                            </td>

                            <td class="px-4 py-2">
                                <flux:badge size="sm">
                                    {{ $role->permissions_count }} {{ __('permissions') }}
                                </flux:badge>
                            </td>

                            <td class="px-4 py-2">
                                <div class="flex justify-end gap-2">
                                    @can('update roles')
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="pencil-square"
                                            wire:click="edit({{ $role->id }})"
                                        >
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan

                                    @can('delete roles')
                                        @if ($role->name !== 'Super Admin')
                                            <flux:button
                                                size="xs"
                                                variant="ghost"
                                                color="danger"
                                                icon="trash"
                                                wire:click="delete({{ $role->id }})"
                                            >
                                                {{ __('Delete') }}
                                            </flux:button>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="3"
                                class="px-4 py-8 text-center text-neutral-500 dark:text-neutral-400"
                            >
                                {{ __('No roles found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
            {{ $roles->links() }}
        </div>
    </div>

    {{-- Role Modal --}}
    <flux:modal wire:model="showModal" focusable class="max-w-2xl w-full">
        <form wire:submit="save" class="space-y-6">

            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Role') : __('New Role') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Assign a name and permissions to this role.') }}
                </flux:subheading>
            </div>

            {{-- Role Name --}}
            <flux:input
                id="role-name"
                wire:model.live="name"
                :label="__('Name')"
                autofocus
            />
            <flux:error for="name" />

            {{-- Permissions --}}
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
                                    <flux:checkbox
                                        wire:model.live="permissionIds"
                                        :value="$perm->id"
                                        :label="$perm->name"
                                    />
                                @endforeach
                            </div>
                        </div>
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

                <flux:button
                    type="submit"
                    icon="check"
                    wire:loading.attr="disabled"
                >
                    {{ __('Save') }}
                </flux:button>
            </div>

        </form>
    </flux:modal>

</div>
