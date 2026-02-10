<x-layouts::app :title="__('Due Payment for :name - BasicEMS', ['name' => $duePayment->employee?->full_name ?? 'Unknown'])">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('due-payments.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to due payments') }}"
				wire:navigate
			/>
			<flux:heading size="xl">{{ __('Due Payment Details') }}</flux:heading>
		</div>

		@php
			$statusLabels = [
				'pending' => __('Pending'),
				'paid' => __('Paid'),
			];

			$statusColors = [
				'pending' => 'yellow',
				'paid' => 'green',
			];

			$urgencyLabels = [
				'overdue' => __('Overdue'),
				'urgent' => __('Urgent'),
				'soon' => __('Soon'),
				'upcoming' => __('Upcoming'),
				'scheduled' => __('Scheduled'),
			];

			$statusLabel = $statusLabels[$duePayment->status] ?? ucfirst($duePayment->status);
			$statusColor = $statusColors[$duePayment->status] ?? 'zinc';
			$urgencyLabel = $urgencyLabels[$duePayment->urgency] ?? ucfirst($duePayment->urgency);
			$urgencyColor = $duePayment->urgency_color;
			$daysUntil = $duePayment->days_until_due;
			$daysLabel = $daysUntil === 0
				? __('Today')
				: ($daysUntil === 1
					? __('Tomorrow')
					: ($daysUntil < 0
						? __(':days days ago', ['days' => abs($daysUntil)])
						: __('In :days days', ['days' => $daysUntil])));
		@endphp

		<flux:card class="flex-1">
			<div class="space-y-6">
				<div class="flex items-start justify-between gap-4">
					<div class="space-y-2">
						<flux:heading size="lg">
							{{ __('Payment for :name', ['name' => $duePayment->employee?->full_name ?? __('Unknown')]) }}
						</flux:heading>
						<flux:subheading>
							${{ number_format((float) $duePayment->amount, 2) }}
						</flux:subheading>
						<div class="flex gap-2">
							<flux:badge :color="$urgencyColor">{{ $urgencyLabel }}</flux:badge>
							<flux:badge :color="$statusColor">{{ $statusLabel }}</flux:badge>
						</div>
					</div>
					<div class="flex items-center gap-2">
						<flux:button href="{{ route('due-payments.edit', $duePayment) }}" variant="ghost" icon="pencil" wire:navigate>
							{{ __('Edit') }}
						</flux:button>
						<flux:modal.trigger name="delete-payment">
							<flux:button
								variant="ghost"
								icon="trash"
								x-data=""
								x-on:click.prevent="$dispatch('open-modal', 'delete-payment')"
							>
								{{ __('Delete') }}
							</flux:button>
						</flux:modal.trigger>
					</div>
				</div>

				<flux:separator />

				<div class="grid gap-6 sm:grid-cols-2">
					<div>
						<flux:subheading>{{ __('Employee') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">
							@if ($duePayment->employee)
								<a href="{{ route('employees.show', $duePayment->employee) }}" class="hover:underline" wire:navigate>
									{{ $duePayment->employee->full_name }}
								</a>
							@else
								{{ __('Unknown') }}
							@endif
						</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Amount') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">${{ number_format((float) $duePayment->amount, 2) }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Pay Date') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">
							{{ $duePayment->pay_date->format('M d, Y') }}
							<span class="text-sm font-normal text-zinc-500">({{ $daysLabel }})</span>
						</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Status') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $statusLabel }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Urgency') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $urgencyLabel }}</flux:heading>
					</div>
					<div>
						<flux:subheading>{{ __('Created') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $duePayment->created_at->format('M d, Y \a\t g:i A') }}</flux:heading>
					</div>
					<div class="sm:col-span-2">
						<flux:subheading>{{ __('Notes') }}</flux:subheading>
						<flux:heading size="sm" class="mt-1">{{ $duePayment->notes ?? '-' }}</flux:heading>
					</div>
				</div>
			</div>
		</flux:card>

		<flux:modal
			name="delete-payment"
			aria-labelledby="delete-payment-title"
			aria-describedby="delete-payment-desc"
			class="md:w-96"
		>
			<div class="space-y-6">
				<div>
					<flux:heading id="delete-payment-title" size="lg">{{ __('Delete Due Payment') }}</flux:heading>
					<flux:subheading id="delete-payment-desc" class="mt-2">
						{{ __('Are you sure you want to delete this payment for :name? This action cannot be undone.', ['name' => $duePayment->employee?->full_name ?? __('Unknown')]) }}
					</flux:subheading>
				</div>
				<div class="flex gap-3">
					<flux:spacer />
					<flux:modal.close>
						<flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
					</flux:modal.close>
					<form method="POST" action="{{ route('due-payments.destroy', $duePayment) }}">
						@csrf
						@method('DELETE')
						<flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
					</form>
				</div>
			</div>
		</flux:modal>
	</div>
</x-layouts::app>