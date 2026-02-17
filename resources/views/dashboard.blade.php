<x-layouts::app :title="__('Dashboard - BasicEMS')">
    <main class="flex min-h-screen w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
        <div class="space-y-1">
            <flux:heading id="page-title" size="xl">{{ __('Dashboard') }}</flux:heading>
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3" aria-label="{{ __('Quick stats') }}">
            @foreach ($quickStats as $stat)
                <flux:card class="flex h-full flex-col gap-4" role="region" aria-label="{{ $stat['label'] }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="space-y-2">
                            <flux:subheading>{{ $stat['label'] }}</flux:subheading>
                            <flux:heading size="lg">{{ $stat['value'] }}</flux:heading>
                        </div>
                        <flux:icon name="{{ $stat['icon'] }}" class="size-6 text-zinc-500" aria-hidden="true" />
                    </div>
                    <flux:button href="{{ $stat['href'] }}" variant="ghost" wire:navigate>
                        {{ $stat['link'] }}
                    </flux:button>
                </flux:card>
            @endforeach
        </section>

        @php
            $taskSectionTitle = $isShowingFallbackTasks
                ? __('Priority tasks')
                : __('Urgent tasks');
            $taskSectionSubtitle = $isShowingFallbackTasks
                ? __('No urgent tasks. Showing high priority items first.')
                : __('Top priority items that need attention.');

            if ($dashboardLoadError ?? false) {
                $taskSectionTitle = __('Tasks unavailable');
                $taskSectionSubtitle = __('There was a problem loading dashboard data. Please refresh and try again.');
            }

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

        <div class="flex items-start justify-between gap-3" aria-labelledby="priority-tasks-title" aria-describedby="priority-tasks-subtitle">
            <div class="space-y-1">
                <flux:heading id="priority-tasks-title" size="lg">{{ $taskSectionTitle }}</flux:heading>
                <flux:subheading id="priority-tasks-subtitle">{{ $taskSectionSubtitle }}</flux:subheading>
            </div>
            <flux:button href="{{ route('tasks.index') }}" variant="ghost" wire:navigate>
                {{ __('View all tasks') }}
            </flux:button>
        </div>

        @if ($dashboardLoadError ?? false)
            <flux:card>
                <flux:subheading>{{ __('Dashboard metrics are temporarily unavailable.') }}</flux:subheading>
            </flux:card>
        @endif

        <div class="flex min-h-0 flex-1 flex-col">
            <flux:card class="flex min-h-0 w-full flex-1 flex-col overflow-hidden">
            <p class="sr-only" aria-live="polite">
                {{ trans_choice('{0} No priority tasks found|{1} :count priority task found|[2,*] :count priority tasks found', $priorityTasks->count(), ['count' => $priorityTasks->count()]) }}
            </p>
            @if ($priorityTasks->isEmpty())
                <flux:subheading role="status" aria-live="polite">{{ __('No tasks yet.') }}</flux:subheading>
            @else
                <div class="min-h-0 w-full flex-1 overflow-y-auto">
                    <flux:table aria-label="{{ __('Priority tasks list') }}" aria-describedby="priority-tasks-caption">
                        <caption id="priority-tasks-caption" class="sr-only">
                            {{ __('Priority tasks showing title, assigned employee, priority, due date, and actions.') }}
                        </caption>
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
            @endif
            </flux:card>
        </div>

        <div class="flex items-start justify-between gap-3" aria-labelledby="due-payments-title" aria-describedby="due-payments-subtitle">
            <div class="space-y-1">
                <flux:heading id="due-payments-title" size="lg">{{ __('Due payments') }}</flux:heading>
                <flux:subheading id="due-payments-subtitle">{{ __('Upcoming pay dates sorted by time.') }}</flux:subheading>
            </div>
            <flux:button href="{{ route('due-payments.index') }}" variant="ghost" wire:navigate>
                {{ __('View all due payments') }}
            </flux:button>
        </div>

        <div class="flex min-h-0 flex-1 flex-col">
            <flux:card class="flex min-h-0 w-full flex-1 flex-col overflow-hidden">
                <p class="sr-only" aria-live="polite">
                    {{ trans_choice('{0} No due payments found|{1} :count due payment found|[2,*] :count due payments found', $duePaymentsPreview->count(), ['count' => $duePaymentsPreview->count()]) }}
                </p>
                @if ($duePaymentsPreview->isEmpty())
                    <flux:subheading role="status" aria-live="polite">{{ __('No due payments yet.') }}</flux:subheading>
                @else
                    <div class="min-h-0 w-full flex-1 overflow-y-auto">
                        <flux:table aria-label="{{ __('Due payments list') }}" aria-describedby="due-payments-caption">
                            <caption id="due-payments-caption" class="sr-only">
                                {{ __('Due payments preview showing employee, pay amount, next pay date, and actions.') }}
                            </caption>
                            <flux:table.columns>
                                <flux:table.column>{{ __('Employee') }}</flux:table.column>
                                <flux:table.column>{{ __('Pay Amount') }}</flux:table.column>
                                <flux:table.column>{{ __('Next Pay Date') }}</flux:table.column>
                                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                            </flux:table.columns>

                            <flux:table.rows>
                                @foreach ($duePaymentsPreview as $employee)
                                    @php
                                        $daysUntil = $employee->days_until_pay;
                                        $daysLabel = $daysUntil === 0
                                            ? __('Today')
                                            : ($daysUntil === 1
                                                ? __('Tomorrow')
                                                : __('In :days days', ['days' => $daysUntil]));
                                    @endphp
                                    <flux:table.row :key="$employee->id">
                                        <flux:table.cell variant="strong">
                                            <a href="{{ route('employees.show', $employee) }}" class="hover:underline" wire:navigate>
                                                {{ $employee->full_name }}
                                            </a>
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            {{ $employee->pay_amount !== null ? '$' . number_format((float) $employee->pay_amount, 2) : '-' }}
                                        </flux:table.cell>
                                        <flux:table.cell>
                                            <div class="flex flex-col">
                                                <span>{{ $employee->next_pay_date?->format('M d, Y') ?? '-' }}</span>
                                                @if ($employee->next_pay_date && $daysUntil !== null)
                                                    <span class="text-xs text-zinc-500">{{ $daysLabel }}</span>
                                                @endif
                                            </div>
                                        </flux:table.cell>
                                        <flux:table.cell align="end">
                                            <flux:button
                                                href="{{ route('employees.show', $employee) }}"
                                                variant="ghost"
                                                size="sm"
                                                icon="eye"
                                                aria-label="{{ __('View :name', ['name' => $employee->full_name]) }}"
                                                title="{{ __('View') }}"
                                                wire:navigate
                                            />
                                        </flux:table.cell>
                                    </flux:table.row>
                                @endforeach
                            </flux:table.rows>
                        </flux:table>
                    </div>
                @endif
            </flux:card>
        </div>
    </main>
</x-layouts::app>
