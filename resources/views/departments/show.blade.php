<x-layouts::app :title="__(':name - BasicEMS', ['name' => $department->name])">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('departments.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to departments') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ $department->name }}</flux:heading>
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

		<flux:card class="flex-1">
			<div class="space-y-6">
				<div class="flex items-start justify-between">
					<div>
						<flux:heading size="lg">{{ $department->name }}</flux:heading>
						<flux:subheading>{{ __('Department details and usage') }}</flux:subheading>
					</div>
					<div class="flex items-center gap-2">
						<flux:button href="{{ route('departments.edit', $department) }}" variant="ghost" icon="pencil" aria-label="{{ __('Edit :name', ['name' => $department->name]) }}" wire:navigate>
							{{ __('Edit') }}
						</flux:button>
						<flux:modal.trigger name="delete-department">
							<flux:button variant="ghost" icon="trash" aria-label="{{ __('Delete :name', ['name' => $department->name]) }}">
								{{ __('Delete') }}
							</flux:button>
						</flux:modal.trigger>
					</div>
				</div>

				<flux:separator />

				<dl class="grid gap-6 sm:grid-cols-2">
					<div>
						<dt><flux:subheading>{{ __('Department Name') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $department->name }}</flux:heading></dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Employees Assigned') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $department->employees_count }}</flux:heading></dd>
					</div>
					<div class="sm:col-span-2">
						<dt><flux:subheading>{{ __('Description') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $department->description ?? '-' }}</flux:heading></dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Created') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $department->created_at->format('M d, Y \a\t g:i A') }}</flux:heading></dd>
					</div>
					<div>
						<dt><flux:subheading>{{ __('Updated') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1">{{ $department->updated_at->format('M d, Y \a\t g:i A') }}</flux:heading></dd>
					</div>
				</dl>
			</div>
		</flux:card>

		<flux:modal
			name="delete-department"
			aria-labelledby="delete-department-title"
			aria-describedby="delete-department-desc"
			class="md:w-96"
		>
			<div class="space-y-6">
				<div>
					<flux:heading id="delete-department-title" size="lg">{{ __('Delete Department') }}</flux:heading>
					<flux:subheading id="delete-department-desc" class="mt-2">
						{{ __('Are you sure you want to delete :name? Employees in this department will remain and become unassigned.', ['name' => $department->name]) }}
					</flux:subheading>
				</div>
				<div class="flex gap-3">
					<flux:spacer />
					<flux:modal.close>
						<flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
					</flux:modal.close>
					<form method="POST" action="{{ route('departments.destroy', $department) }}">
						@csrf
						@method('DELETE')
						<flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
					</form>
				</div>
			</div>
		</flux:modal>
	</main>
</x-layouts::app>
