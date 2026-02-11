<x-layouts::app :title="__('Dashboard - BasicEMS')">
    <div class="flex min-h-screen w-full flex-1 flex-col gap-6">
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

        @php
            $taskSectionTitle = $isShowingFallbackTasks
                ? __('Priority tasks')
                : __('Urgent tasks');
            $taskSectionSubtitle = $isShowingFallbackTasks
                ? __('No urgent tasks. Showing high priority items first.')
                : __('Top priority items that need attention.');
            $priorityLabels = [
                'urgent' => __('Urgent'),
                'high' => __('High'),
                'medium' => __('Medium'),
                'low' => __('Low'),
                'none' => __('None'),
            ];
            $priorityColors = [
                'urgent' => 'red',
                'high' => 'orange',
                'medium' => 'sky',
                'low' => 'zinc',
                'none' => 'zinc',
            ];
        @endphp

        <div class="space-y-1">
            <flux:heading size="lg">{{ $taskSectionTitle }}</flux:heading>
            <flux:subheading>{{ $taskSectionSubtitle }}</flux:subheading>
        </div>

        <div class="flex min-h-0 flex-1 flex-col">
            <flux:card class="flex min-h-0 w-full flex-1 flex-col overflow-hidden">
            @if ($priorityTasks->isEmpty())
                <flux:subheading>{{ __('No tasks yet.') }}</flux:subheading>
            @else
                <div class="min-h-0 w-full flex-1 overflow-y-auto">
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column>{{ __('Title') }}</flux:table.column>
                            <flux:table.column>{{ __('Employee') }}</flux:table.column>
                            <flux:table.column>{{ __('Priority') }}</flux:table.column>
                            <flux:table.column>{{ __('Due Date') }}</flux:table.column>
                            <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($priorityTasks as $task)
                                @php
                                    $priorityLabel = $priorityLabels[$task->priority] ?? ucfirst($task->priority);
                                    $priorityColor = $priorityColors[$task->priority] ?? 'zinc';
                                @endphp
                                <flux:table.row :key="$task->id">
                                    <flux:table.cell variant="strong">
                                        <a href="{{ route('tasks.show', $task) }}" class="hover:underline" wire:navigate>
                                            {{ $task->title }}
                                        </a>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @if ($task->employee)
                                            <a href="{{ route('employees.show', $task->employee) }}" class="hover:underline" wire:navigate>
                                                {{ $task->employee?->full_name ?? __('Unassigned') }}
                                            </a>
                                        @else
                                            {{ __('Unassigned') }}
                                        @endif
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:badge :color="$priorityColor">{{ $priorityLabel }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        {{ $task->due_date?->format('M d, Y') ?? __('No due date') }}
                                    </flux:table.cell>
                                    <flux:table.cell align="end">
                                        <flux:button
                                            href="{{ route('tasks.show', $task) }}"
                                            variant="ghost"
                                            size="sm"
                                            icon="eye"
                                            aria-label="{{ __('View :title', ['title' => $task->title]) }}"
                                            title="{{ __('View') }}"
                                            wire:navigate
                                        />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
                <div class="mt-4">
                    <flux:button href="{{ route('tasks.index') }}" variant="ghost" wire:navigate>
                        {{ __('View all tasks') }}
                    </flux:button>
                </div>
            @endif
            </flux:card>
        </div>
    </div>
</x-layouts::app>
