<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    {{-- Header / Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-1 min-w-[200px] items-center gap-3">
            <flux:input
                icon="magnifying-glass"
                wire:model.live.debounce.500ms="search"
                placeholder="{{ __('Search categories...') }}"
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

        @can('create categories')
            <flux:button icon="plus" wire:click="create">
                {{ __('New Category') }}
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
            wire:target="search,sortBy,perPage"
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
                            <button
                                type="button"
                                wire:click="sortBy('name')"
                                class="inline-flex items-center gap-1"
                            >
                                {{ __('Name') }}
                            </button>
                        </th>
                        <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Slug') }}
                        </th>
                        <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Description') }}
                        </th>
                        <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                            {{ __('Actions') }}
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @forelse ($categories as $category)
                        <tr wire:key="cat-{{ $category->id }}">
                            <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">
                                {{ $category->name }}
                            </td>

                            <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">
                                {{ $category->slug }}
                            </td>

                            <td class="px-4 py-2 max-w-xs truncate text-neutral-600 dark:text-neutral-400">
                                {{ Str::limit($category->description, 50) ?: '—' }}
                            </td>

                            <td class="px-4 py-2">
                                <div class="flex items-center gap-2">
                                    @can('update categories')
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            icon="pencil-square"
                                            wire:click="edit({{ $category->id }})"
                                        >
                                            {{ __('Edit') }}
                                        </flux:button>
                                    @endcan

                                    @can('delete categories')
                                        <flux:button
                                            size="xs"
                                            variant="ghost"
                                            color="danger"
                                            icon="trash"
                                            wire:click="delete({{ $category->id }})"
                                        >
                                            {{ __('Delete') }}
                                        </flux:button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="4"
                                class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400"
                            >
                                {{ __('No categories found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
            {{ $categories->links() }}
        </div>
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" focusable class="max-w-xl w-full">
        <form wire:submit="save" class="space-y-6">
    
            {{-- Heading --}}
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Category') : __('New Category') }}
                </flux:heading>
    
                <flux:subheading>
                    {{ __('Organize posts with categories.ss') }}
                </flux:subheading>
            </div>
    
            {{-- Fields --}}
            <flux:input
                wire:model.live="name"
                :label="__('Name')"
            />
            <flux:error for="name" />
    
            <flux:input
                wire:model.live="slug"
                :label="__('Slug')"
            />
            <flux:error for="slug" />
    
            <flux:textarea
                wire:model.live="description"
                :label="__('Description')"
                rows="3"
            />
    
            {{-- Actions --}}
            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
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
                    {{ $editingId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
    
        </form>
    </flux:modal>

</div>
