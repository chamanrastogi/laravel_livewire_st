<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    <flux:heading>{{ __('Dashboard') }}</flux:heading>
    <flux:text class="mb-4">{{ __('Overview of your CMS. Stats depend on your permissions.') }}</flux:text>

    <div class="grid auto-rows-min gap-4 md:grid-cols-2 lg:grid-cols-4">
        @if (isset($stats['users']))
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/30">
                        <flux:icon name="users" class="size-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{{ $stats['users'] }}</div>
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Users') }}</div>
                    </div>
                </div>
            </flux:card>
        @endif
        @if (isset($stats['pages']))
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/30">
                        <flux:icon name="document-text" class="size-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{{ $stats['pages'] }}</div>
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Pages') }} ({{ $stats['pages_published'] ?? 0 }} {{ __('published') }})</div>
                    </div>
                </div>
            </flux:card>
        @endif
        @if (isset($stats['posts']))
            <flux:card class="p-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-12 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/30">
                        <flux:icon name="newspaper" class="size-6 text-violet-600 dark:text-violet-400" />
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-50">{{ $stats['posts'] }}</div>
                        <div class="text-sm text-neutral-600 dark:text-neutral-400">{{ __('Posts') }} ({{ $stats['posts_published'] ?? 0 }} {{ __('published') }})</div>
                    </div>
                </div>
            </flux:card>
        @endif
    </div>

    <div class="relative h-full min-h-[200px] flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
        <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
    </div>
</div>
