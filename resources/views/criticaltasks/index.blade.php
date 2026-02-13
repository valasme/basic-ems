<x-layouts::app :title="__('Critical Tasks - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center justify-between">
			<flux:heading size="xl">{{ __('Critical Tasks') }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		<form method="GET" action="{{ route('critical-tasks.index') }}" role="search" class="flex flex-col gap-2 sm:flex-row sm:items-center">
			<div class="flex flex-col gap-2 sm:flex-row sm:items-center">
				<flux:input
					type="search"
					name="search"
					placeholder="{{ __('Search critical tasks...') }}"
					value="{{ $search }}"
					icon="magnifying-glass"
					aria-label="{{ __('Search critical tasks') }}"
					class="w-full max-w-xs"
				/>
				<flux:button type="submit">{{ __('Search') }}</flux:button>
			</div>
			<flux:select name="filter" aria-label="{{ __('Filter critical tasks') }}" class="min-w-56">
				<option value="time_priority" @selected($filter === 'time_priority')>{{ __('Time Remaining + Priority') }}</option>
				<option value="priority_only" @selected($filter === 'priority_only')>{{ __('Priority Only') }}</option>
			</flux:select>
			@if ($search || $filter !== 'time_priority')
				<flux:button href="{{ route('critical-tasks.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear') }}
				</flux:button>
			@endif
		</form>

		@if ($tasks->isEmpty())
			<flux:card class="text-center">
				<flux:icon name="exclamation-triangle" class="mx-auto size-12 text-zinc-400" />
				<flux:heading size="lg" class="mt-4">{{ __('No critical tasks found') }}</flux:heading>
				<flux:subheading class="mt-2">
					@if ($search)
						{{ __('No tasks match your search criteria. Try adjusting your search.') }}
					@else
						{{ __('You are all clear. Add or update tasks to see critical items here.') }}
					@endif
				</flux:subheading>
				@unless ($search)
					<div class="mt-6">
						<flux:button href="{{ route('tasks.index') }}" variant="primary" wire:navigate>
							{{ __('View Tasks') }}
						</flux:button>
					</div>
				@endunless
			</flux:card>
		@else
			@php
				$statusLabels = [
					'pending' => __('Pending'),
					'in_progress' => __('In Progress'),
					'completed' => __('Completed'),
				];

				$statusColors = [
					'pending' => 'yellow',
					'in_progress' => 'blue',
					'completed' => 'green',
				];

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

			<flux:table>
				<flux:table.columns>
					<flux:table.column>{{ __('Task') }}</flux:table.column>
					<flux:table.column>{{ __('Employee') }}</flux:table.column>
					<flux:table.column>{{ __('Status') }}</flux:table.column>
					<flux:table.column>{{ __('Priority') }}</flux:table.column>
					<flux:table.column>{{ __('Due Date') }}</flux:table.column>
					<flux:table.column>{{ __('Time Remaining') }}</flux:table.column>
					<flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
				</flux:table.columns>

				<flux:table.rows>
					@foreach ($tasks as $task)
						@php
							$statusLabel = $statusLabels[$task->status] ?? ucfirst(str_replace('_', ' ', $task->status));
							$statusColor = $statusColors[$task->status] ?? 'zinc';
							$priorityLabel = $priorityLabels[$task->priority] ?? ucfirst($task->priority);
							$priorityColor = $priorityColors[$task->priority] ?? 'zinc';
							$daysUntil = $task->due_date
								? (int) now()->startOfDay()->diffInDays($task->due_date, false)
								: null;
							$timeRemaining = $daysUntil === null
								? __('No due date')
								: ($daysUntil < 0
									? __('Overdue by :days days', ['days' => abs($daysUntil)])
									: ($daysUntil === 0
										? __('Due today')
										: __('Due in :days days', ['days' => $daysUntil])));
						@endphp
						<flux:table.row :key="$task->id">
							<flux:table.cell variant="strong">
								<a href="{{ route('tasks.show', $task) }}" class="hover:underline" wire:navigate>
									{{ $task->title }}
								</a>
							</flux:table.cell>
							<flux:table.cell>
								{{ $task->employee?->full_name ?? '-' }}
							</flux:table.cell>
							<flux:table.cell>
								<flux:badge :color="$statusColor">{{ $statusLabel }}</flux:badge>
							</flux:table.cell>
							<flux:table.cell>
								<flux:badge :color="$priorityColor">{{ $priorityLabel }}</flux:badge>
							</flux:table.cell>
							<flux:table.cell>
								{{ $task->due_date?->format('M d, Y') ?? '-' }}
							</flux:table.cell>
							<flux:table.cell>
								<span @class([
									'font-semibold text-red-400' => $daysUntil !== null && $daysUntil <= 1,
									'text-zinc-500' => $daysUntil === null,
								])>{{ $timeRemaining }}</span>
							</flux:table.cell>
							<flux:table.cell align="end">
								<div class="flex items-center justify-end gap-1">
									<flux:button
										href="{{ route('tasks.show', $task) }}"
										variant="ghost"
										size="sm"
										icon="eye"
										aria-label="{{ __('View :title', ['title' => $task->title]) }}"
										title="{{ __('View') }}"
										wire:navigate
									/>
									<flux:button
										href="{{ route('tasks.edit', $task) }}"
										variant="ghost"
										size="sm"
										icon="pencil"
										aria-label="{{ __('Edit :title', ['title' => $task->title]) }}"
										title="{{ __('Edit') }}"
										wire:navigate
									/>
								</div>
							</flux:table.cell>
						</flux:table.row>
					@endforeach
				</flux:table.rows>
			</flux:table>
		@endif
	</div>
</x-layouts::app>
