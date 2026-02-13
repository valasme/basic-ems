<x-layouts::app :title="__('Attendance - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center justify-between">
			<flux:heading id="page-title" size="xl">{{ __('Attendance') }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		<form method="GET" action="{{ route('attendances.index') }}" role="search" aria-describedby="attendance-search-help" class="flex items-center gap-2">
			<p id="attendance-search-help" class="sr-only">
				{{ __('Search employees to quickly find attendance schedules.') }}
			</p>
			<label for="attendance-search" class="sr-only">{{ __('Search employees') }}</label>
			<flux:input
				id="attendance-search"
				type="search"
				name="search"
				placeholder="{{ __('Search employees...') }}"
				value="{{ $search }}"
				icon="magnifying-glass"
				class="max-w-xs"
			/>
			<flux:button type="submit">{{ __('Search') }}</flux:button>
			@if ($search)
				<flux:button href="{{ route('attendances.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear Search') }}
				</flux:button>
			@endif
		</form>

		<p class="sr-only" aria-live="polite">
			{{ trans_choice('{0} No employees found|{1} :count employee found|[2,*] :count employees found', $employees->count(), ['count' => $employees->count()]) }}
		</p>

		@if ($employees->isEmpty())
			<flux:card class="text-center" role="status" aria-live="polite">
				<flux:icon name="clock" class="mx-auto size-12 text-zinc-400" aria-hidden="true" />
				<flux:heading size="lg" class="mt-4">{{ __('No employee schedules found') }}</flux:heading>
				<flux:subheading class="mt-2">
					@if ($search)
						{{ __('No employees match your search criteria. Try adjusting your search.') }}
					@else
						{{ __('Add employees and set their work in/out times to see schedules here.') }}
					@endif
				</flux:subheading>
				@unless ($search)
					<div class="mt-6">
						<flux:button href="{{ route('employees.index') }}" variant="primary" wire:navigate>
							{{ __('View Employees') }}
						</flux:button>
					</div>
				@endunless
			</flux:card>
		@else
			<flux:table aria-label="{{ __('Attendance schedule list') }}" aria-describedby="attendance-table-caption">
				<caption id="attendance-table-caption" class="sr-only">
					{{ __('Attendance schedules showing priority, employee, work in, work out, department, and job title.') }}
				</caption>
				<flux:table.columns>
					<flux:table.column>{{ __('Priority') }}</flux:table.column>
					<flux:table.column>{{ __('Employee') }}</flux:table.column>
					<flux:table.column>{{ __('Work In') }}</flux:table.column>
					<flux:table.column>{{ __('Work Out') }}</flux:table.column>
					<flux:table.column>{{ __('Department') }}</flux:table.column>
					<flux:table.column>{{ __('Job Title') }}</flux:table.column>
				</flux:table.columns>

				<flux:table.rows>
					@foreach ($employees as $employee)
						<flux:table.row :key="$employee->id">
							<flux:table.cell variant="strong">
								{{ $loop->iteration }}
							</flux:table.cell>
							<flux:table.cell>
								<a href="{{ route('employees.show', $employee) }}" class="hover:underline" wire:navigate>
									{{ $employee->full_name }}
								</a>
							</flux:table.cell>
							<flux:table.cell>{{ $employee->work_in ?? '-' }}</flux:table.cell>
							<flux:table.cell>{{ $employee->work_out ?? '-' }}</flux:table.cell>
							<flux:table.cell>{{ $employee->department ?? '-' }}</flux:table.cell>
							<flux:table.cell>{{ $employee->job_title ?? '-' }}</flux:table.cell>
						</flux:table.row>
					@endforeach
				</flux:table.rows>
			</flux:table>
		@endif
	</main>
</x-layouts::app>
