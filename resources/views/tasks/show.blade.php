<x-layouts::app :title="__(':title - BasicEMS', ['title' => $task->title])">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('tasks.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to tasks') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ $task->title }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

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

			$statusLabel = $statusLabels[$task->status] ?? ucfirst(str_replace('_', ' ', $task->status));
			$statusColor = $statusColors[$task->status] ?? 'zinc';
			$priorityLabel = $priorityLabels[$task->priority] ?? ucfirst($task->priority);
			$priorityColor = $priorityColors[$task->priority] ?? 'zinc';
		@endphp

		<flux:card class="flex-1">
			<div class="space-y-6">
				<div class="flex items-start justify-between gap-4">
					<div class="space-y-2">
						<flux:heading size="lg">{{ $task->title }}</flux:heading>
						<flux:subheading>
							@if ($task->employee)
								<a href="{{ route('employees.show', $task->employee) }}" class="hover:underline" wire:navigate>
									{{ $task->employee->full_name }}
								</a>
							@else
								{{ __('Unassigned') }}
							@endif
						</flux:subheading>
						<flux:badge :color="$statusColor">{{ $statusLabel }}</flux:badge>
						<flux:badge :color="$priorityColor">{{ $priorityLabel }}</flux:badge>
					</div>
					<div class="flex items-center gap-2">
						<flux:button href="{{ route('tasks.edit', $task) }}" variant="ghost" icon="pencil" aria-label="{{ __('Edit :title', ['title' => $task->title]) }}" wire:navigate>
							{{ __('Edit') }}
						</flux:button>
						<flux:modal.trigger name="delete-task">
							<flux:button variant="ghost" icon="trash" aria-label="{{ __('Delete :title', ['title' => $task->title]) }}">
								{{ __('Delete') }}
							</flux:button>
						</flux:modal.trigger>
					</div>
				</div>

				<flux:separator />

				<dl class="grid gap-6 sm:grid-cols-2">
					<div>
						<dt><flux:subheading>{{ __('Employee') }}</flux:subheading></dt>
						<dd>
							<flux:heading size="sm" class="mt-1">
								{{ $task->employee?->full_name ?? __('Unassigned') }}
							</flux:heading>
						</dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Status') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $statusLabel }}</flux:heading></dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Priority') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $priorityLabel }}</flux:heading></dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Due Date') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $task->due_date?->format('M d, Y') ?? '-' }}</flux:heading></dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Created') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $task->created_at->format('M d, Y \a\t g:i A') }}</flux:heading></dd>
					</div>
					<div class="sm:col-span-2">
						<dt><flux:subheading>{{ __('Description') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $task->description ?? '-' }}</flux:heading></dd>
					</div>
				</dl>
			</div>
		</flux:card>

		<flux:modal
			name="delete-task"
			aria-labelledby="delete-task-title"
			aria-describedby="delete-task-desc"
			class="md:w-96"
		>
			<div class="space-y-6">
				<div>
					<flux:heading id="delete-task-title" size="lg">{{ __('Delete Task') }}</flux:heading>
					<flux:subheading id="delete-task-desc" class="mt-2">
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
	</main>
</x-layouts::app>
