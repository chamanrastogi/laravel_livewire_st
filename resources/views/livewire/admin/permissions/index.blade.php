<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('status'))
            <flux:callout variant="success">{{ session('status') }}</flux:callout>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                icon="magnifying-glass"
                wire:model.live.debounce.500ms="search"
                placeholder="{{ __('Search permissions...') }}"
            />
            <flux:select wire:model.live="perPage" class="w-24">
                @foreach ($perPageOptions as $n)
                    <option value="{{ $n }}">{{ $n }}</option>
                @endforeach
            </flux:select>
            <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
        </div>

        <div class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700" wire:loading.class="opacity-60 pointer-events-none">
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/80 dark:bg-zinc-900/80" wire:target="search,perPage">
                <flux:icon name="arrow-path" class="size-8 animate-spin text-neutral-500 dark:text-neutral-400" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">#</th>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Name') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Guard') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($permissions as $permission)
                            <tr wire:key="perm-{{ $permission->id }}">
                                <td class="px-4 py-2 text-neutral-500">{{ $permission->id }}</td>
                                <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">{{ $permission->name }}</td>
                                <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $permission->guard_name }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">{{ __('No permissions found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
                {{ $permissions->links() }}
            </div>
        </div>
</div>
