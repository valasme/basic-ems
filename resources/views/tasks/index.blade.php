<x-layouts::app :title="__('Tasks - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center justify-between">
			<flux:heading id="page-title" size="xl">{{ __('Tasks') }}</flux:heading>
			<flux:button href="{{ route('tasks.create') }}" variant="primary" wire:navigate>
				{{ __('Add Task') }}
			</flux:button>
		</div>

		@if (session('success'))
			<div x-data="{ open: true }" x-show="open">
				<flux:callout variant="success" role="status" aria-live="polite">
					<div class="flex items-start gap-4">
						<div class="mt-0.5 flex size-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600" aria-hidden="true">
							<flux:icon name="check-circle" class="size-4" />
						</div>
						<div class="min-w-0 flex-1">
							<flux:heading size="sm">{{ __('Success') }}</flux:heading>
							<flux:subheading class="mt-1">
								{{ session('success') }}
							</flux:subheading>
						</div>
						<flux:button
							variant="ghost"
							size="sm"
							icon="x-mark"
							class="shrink-0"
							x-on:click="open = false"
							aria-label="{{ __('Dismiss notification') }}"
						/>
					</div>
				</flux:callout>
			</div>
		@endif

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		<form method="GET" action="{{ route('tasks.index') }}" role="search" aria-describedby="tasks-search-help" class="flex flex-col gap-2 sm:flex-row sm:items-center">
			<p id="tasks-search-help" class="sr-only">
				{{ __('Search tasks by text and sort the list using the filter dropdown.') }}
			</p>
			<div class="flex flex-col gap-2 sm:flex-row sm:items-center">
				<label for="tasks-search" class="sr-only">{{ __('Search tasks') }}</label>
				<flux:input
					id="tasks-search"
					type="search"
					name="search"
					placeholder="{{ __('Search tasks...') }}"
					value="{{ $search }}"
					icon="magnifying-glass"
					class="w-full max-w-xs"
				/>
				<flux:button type="submit">{{ __('Search') }}</flux:button>
			</div>
			<label for="tasks-filter" class="sr-only">{{ __('Sort tasks') }}</label>
			<flux:select id="tasks-filter" name="filter" aria-label="{{ __('Sort tasks') }}" class="min-w-56">
				<option value="priority_status" @selected($filter === 'priority_status')>{{ __('Priority + Status') }}</option>
				<option value="priority_only" @selected($filter === 'priority_only')>{{ __('Priority Only') }}</option>
				<option value="title_alpha" @selected($filter === 'title_alpha')>{{ __('Title (A-Z)') }}</option>
				<option value="title_reverse" @selected($filter === 'title_reverse')>{{ __('Title (Z-A)') }}</option>
				<option value="employee_alpha" @selected($filter === 'employee_alpha')>{{ __('Employee (A-Z)') }}</option>
				<option value="employee_reverse" @selected($filter === 'employee_reverse')>{{ __('Employee (Z-A)') }}</option>
				<option value="status_workflow" @selected($filter === 'status_workflow')>{{ __('Status (Workflow)') }}</option>
				<option value="status_reverse" @selected($filter === 'status_reverse')>{{ __('Status (Reverse)') }}</option>
				<option value="due_date_soonest" @selected($filter === 'due_date_soonest')>{{ __('Due Date (Soonest)') }}</option>
				<option value="due_date_latest" @selected($filter === 'due_date_latest')>{{ __('Due Date (Latest)') }}</option>
				<option value="created_newest" @selected($filter === 'created_newest')>{{ __('Created Date (Newest)') }}</option>
			</flux:select>
			@if ($search || $filter !== 'priority_status')
				<flux:button href="{{ route('tasks.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear Filters') }}
				</flux:button>
			@endif
		</form>

		<p class="sr-only" aria-live="polite">
			{{ trans_choice('{0} No tasks found|{1} :count task found|[2,*] :count tasks found', $tasks->total(), ['count' => $tasks->total()]) }}
		</p>

		@if ($tasks->isEmpty())
			<flux:card class="text-center" role="status" aria-live="polite">
				<flux:icon name="check-circle" class="mx-auto size-12 text-zinc-400" aria-hidden="true" />
				<flux:heading size="lg" class="mt-4">{{ __('No tasks found') }}</flux:heading>
				<flux:subheading class="mt-2">
					@if ($search)
						{{ __('No tasks match your search criteria. Try adjusting your search.') }}
					@else
						{{ __('Get started by adding your first task.') }}
					@endif
				</flux:subheading>
				@unless ($search)
					<div class="mt-6">
						<flux:button href="{{ route('tasks.create') }}" variant="primary" wire:navigate>
							{{ __('Add Task') }}
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

			<flux:table aria-label="{{ __('Tasks list') }}" aria-describedby="tasks-table-caption">
				<caption id="tasks-table-caption" class="sr-only">
					{{ __('Task list showing title, employee, status, priority, due date, and available actions.') }}
				</caption>
				<flux:table.columns>
					<flux:table.column>{{ __('Title') }}</flux:table.column>
					<flux:table.column>{{ __('Employee') }}</flux:table.column>
					<flux:table.column>{{ __('Status') }}</flux:table.column>
					<flux:table.column>{{ __('Priority') }}</flux:table.column>
					<flux:table.column>{{ __('Due Date') }}</flux:table.column>
					<flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
				</flux:table.columns>

				<flux:table.rows>
					@foreach ($tasks as $task)
						@php
							$statusLabel = $statusLabels[$task->status] ?? ucfirst(str_replace('_', ' ', $task->status));
							$statusColor = $statusColors[$task->status] ?? 'zinc';
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
										{{ $task->employee->full_name }}
									</a>
								@else
									{{ __('Unassigned') }}
								@endif
							</flux:table.cell>
							<flux:table.cell>
								<flux:badge :color="$statusColor">{{ $statusLabel }}</flux:badge>
							</flux:table.cell>
							<flux:table.cell>
								<flux:badge :color="$priorityColor">{{ $priorityLabel }}</flux:badge>
							</flux:table.cell>
							<flux:table.cell>{{ $task->due_date?->format('M d, Y') ?? '-' }}</flux:table.cell>
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
									<flux:modal.trigger :name="'delete-task-'.$task->id">
										<flux:button
											variant="ghost"
											size="sm"
											icon="trash"
											aria-label="{{ __('Delete :title', ['title' => $task->title]) }}"
											title="{{ __('Delete') }}"
										/>
									</flux:modal.trigger>
								</div>
							</flux:table.cell>
						</flux:table.row>
					@endforeach
				</flux:table.rows>
			</flux:table>

			@foreach ($tasks as $task)
				<flux:modal
					:name="'delete-task-'.$task->id"
					:aria-labelledby="'delete-task-title-'.$task->id"
					:aria-describedby="'delete-task-desc-'.$task->id"
					class="md:w-96"
				>
					<div class="space-y-6">
						<div>
							<flux:heading :id="'delete-task-title-'.$task->id" size="lg">{{ __('Delete Task') }}</flux:heading>
							<flux:subheading :id="'delete-task-desc-'.$task->id" class="mt-2">
								{{ __('Are you sure you want to delete :title? This action cannot be undone.', ['title' => $task->title]) }}
							</flux:subheading>
						</div>
						<div class="flex gap-3">
							<flux:spacer />
							<flux:modal.close>
								<flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
							</flux:modal.close>
							<form method="POST" action="{{ route('tasks.destroy', $task) }}">
								@csrf
								@method('DELETE')
								<flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
							</form>
						</div>
					</div>
				</flux:modal>
			@endforeach

			@if ($tasks->hasPages())
				<div class="flex justify-end">
					{{ $tasks->onEachSide(1)->links() }}
				</div>
			@endif
		@endif
	</main>
</x-layouts::app>
