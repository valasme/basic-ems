<x-layouts::app :title="__('Edit Attendance - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('attendances.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to attendance') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ __('Edit Attendance') }}</flux:heading>
		</div>

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
			<form method="POST" action="{{ route('attendances.update', $attendance) }}" class="flex h-full flex-col gap-6" aria-describedby="attendance-form-help">
				@csrf
				@method('PUT')

				<p id="attendance-form-help" class="sr-only">
					{{ __('All required fields must be completed before updating attendance.') }}
				</p>

				<fieldset class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
					<legend class="sr-only">{{ __('Attendance information') }}</legend>
					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Employee') }}</flux:label>
						<flux:select name="employee_id" required aria-required="true" autofocus>
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
							aria-required="true"
						/>
						<flux:error name="attendance_date" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Work In') }}</flux:label>
						<flux:input type="time" name="work_in" value="{{ old('work_in', $attendance->work_in) }}" required aria-required="true" />
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
				</fieldset>

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
	</main>
</x-layouts::app>
