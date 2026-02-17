<x-layouts::app :title="__('Due Payments - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center justify-between">
			<flux:heading id="page-title" size="xl">{{ __('Due Payments') }}</flux:heading>
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
			<div x-data="{ open: true }" x-show="open">
				<flux:callout variant="danger" role="alert" aria-live="assertive">
					<div class="flex items-start gap-4">
						<div class="min-w-0 flex-1">
							<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
							<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
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

		<form method="GET" action="{{ route('due-payments.index') }}" role="search" aria-describedby="due-payments-search-help" class="flex items-center gap-2">
			<p id="due-payments-search-help" class="sr-only">
				{{ __('Search employees to find upcoming due payments.') }}
			</p>
			<label for="due-payments-search" class="sr-only">{{ __('Search employees') }}</label>
			<flux:input
				id="due-payments-search"
				type="search"
				name="search"
				placeholder="{{ __('Search employees...') }}"
				value="{{ $search }}"
				icon="magnifying-glass"
				class="max-w-xs"
			/>
			<flux:button type="submit">{{ __('Search') }}</flux:button>
			@if ($search)
				<flux:button href="{{ route('due-payments.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear Search') }}
				</flux:button>
			@endif
		</form>

		<p class="sr-only" aria-live="polite">
			{{ trans_choice('{0} No employees found|{1} :count employee found|[2,*] :count employees found', $employees->count(), ['count' => $employees->count()]) }}
		</p>

		@if ($employees->isEmpty())
			<flux:card class="text-center" role="status" aria-live="polite">
				<flux:icon name="banknotes" class="mx-auto size-12 text-zinc-400" aria-hidden="true" />
				<flux:heading size="lg" class="mt-4">{{ __('No employees with pay dates found') }}</flux:heading>
				<flux:subheading class="mt-2">
					@if ($search)
						{{ __('No employees match your search criteria. Try adjusting your search.') }}
					@else
						{{ __('Add pay dates to your employees to see upcoming payments.') }}
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
			@php
				$urgencyLabels = [
					'urgent' => __('Urgent'),
					'soon' => __('Soon'),
					'upcoming' => __('Upcoming'),
					'scheduled' => __('Scheduled'),
				];
			@endphp

			<flux:table aria-label="{{ __('Due payments list') }}" aria-describedby="due-payments-table-caption">
				<caption id="due-payments-table-caption" class="sr-only">
					{{ __('Due payments list showing employee, job title, pay amount, next pay date, urgency, and actions.') }}
				</caption>
				<flux:table.columns>
					<flux:table.column>{{ __('Employee') }}</flux:table.column>
					<flux:table.column>{{ __('Job Title') }}</flux:table.column>
					<flux:table.column>{{ __('Pay Amount') }}</flux:table.column>
					<flux:table.column>{{ __('Next Pay Date') }}</flux:table.column>
					<flux:table.column>{{ __('Urgency') }}</flux:table.column>
					<flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
				</flux:table.columns>

				<flux:table.rows>
					@foreach ($employees as $employee)
						@php
							$urgencyLabel = $urgencyLabels[$employee->pay_urgency] ?? ucfirst($employee->pay_urgency ?? 'scheduled');
							$urgencyColor = $employee->pay_urgency_color ?? 'zinc';
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
								{{ $employee->job_title ?? '-' }}
							</flux:table.cell>
							<flux:table.cell>
								{{ $employee->pay_amount !== null ? '$' . number_format((float) $employee->pay_amount, 2) : '-' }}
							</flux:table.cell>
							<flux:table.cell>
								<div class="flex flex-col">
									<span>{{ $employee->next_pay_date?->format('M d, Y') ?? '-' }}</span>
									@if ($employee->next_pay_date)
										<span class="text-xs text-zinc-500">{{ $daysLabel }}</span>
									@endif
								</div>
							</flux:table.cell>
							<flux:table.cell>
								<flux:badge :color="$urgencyColor">{{ $urgencyLabel }}</flux:badge>
							</flux:table.cell>
							<flux:table.cell align="end">
								<div class="flex items-center justify-end gap-1">
									<flux:button
										href="{{ route('employees.show', $employee) }}"
										variant="ghost"
										size="sm"
										icon="eye"
										aria-label="{{ __('View :name', ['name' => $employee->full_name]) }}"
										title="{{ __('View') }}"
										wire:navigate
									/>
									<flux:button
										href="{{ route('employees.edit', $employee) }}"
										variant="ghost"
										size="sm"
										icon="pencil"
										aria-label="{{ __('Edit :name', ['name' => $employee->full_name]) }}"
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
	</main>
</x-layouts::app>