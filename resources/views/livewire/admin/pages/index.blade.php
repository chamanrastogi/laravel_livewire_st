<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <x-data-table columns="5" loading-target="search,sortBy,perPage,nextPage,previousPage,gotoPage,setPage"
        :empty="__('No pages found.')">
        <x-slot:toolbar>
            <div class="flex flex-1 min-w-[200px] items-center gap-3">
                <flux:input icon="magnifying-glass" wire:model.live.debounce.500ms="search"
                    placeholder="{{ __('Search pages...') }}" />
                <flux:select wire:model.live="perPage" class="w-24">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </flux:select>
                <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ __('per page') }}</span>
            </div>
            @can('create pages')
                <flux:button icon="plus" wire:click="create">{{ __('New Page') }}</flux:button>
            @endcan
        </x-slot:toolbar>

        <x-slot:head>
            <tr>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    <button type="button" wire:click="sortBy('title')" class="inline-flex items-center gap-1">
                        {{ __('Title') }}
                    </button>
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Image') }}
                </th>
                <th scope="col" class="px-4 py-2 text-left font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Slug') }}
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
            @forelse ($pages as $page)
                <tr wire:key="page-{{ $page->id }}">
                    <td class="px-4 py-2 font-medium text-neutral-900 dark:text-neutral-50">{{ $page->title }}</td>
                    <td class="px-4 py-2">
                        @if ($page->featured_image_path)
                            <img src="{{ Storage::disk('public')->url($page->featured_image_path) }}"
                                alt="{{ $page->title }}" class="h-10 w-16 rounded object-cover" />
                        @else
                            <span class="text-neutral-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-neutral-600 dark:text-neutral-400">{{ $page->slug }}</td>
                    <td class="px-4 py-2">
                        <flux:badge :color="$page->status === 'published' ? 'success' : 'neutral'">
                            {{ $page->status }}
                        </flux:badge>
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-2">
                            @can('update pages')
                                <flux:button size="xs" variant="ghost" icon="pencil-square"
                                    :href="route('admin.pages.edit', $page->id)" wire:navigate>{{ __('Edit') }}</flux:button>
                                <flux:button size="xs" variant="ghost" wire:click="togglePublish({{ $page->id }})">
                                    {{ $page->status === 'published' ? __('Unpublish') : __('Publish') }}
                                </flux:button>
                            @endcan
                            @can('delete pages')
                                <flux:button size="xs" variant="ghost" color="danger" icon="trash"
                                    wire:click="delete({{ $page->id }})">{{ __('Delete') }}</flux:button>
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
            @endforelse
        </x-slot:rows>

        <x-slot:pagination>
            {{ $pages->links() }}
        </x-slot:pagination>
    </x-data-table>

    <flux:modal wire:model="showModal" focusable class="max-w-2xl w-full">
        <form wire:submit="save" class="space-y-6">
            {{-- Header --}}
            <div>
                <flux:heading size="lg">
                    {{ $editingId ? __('Edit Page') : __('New Page') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Content and SEO for this page.') }}
                </flux:subheading>
            </div>

            {{-- Basic Info --}}
            <flux:input wire:model.live="title" :label="__('Title')" autofocus />
            <flux:error for="title" />

            <flux:input wire:model.live="slug" :label="__('Slug')" />
            <flux:error for="slug" />

            <flux:input type="file" wire:model="featuredImage" :label="__('Featured image')" />
            <flux:error for="featuredImage" />

            @if ($featuredImage)
                <img src="{{ $featuredImage->temporaryUrl() }}" alt="{{ __('Featured image preview') }}"
                    class="h-24 w-40 rounded object-cover" />
            @elseif ($currentFeaturedImagePath)
                <img src="{{ Storage::disk('public')->url($currentFeaturedImagePath) }}"
                    alt="{{ __('Current featured image') }}" class="h-24 w-40 rounded object-cover" />
            @endif

            @if ($editingId && $currentFeaturedImagePath)
                <flux:checkbox wire:model.live="removeFeaturedImage" :label="__('Delete current image')" />
            @endif

            <flux:field>
                <flux:label>{{ __('Content') }}</flux:label>
                <div wire:ignore class="rich-editor rich-editor--compact">
                    <div id="page-content-editor"></div>
                </div>
                <textarea wire:model.live="content" class="hidden"></textarea>
                <flux:error for="content" />
            </flux:field>

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
                    <flux:error for="publishedAt" />
                </flux:field>
            </div>

            {{-- SEO --}}
            <flux:separator />

            <flux:heading size="sm">
                {{ __('SEO') }}
            </flux:heading>

            <flux:input wire:model.live="metaTitle" :label="__('Meta title')" />

            <flux:textarea wire:model.live="metaDescription" :label="__('Meta description')" rows="2" />

            <flux:input wire:model.live="metaKeywords" :label="__('Meta keywords')" />

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

    @once
        <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
        <style>
            .rich-editor {
                overflow: hidden;
                border: 1px solid #d4d4d8;
                border-radius: 0.75rem;
                background: #ffffff;
                transition: border-color 120ms ease, box-shadow 120ms ease;
            }

            .dark .rich-editor {
                border-color: #3f3f46;
                background: #18181b;
            }

            .rich-editor:focus-within {
                border-color: #2563eb;
                box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            }

            .rich-editor .ql-toolbar.ql-snow {
                border: 0;
                border-bottom: 1px solid #e4e4e7;
                background: #fafafa;
                padding: 0.625rem 0.75rem;
            }

            .dark .rich-editor .ql-toolbar.ql-snow {
                border-bottom-color: #3f3f46;
                background: #27272a;
            }

            .rich-editor .ql-container.ql-snow {
                border: 0;
                font-size: 0.95rem;
            }

            .rich-editor .ql-editor {
                min-height: 22rem;
                padding: 0.9rem 1rem;
                line-height: 1.65;
            }

            .rich-editor--compact .ql-editor {
                min-height: 18rem;
            }

            .rich-editor .ql-editor.ql-blank::before {
                color: #a1a1aa;
                font-style: normal;
                left: 1rem;
                right: 1rem;
            }

            .dark .rich-editor .ql-editor.ql-blank::before {
                color: #71717a;
            }
        </style>
        <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    @endonce

    @script
        <script>
            let pageEditor;
            let pendingPageContent = null;
            let isSettingPageContent = false;

            const initPageEditor = async () => {
                if (pageEditor || typeof Quill === 'undefined') {
                    return;
                }

                const element = document.getElementById('page-content-editor');
                if (!element) {
                    return;
                }

                pageEditor = new Quill(element, {
                    theme: 'snow',
                    placeholder: 'Write your content...',
                    modules: {
                        toolbar: [
                            [{
                                header: [1, 2, 3, false]
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }],
                            ['link', 'blockquote', 'code-block'],
                            ['clean'],
                        ],
                    },
                });

                const initialContent = pendingPageContent !== null ? pendingPageContent : @js($content);
                setPageEditorContent(initialContent);
                pendingPageContent = null;

                pageEditor.on('text-change', () => {
                    if (isSettingPageContent) {
                        return;
                    }

                    $wire.set('content', pageEditor.root.innerHTML, true);
                });
            };

            const setPageEditorContent = (content = '') => {
                if (pageEditor) {
                    isSettingPageContent = true;
                    pageEditor.clipboard.dangerouslyPasteHTML(content || '');
                    $wire.set('content', pageEditor.root.innerHTML, true);
                    isSettingPageContent = false;
                }
            };

            document.addEventListener('livewire:initialized', () => {
                setTimeout(initPageEditor, 0);
            });

            setTimeout(initPageEditor, 0);

            window.addEventListener('quill:set-content', (event) => {
                if (event.detail?.instance !== 'page-content-editor') {
                    return;
                }

                const content = event.detail?.content ?? '';
                if (pageEditor) {
                    setPageEditorContent(content);
                } else {
                    pendingPageContent = content;
                }
            });
        </script>
    @endscript

</div>
