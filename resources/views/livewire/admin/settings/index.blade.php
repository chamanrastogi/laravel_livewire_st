<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        @if (session('status'))
            <flux:callout variant="success">{{ session('status') }}</flux:callout>
        @endif

        <flux:card class="max-w-2xl">
            <flux:heading class="mb-1">{{ __('Site settings') }}</flux:heading>
            <flux:text class="mb-4">{{ __('General and SEO configuration.') }}</flux:text>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Site name') }}</flux:label>
                    <flux:input wire:model.live="siteName" placeholder="My CMS" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Site description') }}</flux:label>
                    <flux:textarea wire:model.live="siteDescription" rows="2" placeholder="A short description of the site." />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Logo') }}</flux:label>
                    <flux:input type="file" wire:model="logo" />
                    <flux:error for="logo" />
                    @if ($logo)
                        <img src="{{ $logo->temporaryUrl() }}" alt="{{ __('Logo preview') }}" class="mt-2 h-16 w-auto rounded object-contain" />
                    @elseif ($currentLogoPath)
                        <img src="{{ Storage::disk('public')->url($currentLogoPath) }}" alt="{{ __('Current logo') }}"
                            class="mt-2 h-16 w-auto rounded object-contain" />
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Favicon') }}</flux:label>
                    <flux:input type="file" wire:model="favicon" />
                    <flux:error for="favicon" />
                    @if ($favicon)
                        <img src="{{ $favicon->temporaryUrl() }}" alt="{{ __('Favicon preview') }}" class="mt-2 size-10 rounded object-contain" />
                    @elseif ($currentFaviconPath)
                        <img src="{{ Storage::disk('public')->url($currentFaviconPath) }}" alt="{{ __('Current favicon') }}"
                            class="mt-2 size-10 rounded object-contain" />
                    @endif
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input type="email" wire:model.live="siteEmail" placeholder="hello@example.com" />
                    <flux:error for="siteEmail" />
                </flux:field>

                <flux:field>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model.live="sitePhone" placeholder="+1 555 123 4567" />
                    <flux:error for="sitePhone" />
                </flux:field>

                <flux:separator />
                <flux:heading size="sm">{{ __('SEO') }}</flux:heading>
                <flux:field>
                    <flux:label>{{ __('Meta title') }}</flux:label>
                    <flux:input wire:model.live="metaTitle" placeholder="Default page title" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Meta description') }}</flux:label>
                    <flux:textarea wire:model.live="metaDescription" rows="2" placeholder="Default meta description" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('Meta keywords') }}</flux:label>
                    <flux:input wire:model.live="metaKeywords" placeholder="keyword1, keyword2" />
                </flux:field>
            </div>

            <flux:separator class="my-4" />
            @can('update settings')
                <div class="flex justify-end">
                    <flux:button icon="check" wire:click="save" wire:loading.attr="disabled">{{ __('Save settings') }}</flux:button>
                </div>
            @endcan
        </flux:card>
</div>
