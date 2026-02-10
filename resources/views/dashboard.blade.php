<x-layouts::app :title="__('Dashboard - BasicEMS')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="space-y-1">
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading>{{ __('Quick stats for your workspace.') }}</flux:subheading>
        </div>

        @php
            $quickStats = [
                [
                    'label' => __('Employees'),
                    'value' => $employeesCount,
                    'href' => route('employees.index'),
                    'link' => __('View employees'),
                    'icon' => 'users',
                ],
                [
                    'label' => __('Tasks'),
                    'value' => $tasksCount,
                    'href' => route('tasks.index'),
                    'link' => __('View tasks'),
                    'icon' => 'check-circle',
                ],
                [
                    'label' => __('Notes'),
                    'value' => $notesCount,
                    'href' => route('notes.index'),
                    'link' => __('View notes'),
                    'icon' => 'document-text',
                ],
            ];
        @endphp

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($quickStats as $stat)
                <flux:card class="flex h-full flex-col gap-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-2">
                            <flux:subheading>{{ $stat['label'] }}</flux:subheading>
                            <flux:heading size="lg">{{ $stat['value'] }}</flux:heading>
                        </div>
                        <flux:icon name="{{ $stat['icon'] }}" class="size-6 text-zinc-500" />
                    </div>
                    <flux:button href="{{ $stat['href'] }}" variant="ghost" wire:navigate>
                        {{ $stat['link'] }}
                    </flux:button>
                </flux:card>
            @endforeach
        </div>
    </div>
</x-layouts::app>
