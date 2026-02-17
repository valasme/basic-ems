<x-layouts::app :title="__('Edit :title - BasicEMS', ['title' => $note->note_title])">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('notes.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to notes') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ __('Edit Note') }}</flux:heading>
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
			<form method="POST" action="{{ route('notes.update', $note) }}" class="flex h-full flex-col gap-6" aria-describedby="note-form-help">
				@csrf
				@method('PUT')

				<p id="note-form-help" class="sr-only">
					{{ __('Required fields must be completed before updating the note.') }}
				</p>

				<fieldset class="grid flex-1 content-start auto-rows-min gap-6">
					<legend class="sr-only">{{ __('Note information') }}</legend>
					<flux:field>
						<flux:label>{{ __('Title') }}</flux:label>
						<flux:input
							type="text"
							name="note_title"
							value="{{ old('note_title', $note->note_title) }}"
							placeholder="{{ __('Enter note title') }}"
							required
							aria-required="true"
							autofocus
						/>
						<flux:error name="note_title" />
					</flux:field>

					<flux:field>
						<flux:label>{{ __('Description') }}</flux:label>
						<flux:textarea
							name="note_description"
							rows="8"
							placeholder="{{ __('Enter note description (optional)') }}"
						>{{ old('note_description', $note->note_description) }}</flux:textarea>
						<flux:error name="note_description" />
					</flux:field>
				</fieldset>

				<div class="mt-6 flex items-center gap-3">
					<flux:spacer />
					<flux:button href="{{ route('notes.index') }}" variant="ghost" wire:navigate>
						{{ __('Cancel') }}
					</flux:button>
					<flux:button type="submit" variant="primary">
						{{ __('Update Note') }}
					</flux:button>
				</div>
			</form>
		</flux:card>
	</main>
</x-layouts::app>