<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <x-data-table columns="5" loading-target="search,sortBy,perPage,nextPage,previousPage,gotoPage,setPage"
        :empty="__('No categories found.')">
        <x-slot:toolbar>
            <div class="flex flex-1 min-w-[200px] items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search categories...') }}" />
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
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1">
                        {{ __('Name') }}
                    </button>
                </th>
                <th class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Image') }}
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
        </x-slot:head>

        <x-slot:rows>
            @forelse ($categories as $category)
                <tr wire:key="cat-{{ $category->id }}">
                    <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">
                        {{ $category->name }}
                    </td>
                    <td class="px-4 py-2">
                        @if ($category->image_path)
                            <img src="{{ Storage::disk('public')->url($category->image_path) }}" alt="{{ $category->name }}"
                                class="h-10 w-16 rounded object-cover" />
                        @else
                            <span class="text-neutral-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">
                        {{ $category->slug }}
                    </td>
                    <td class="px-4 py-2 max-w-xs truncate text-neutral-600 dark:text-neutral-400">
                        {{ Str::limit($category->description, 50) ?: '-' }}
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            @can('update categories')
                                <flux:button size="xs" variant="ghost" icon="pencil-square"
                                    wire:click="edit({{ $category->id }})">
                                    {{ __('Edit') }}
                                </flux:button>
                            @endcan

                            @can('delete categories')
                                <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                    wire:click="delete({{ $category->id }})">
                                    {{ __('Delete') }}
                                </flux:button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-slot:rows>

        <x-slot:pagination>
            {{ $categories->links() }}
        </x-slot:pagination>
    </x-data-table>

    <flux:modal wire:model="showModal" focusable class="max-w-xl w-full">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Category') : __('New Category') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Organize posts with categories.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model.live="name" :label="__('Name')" />
            <flux:error for="name" />

            <flux:input wire:model.live="slug" :label="__('Slug')" />
            <flux:error for="slug" />

            <flux:textarea wire:model.live="description" :label="__('Description')" rows="3" />

            <flux:input type="file" wire:model="image" :label="__('Image')" />
            <flux:error for="image" />

            @if ($image)
                <img src="{{ $image->temporaryUrl() }}" alt="{{ __('Category image preview') }}"
                    class="h-24 w-40 rounded object-cover" />
            @elseif ($currentImagePath)
                <img src="{{ Storage::disk('public')->url($currentImagePath) }}" alt="{{ __('Current category image') }}"
                    class="h-24 w-40 rounded object-cover" />
            @endif

            @if ($editingId && $currentImagePath)
                <flux:checkbox wire:model.live="removeImage" :label="__('Delete current image')" />
            @endif

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button type="submit" icon="check" wire:loading.attr="disabled">
                    {{ $editingId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>

</div>
