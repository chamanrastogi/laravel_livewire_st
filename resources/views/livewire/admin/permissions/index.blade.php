<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <x-data-table columns="3" loading-target="search,perPage,nextPage,previousPage,gotoPage,setPage"
        :empty="__('No permissions found.')">
        <x-slot:toolbar>
            <div class="flex flex-wrap items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search permissions...') }}" />
                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
            </div>
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">#</th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Name') }}
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Guard') }}
                </th>
            </tr>
        </x-slot:head>

        <x-slot:rows>
            @forelse ($permissions as $permission)
                <tr wire:key="perm-{{ $permission->id }}">
                    <td class="px-4 py-2 text-neutral-500">{{ $permission->id }}</td>
                    <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">{{ $permission->name }}</td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $permission->guard_name }}</td>
                </tr>
            @empty
            @endforelse
        </x-slot:rows>

        <x-slot:pagination>
            {{ $permissions->links() }}
        </x-slot:pagination>
    </x-data-table>
</div>
