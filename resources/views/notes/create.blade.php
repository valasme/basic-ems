<x-layouts::app :title="__('Add Note - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('notes.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to notes') }}"
				wire:navigate
			/>
			<flux:heading size="xl">{{ __('Add Note') }}</flux:heading>
		</div>

		<flux:card class="flex-1">
			<form method="POST" action="{{ route('notes.store') }}" class="flex h-full flex-col gap-6">
				@csrf

				<div class="grid flex-1 content-start auto-rows-min gap-6">
					<flux:field>
						<flux:label>{{ __('Title') }}</flux:label>
						<flux:input
							type="text"
							name="note_title"
							value="{{ old('note_title') }}"
							placeholder="{{ __('Enter note title') }}"
							required
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
						>{{ old('note_description') }}</flux:textarea>
						<flux:error name="note_description" />
					</flux:field>
				</div>

				<div class="mt-6 flex items-center gap-3">
					<flux:spacer />
					<flux:button href="{{ route('notes.index') }}" variant="ghost" wire:navigate>
						{{ __('Cancel') }}
					</flux:button>
					<flux:button type="submit" variant="primary">
						{{ __('Create Note') }}
					</flux:button>
				</div>
			</form>
		</flux:card>
	</div>
</x-layouts::app>