<x-layouts::app :title="__('Add Due Payment - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('due-payments.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to due payments') }}"
				wire:navigate
			/>
			<flux:heading size="xl">{{ __('Add Due Payment') }}</flux:heading>
		</div>

		<flux:card class="flex-1">
			@php
				$statuses = [
					'pending' => __('Pending'),
					'paid' => __('Paid'),
				];
			@endphp

			<form
				method="POST"
				action="{{ route('due-payments.store') }}"
				class="flex h-full flex-col gap-6"
				x-data="{
					selectedEmployee: '{{ old('employee_id') }}',
					employees: @js($employees->keyBy('id')->map(fn($e) => ['pay_amount' => $e->pay_amount, 'pay_day' => $e->pay_day])),
					get suggestedAmount() {
						return this.employees[this.selectedEmployee]?.pay_amount || '';
					}
				}"
			>
				@csrf

				<div class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Employee') }}</flux:label>
						<flux:select name="employee_id" x-model="selectedEmployee" required>
							<option value="">{{ __('Select an employee') }}</option>
							@foreach ($employees as $employee)
								<option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
									{{ $employee->full_name }}
									@if ($employee->pay_day)
										(Pay day: {{ $employee->pay_day }})
									@endif
								</option>
							@endforeach
						</flux:select>
						<flux:error name="employee_id" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Amount') }}</flux:label>
						<flux:input
							type="number"
							name="amount"
							:value="old('amount')"
							x-bind:placeholder="suggestedAmount ? 'Suggested: $' + suggestedAmount : 'Enter amount'"
							step="0.01"
							min="0.01"
							required
						/>
						<flux:error name="amount" />
						<flux:description x-show="suggestedAmount">
							{{ __('Employee\'s regular pay: ') }}<span x-text="'$' + suggestedAmount"></span>
						</flux:description>
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Pay Date') }}</flux:label>
						<flux:input
							type="date"
							name="pay_date"
							value="{{ old('pay_date') }}"
							required
						/>
						<flux:error name="pay_date" />
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

					<flux:field class="sm:col-span-2">
						<flux:label>{{ __('Notes') }}</flux:label>
						<flux:textarea
							name="notes"
							rows="3"
							placeholder="{{ __('Add any notes about this payment (optional)') }}"
						>{{ old('notes') }}</flux:textarea>
						<flux:error name="notes" />
					</flux:field>
				</div>

				<div class="mt-6 flex items-center gap-3">
					<flux:spacer />
					<flux:button href="{{ route('due-payments.index') }}" variant="ghost" wire:navigate>
						{{ __('Cancel') }}
					</flux:button>
					<flux:button type="submit" variant="primary">
						{{ __('Create Due Payment') }}
					</flux:button>
				</div>
			</form>
		</flux:card>
	</div>
</x-layouts::app>