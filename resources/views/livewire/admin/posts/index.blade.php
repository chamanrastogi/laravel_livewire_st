<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('status'))
            <flux:callout variant="success">{{ session('status') }}</flux:callout>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-1 min-w-[200px] items-center gap-3">
                <flux:input
                    icon="magnifying-glass"
                    wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search posts...') }}"
                />
                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
            </div>
            @can('create posts')
                <flux:button icon="plus" wire:click="create">{{ __('New Post') }}</flux:button>
            @endcan
        </div>

        <div class="relative flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700" wire:loading.class="opacity-60 pointer-events-none">
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/80 dark:bg-zinc-900/80" wire:target="search,sortBy,perPage">
                <flux:icon name="arrow-path" class="size-8 animate-spin text-neutral-500 dark:text-neutral-400" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
                    <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                                <button type="button" wire:click="sortBy('title')" class="inline-flex items-center gap-1">{{ __('Title') }}</button>
                            </th>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Slug') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Status') }}</th>
                            <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                        @forelse ($posts as $post)
                            <tr wire:key="post-{{ $post->id }}">
                                <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">{{ $post->title }}</td>
                                <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $post->slug }}</td>
                                <td class="px-4 py-2">
                                    <flux:badge :color="$post->status === 'published' ? 'success' : 'neutral'">{{ $post->status }}</flux:badge>
                                </td>
                                <td class="px-4 py-2">
                                    <div class="flex items-center gap-2">
                                        @can('update posts')
                                            <flux:button size="xs" variant="ghost" icon="pencil-square" wire:click="edit({{ $post->id }})">{{ __('Edit') }}</flux:button>
                                            <flux:button size="xs" variant="ghost" wire:click="togglePublish({{ $post->id }})">
                                                {{ $post->status === 'published' ? __('Unpublish') : __('Publish') }}
                                            </flux:button>
                                        @endcan
                                        @can('delete posts')
                                            <flux:button size="xs" variant="ghost" color="danger" icon="trash" wire:click="delete({{ $post->id }})">{{ __('Delete') }}</flux:button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-neutral-500 dark:text-neutral-400">{{ __('No posts found.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
                {{ $posts->links() }}
            </div>
        </div>

        <flux:modal wire:model="showModal" focusable class="max-w-2xl w-full">
    <form wire:submit="save" class="space-y-6">

        {{-- Header --}}
        <div>
            <flux:heading size="lg">
                {{ $editingId ? __('Edit Post') : __('New Post') }}
            </flux:heading>

            <flux:subheading>
                {{ __('Content, categories and tags.') }}
            </flux:subheading>
        </div>

        {{-- Basic Info --}}
        <flux:input
            wire:model.live="title"
            :label="__('Title')"
            autofocus
        />
        <flux:error for="title" />

        <flux:input
            wire:model.live="slug"
            :label="__('Slug')"
        />
        <flux:error for="slug" />

        <flux:textarea
            wire:model.live="excerpt"
            :label="__('Excerpt')"
            rows="2"
        />

        <flux:textarea
            wire:model.live="content"
            :label="__('Content')"
            rows="6"
        />

        {{-- Publishing --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model.live="status">
                    <option value="draft">{{ __('Draft') }}</option>
                    <option value="published">{{ __('Published') }}</option>
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Published at') }}</flux:label>
                <flux:input type="datetime-local" wire:model.live="publishedAt" />
            </flux:field>
        </div>

        {{-- Categories --}}
        <flux:field>
            <flux:label>{{ __('Categories') }}</flux:label>
            <div class="flex flex-wrap gap-2">
                @foreach ($categories as $cat)
                    <flux:checkbox
                        wire:model.live="categoryIds"
                        :value="$cat->id"
                        :label="$cat->name"
                    />
                @endforeach
            </div>
        </flux:field>

        {{-- Tags --}}
        <flux:field>
            <flux:label>{{ __('Tags') }}</flux:label>
            <div class="flex flex-wrap gap-2">
                @foreach ($tags as $tag)
                    <flux:checkbox
                        wire:model.live="tagIds"
                        :value="$tag->id"
                        :label="$tag->name"
                    />
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
