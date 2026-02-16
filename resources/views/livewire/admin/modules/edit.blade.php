<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (session('status'))
        <flux:callout variant="success">{{ session('status') }}</flux:callout>
    @endif

    <flux:card class="max-w-2xl">
        <div class="mb-4 flex items-center justify-between gap-3">
            <flux:heading size="lg">{{ __('Edit Module') }}</flux:heading>
            <flux:button variant="filled" :href="route('admin.modules.index')" wire:navigate>{{ __('Back') }}</flux:button>
        </div>

        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model.live="name" :label="__('Name')" autofocus />
            <flux:error for="name" />

            <flux:input wire:model.live="slug" :label="__('Slug')" />
            <flux:error for="slug" />

            <flux:textarea wire:model.live="description" :label="__('Description')" rows="3" />

            <flux:checkbox wire:model.live="isActive" :label="__('Active')" />

            <div class="flex justify-end">
                <flux:button type="submit" icon="check" wire:loading.attr="disabled">{{ __('Update Module') }}</flux:button>
            </div>
        </form>
    </flux:card>
</div>
