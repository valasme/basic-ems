<x-layouts::app :title="__('Add Task - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('tasks.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to tasks') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ __('Add Task') }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		@if ($errors->any())
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Please fix the following errors') }}</flux:heading>
				<ul class="mt-2 list-disc ps-5 text-sm">
					@foreach ($errors->all() as $error)
						<li>{{ $error }}</li>
					@endforeach
				</ul>
			</flux:callout>
		@endif

		<flux:card class="flex-1">
			@php
				$statuses = [
					'pending' => __('Pending'),
					'in_progress' => __('In Progress'),
					'completed' => __('Completed'),
				];

				$priorities = [
					'urgent' => __('Urgent'),
					'high' => __('High'),
					'medium' => __('Medium'),
					'low' => __('Low'),
				];
			@endphp

			<form
				method="POST"
				action="{{ route('tasks.store') }}"
				class="flex h-full flex-col gap-6"
				aria-describedby="task-form-help"
				x-data="{ status: '{{ old('status', 'pending') }}' }"
			>
				@csrf

				<p id="task-form-help" class="sr-only">
					{{ __('All required fields must be completed before creating the task.') }}
				</p>

				<fieldset class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
					<legend class="sr-only">{{ __('Task information') }}</legend>
					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Title') }}</flux:label>
						<flux:input
							type="text"
							name="title"
							value="{{ old('title') }}"
							placeholder="{{ __('Enter task title') }}"
							required
							aria-required="true"
							autofocus
						/>
						<flux:error name="title" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Employee') }}</flux:label>
						<flux:select name="employee_id">
							<option value="" @selected(old('employee_id') === null)>{{ __('No employee') }}</option>
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
						<flux:select name="status" x-model="status" required aria-required="true">
							@foreach ($statuses as $value => $label)
								<option value="{{ $value }}" @selected(old('status', 'pending') === $value)>
									{{ $label }}
								</option>
							@endforeach
						</flux:select>
						<flux:error name="status" />
					</flux:field>

					<flux:field x-show="status !== 'completed'">
						<flux:label>{{ __('Priority') }}</flux:label>
						<flux:select name="priority" required aria-required="true">
							@foreach ($priorities as $value => $label)
								<option value="{{ $value }}" @selected(old('priority', 'medium') === $value)>
									{{ $label }}
								</option>
							@endforeach
						</flux:select>
						<flux:error name="priority" />
					</flux:field>
					<template x-if="status === 'completed'">
						<input type="hidden" name="priority" value="none">
					</template>

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
				</fieldset>

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
	</main>
</x-layouts::app>
