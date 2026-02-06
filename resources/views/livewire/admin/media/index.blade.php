<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    {{-- Status --}}
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
                placeholder="{{ __('Search media…') }}"
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

        @can('create media')
            <flux:button icon="plus" wire:click="openUpload">
                {{ __('Upload') }}
            </flux:button>
        @endcan
    </div>

    {{-- Media Grid --}}
    <div
        class="relative flex-1"
        wire:loading.class="opacity-60 pointer-events-none"
    >
        {{-- Loading --}}
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

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
            @forelse ($media as $item)
                <div
                    wire:key="media-{{ $item->id }}"
                    class="group relative overflow-hidden rounded-xl border border-neutral-200 bg-neutral-50 transition hover:shadow-sm dark:border-neutral-700 dark:bg-neutral-900/40"
                >
                    <button
                        type="button"
                        class="block w-full focus:outline-none"
                        wire:click="preview({{ $item->id }})"
                    >
                        @if (str_starts_with($item->mime_type ?? '', 'image/'))
                            <img
                                src="{{ Storage::disk($item->disk)->url($item->path) }}"
                                alt="{{ $item->alt_text ?? $item->original_name }}"
                                class="aspect-square w-full object-cover"
                            />
                        @else
                            <div class="flex aspect-square w-full items-center justify-center text-neutral-400">
                                <flux:icon name="document" class="size-12" />
                            </div>
                        @endif
                    </button>

                    <div
                        class="truncate px-2 py-1 text-xs text-neutral-600 dark:text-neutral-400"
                        title="{{ $item->original_name }}"
                    >
                        {{ $item->original_name }}
                    </div>

                    <div class="absolute right-2 top-2 flex gap-1 opacity-0 transition group-hover:opacity-100">
                        @can('update media')
                            <flux:button
                                size="xs"
                                variant="ghost"
                                icon="pencil"
                                wire:click.stop="edit({{ $item->id }})"
                            />
                        @endcan

                        @can('delete media')
                            <flux:button
                                size="xs"
                                variant="ghost"
                                color="danger"
                                icon="trash"
                                wire:click.stop="delete({{ $item->id }})"
                            />
                        @endcan
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-neutral-500 dark:text-neutral-400">
                    {{ __('No media files yet.') }}
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4 border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
            {{ $media->links() }}
        </div>
    </div>

    {{-- Preview Modal --}}
    <flux:modal wire:model="showPreviewModal" focusable class="max-w-2xl w-full">
        @if ($previewId)
            @php $preview = \App\Models\Media::find($previewId); @endphp

            @if ($preview && str_starts_with($preview->mime_type ?? '', 'image/'))
                <div class="space-y-4">
                    <img
                        src="{{ Storage::disk($preview->disk)->url($preview->path) }}"
                        alt="{{ $preview->alt_text ?? $preview->original_name }}"
                        class="w-full rounded-lg"
                    />

                    <div class="text-sm text-neutral-600 dark:text-neutral-400">
                        {{ $preview->original_name }}
                    </div>

                    <div class="flex justify-end">
                        <flux:modal.close>
                            <flux:button variant="filled">
                                {{ __('Close') }}
                            </flux:button>
                        </flux:modal.close>
                    </div>
                </div>
            @endif
        @endif
    </flux:modal>

    {{-- Upload Modal --}}
    <flux:modal wire:model="showUploadModal" focusable class="max-w-xl w-full">
        <form wire:submit="saveUpload" class="space-y-6">

            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ __('Upload file') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Max 10MB. Stored in public disk.') }}
                </flux:subheading>
            </div>

            {{-- Fields --}}
            <flux:input
                type="file"
                wire:model="uploadedFile"
                :label="__('File')"
            />
            <flux:error for="uploadedFile" />

            <flux:input
                wire:model.live="altText"
                :label="__('Alt text')"
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
                    wire:target="uploadedFile,saveUpload"
                >
                    {{ __('Upload') }}
                </flux:button>
            </div>

        </form>
    </flux:modal>

    {{-- Edit Modal --}}
    <flux:modal wire:model="showEditModal" focusable class="max-w-xl w-full">
        <form wire:submit="update" class="space-y-6">

            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ __('Edit Media Details') }}
                </flux:heading>
            </div>

            {{-- Fields --}}
            <flux:input
                wire:model="editName"
                :label="__('Name')"
            />

            <flux:input
                wire:model="editAltText"
                :label="__('Alt text')"
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
                    wire:target="update"
                >
                    {{ __('Save Changes') }}
                </flux:button>
            </div>

        </form>
    </flux:modal>

</div>
