<x-layouts::app :title="__('Add Department - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('departments.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to departments') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ __('Add Department') }}</flux:heading>
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
			<form method="POST" action="{{ route('departments.store') }}" class="flex h-full flex-col gap-6" aria-describedby="department-form-help">
				@csrf

				<p id="department-form-help" class="sr-only">
					{{ __('Department name is required before creating the department.') }}
				</p>

				<fieldset class="grid flex-1 content-start auto-rows-min gap-6">
					<legend class="sr-only">{{ __('Department information') }}</legend>

					<flux:field>
						<flux:label>{{ __('Department Name') }}</flux:label>
						<flux:input
							type="text"
							name="name"
							value="{{ old('name') }}"
							placeholder="{{ __('Enter department name') }}"
							autocomplete="organization"
							required
							aria-required="true"
							autofocus
						/>
						<flux:error name="name" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Description') }}</flux:label>
						<flux:textarea
							name="description"
							rows="5"
							placeholder="{{ __('Add an optional description') }}"
						>{{ old('description') }}</flux:textarea>
						<flux:error name="description" />
					</flux:field>
				</fieldset>

				<div class="mt-6 flex items-center gap-3">
					<flux:spacer />
					<flux:button href="{{ route('departments.index') }}" variant="ghost" wire:navigate>
						{{ __('Cancel') }}
					</flux:button>
					<flux:button type="submit" variant="primary">
						{{ __('Create Department') }}
					</flux:button>
				</div>
			</form>
		</flux:card>
	</main>
</x-layouts::app>
