<x-layouts::app :title="__('Attendance - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center justify-between">
			<flux:heading size="xl">{{ __('Attendance') }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		<form method="GET" action="{{ route('attendances.index') }}" role="search" class="flex items-center gap-2">
			<flux:input
				type="search"
				name="search"
				placeholder="{{ __('Search employees...') }}"
				value="{{ $search }}"
				icon="magnifying-glass"
				aria-label="{{ __('Search employees') }}"
				class="max-w-xs"
			/>
			<flux:button type="submit">{{ __('Search') }}</flux:button>
			@if ($search)
				<flux:button href="{{ route('attendances.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear') }}
				</flux:button>
			@endif
		</form>

		@if ($employees->isEmpty())
			<flux:card class="text-center">
				<flux:icon name="clock" class="mx-auto size-12 text-zinc-400" />
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
			<flux:table>
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
	</div>
</x-layouts::app>
