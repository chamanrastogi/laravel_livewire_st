<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <flux:card class="max-w-4xl">
        <div class="mb-4 flex items-center justify-between gap-3">
            <flux:heading size="lg">{{ __('Edit Page') }}</flux:heading>
            <flux:button variant="filled" :href="route('admin.pages.index')" wire:navigate>{{ __('Back') }}</flux:button>
        </div>

        <form wire:submit="save" class="space-y-6">
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

            @if ($currentFeaturedImagePath)
                <flux:checkbox wire:model.live="removeFeaturedImage" :label="__('Delete current image')" />
            @endif

            <flux:field>
                <flux:label>{{ __('Content') }}</flux:label>
                <div wire:ignore class="rich-editor">
                    <div id="page-content-editor"></div>
                </div>
                <flux:error for="content" />
            </flux:field>

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

            <flux:separator />
            <flux:heading size="sm">{{ __('SEO') }}</flux:heading>

            <flux:input wire:model.live="metaTitle" :label="__('Meta title')" />
            <flux:textarea wire:model.live="metaDescription" :label="__('Meta description')" rows="2" />
            <flux:input wire:model.live="metaKeywords" :label="__('Meta keywords')" />

            <div class="flex justify-end">
                <flux:button type="submit" icon="check" wire:loading.attr="disabled">{{ __('Update Page') }}</flux:button>
            </div>
        </form>
    </flux:card>

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
            let isSettingPageContent = false;

            const initPageEditor = async () => {
                if (pageEditor || typeof Quill === 'undefined') return;
                const element = document.getElementById('page-content-editor');
                if (!element) return;

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

                const setPageEditorContent = (content = '') => {
                    isSettingPageContent = true;
                    pageEditor.clipboard.dangerouslyPasteHTML(content || '');
                    $wire.set('content', pageEditor.root.innerHTML, true);
                    isSettingPageContent = false;
                };

                setPageEditorContent(@js($content));

                pageEditor.on('text-change', () => {
                    if (isSettingPageContent) return;
                    $wire.set('content', pageEditor.root.innerHTML, true);
                });
            };

            setTimeout(initPageEditor, 0);
            document.addEventListener('livewire:navigated', () => setTimeout(initPageEditor, 0));
        </script>
    @endscript
</div>
