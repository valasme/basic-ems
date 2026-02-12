<x-layouts::app :title="__('Attendance Entry - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('attendances.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to attendance') }}"
				wire:navigate
			/>
			<flux:heading size="xl">{{ __('Attendance Entry') }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		<flux:card class="flex-1">
			<div class="space-y-6">
				<div class="flex items-start justify-between gap-4">
					<div>
						<flux:heading size="lg">{{ $attendance->employee->full_name }}</flux:heading>
						<flux:subheading class="mt-1">
							{{ $attendance->attendance_date->format('M d, Y') }}
						</flux:subheading>
					</div>
					<div class="flex items-center gap-2">
						<flux:button href="{{ route('attendances.edit', $attendance) }}" variant="ghost" icon="pencil" wire:navigate>
							{{ __('Edit') }}
						</flux:button>
						<flux:modal.trigger name="delete-attendance">
							<flux:button variant="ghost" icon="trash">
								{{ __('Delete') }}
							</flux:button>
						</flux:modal.trigger>
					</div>
				</div>

				<flux:separator />

				<div class="grid gap-6 sm:grid-cols-2">
					<div>
						<flux:subheading>{{ __('Employee') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $attendance->employee->full_name }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Scheduled Work In') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $attendance->employee->work_in ?? '-' }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Attendance Date') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $attendance->attendance_date->format('M d, Y') }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Work In') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $attendance->work_in }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Work Out') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $attendance->work_out ?? '-' }}</flux:heading>
					</div>
					<div class="sm:col-span-2">
						<flux:subheading>{{ __('Note') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $attendance->note ?? '-' }}</flux:heading>
					</div>
				</div>
			</div>
		</flux:card>

		<flux:modal
			name="delete-attendance"
			aria-labelledby="delete-attendance-title"
			aria-describedby="delete-attendance-desc"
			class="md:w-96"
		>
			<div class="space-y-6">
				<div>
					<flux:heading id="delete-attendance-title" size="lg">{{ __('Delete Attendance Entry') }}</flux:heading>
					<flux:subheading id="delete-attendance-desc" class="mt-2">
						{{ __('Are you sure you want to delete this attendance entry? This action cannot be undone.') }}
					</flux:subheading>
				</div>
				<div class="flex gap-3">
					<flux:spacer />
					<flux:modal.close>
						<flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
					</flux:modal.close>
					<form method="POST" action="{{ route('attendances.destroy', $attendance) }}">
						@csrf
						@method('DELETE')
						<flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
					</form>
				</div>
			</div>
		</flux:modal>
	</div>
</x-layouts::app>
