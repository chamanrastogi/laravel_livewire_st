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
