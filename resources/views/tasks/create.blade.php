<x-layouts::app :title="__('Add Task - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('tasks.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to tasks') }}"
				wire:navigate
			/>
			<flux:heading size="xl">{{ __('Add Task') }}</flux:heading>
		</div>

		<flux:card class="flex-1">
			@php
				$statuses = [
					'pending' => __('Pending'),
					'in_progress' => __('In Progress'),
					'completed' => __('Completed'),
				];
			@endphp

			<form method="POST" action="{{ route('tasks.store') }}" class="flex h-full flex-col gap-6">
				@csrf

				<div class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Title') }}</flux:label>
						<flux:input
							type="text"
							name="title"
							value="{{ old('title') }}"
							placeholder="{{ __('Enter task title') }}"
							required
							autofocus
						/>
						<flux:error name="title" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Employee') }}</flux:label>
						<flux:select name="employee_id" required>
							<option value="" @selected(old('employee_id') === null)>{{ __('Select an employee') }}</option>
							@foreach ($employees as $employee)
								<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
									{{ $employee->full_name }}
								</option>
							@endforeach
						</flux:select>
						<flux:error name="employee_id" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Status') }}</flux:label>
						<flux:select name="status" required>
							@foreach ($statuses as $value => $label)
								<option value="{{ $value }}" @selected(old('status', 'pending') === $value)>
									{{ $label }}
								</option>
							@endforeach
						</flux:select>
						<flux:error name="status" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Due Date') }}</flux:label>
						<flux:input
							type="date"
							name="due_date"
							value="{{ old('due_date') }}"
						/>
						<flux:error name="due_date" />
					</flux:field>

					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Description') }}</flux:label>
						<flux:textarea
							name="description"
							rows="4"
							placeholder="{{ __('Add a short description (optional)') }}"
						>{{ old('description') }}</flux:textarea>
						<flux:error name="description" />
					</flux:field>
				</div>

				<div class="mt-6 flex items-center gap-3">
					<flux:spacer />
					<flux:button href="{{ route('tasks.index') }}" variant="ghost" wire:navigate>
						{{ __('Cancel') }}
					</flux:button>
					<flux:button type="submit" variant="primary">
						{{ __('Create Task') }}
					</flux:button>
				</div>
			</form>
		</flux:card>
	</div>
</x-layouts::app>
