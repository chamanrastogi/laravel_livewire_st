@props([
    'columns' => 1,
    'loadingTarget' => null,
    'loadingClass' => 'opacity-60 pointer-events-none overflow-hidden',
    'empty' => __('No data found.'),
    'emptyClass' => 'px-4 py-6 text-center text-neutral-500 dark:text-neutral-400',
])

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
    @if (isset($toolbar))
        <div class="flex flex-wrap items-center justify-between gap-4">
            {{ $toolbar }}
        </div>
    @endif

    <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700"
        wire:loading.class="{{ $loadingClass }}">
        @if ($loadingTarget)
            <div wire:loading.flex wire:target="{{ $loadingTarget }}"
                class="absolute inset-0 z-10 items-center justify-center bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-8 animate-spin text-neutral-500 dark:text-neutral-400" />
            </div>
        @else
            <div wire:loading.flex class="absolute inset-0 z-10 items-center justify-center bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-8 animate-spin text-neutral-500 dark:text-neutral-400" />
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-neutral-200 text-sm dark:divide-neutral-700">
                @if (isset($head))
                    <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                        {{ $head }}
                    </thead>
                @endif

                <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                    @if (isset($rows) && trim((string) $rows) !== '')
                        {{ $rows }}
                    @else
                        <tr>
                            <td colspan="{{ $columns }}" class="{{ $emptyClass }}">
                                {{ $empty }}
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if (isset($pagination))
            <div class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60">
                {{ $pagination }}
            </div>
        @endif
    </div>
</div>
