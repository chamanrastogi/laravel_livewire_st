@props(['rows' => 10, 'columns' => 4])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }} role="status" aria-label="{{ __('Loading') }}">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-neutral-200 dark:divide-neutral-700 text-sm">
            <thead class="bg-neutral-50 dark:bg-neutral-900/40">
                <tr>
                    @for ($c = 0; $c < $columns; $c++)
                        <th class="px-4 py-2"><div class="h-4 rounded bg-neutral-200 dark:bg-neutral-700 w-24"></div></th>
                    @endfor
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @for ($r = 0; $r < $rows; $r++)
                    <tr>
                        @for ($c = 0; $c < $columns; $c++)
                            <td class="px-4 py-2"><div class="h-4 rounded bg-neutral-100 dark:bg-neutral-800 w-full max-w-[200px]"></div></td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
    <div class="border-t border-neutral-200 bg-neutral-50/60 px-4 py-3 dark:border-neutral-700 dark:bg-neutral-900/60 flex justify-center gap-2">
        <div class="h-8 w-24 rounded bg-neutral-200 dark:bg-neutral-700"></div>
        <div class="h-8 w-24 rounded bg-neutral-200 dark:bg-neutral-700"></div>
    </div>
</div>
