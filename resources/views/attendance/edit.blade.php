<x-layouts::app :title="__('Edit Attendance - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('attendances.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to attendance') }}"
				wire:navigate
			/>
			<flux:heading size="xl">{{ __('Edit Attendance') }}</flux:heading>
		</div>

		@if (session('error'))
			<flux:callout variant="danger" role="alert" aria-live="assertive">
				<flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
				<flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
			</flux:callout>
		@endif

		<flux:card class="flex-1">
			<form method="POST" action="{{ route('attendances.update', $attendance) }}" class="flex h-full flex-col gap-6">
				@csrf
				@method('PUT')

				<div class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Employee') }}</flux:label>
						<flux:select name="employee_id" required autofocus>
							@foreach ($employees as $employee)
								<option value="{{ $employee->id }}" @selected(old('employee_id', $attendance->employee_id) == $employee->id)>
									{{ $employee->full_name }}{{ $employee->work_in ? ' ('.$employee->work_in.')' : '' }}
								</option>
							@endforeach
						</flux:select>
						<flux:error name="employee_id" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Attendance Date') }}</flux:label>
						<flux:input
							type="date"
							name="attendance_date"
							value="{{ old('attendance_date', $attendance->attendance_date->format('Y-m-d')) }}"
							required
						/>
						<flux:error name="attendance_date" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Work In') }}</flux:label>
						<flux:input type="time" name="work_in" value="{{ old('work_in', $attendance->work_in) }}" required />
						<flux:error name="work_in" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Work Out') }}</flux:label>
						<flux:input type="time" name="work_out" value="{{ old('work_out', $attendance->work_out) }}" />
						<flux:error name="work_out" />
					</flux:field>

					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Note') }}</flux:label>
						<flux:textarea name="note" rows="4" placeholder="{{ __('Optional note') }}">{{ old('note', $attendance->note) }}</flux:textarea>
						<flux:error name="note" />
					</flux:field>
				</div>

				<div class="mt-6 flex items-center gap-3">
					<flux:spacer />
					<flux:button href="{{ route('attendances.index') }}" variant="ghost" wire:navigate>
						{{ __('Cancel') }}
					</flux:button>
					<flux:button type="submit" variant="primary">
						{{ __('Update Attendance') }}
					</flux:button>
				</div>
			</form>
		</flux:card>
	</div>
</x-layouts::app>
