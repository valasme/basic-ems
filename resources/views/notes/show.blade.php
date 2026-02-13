<x-layouts::app :title="__(':title - BasicEMS', ['title' => $note->note_title])">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center gap-4">
			<flux:button
				href="{{ route('notes.index') }}"
				variant="ghost"
				icon="arrow-left"
				aria-label="{{ __('Back to notes') }}"
				wire:navigate
			/>
			<flux:heading id="page-title" size="xl">{{ $note->note_title }}</flux:heading>
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
					<div class="space-y-2">
						<flux:heading size="lg">{{ $note->note_title }}</flux:heading>
						<flux:subheading>{{ __('Created :date', ['date' => $note->created_at->format('M d, Y \a\t g:i A')]) }}</flux:subheading>
					</div>
					<div class="flex items-center gap-2">
						<flux:button href="{{ route('notes.edit', $note) }}" variant="ghost" icon="pencil" aria-label="{{ __('Edit :title', ['title' => $note->note_title]) }}" wire:navigate>
							{{ __('Edit') }}
						</flux:button>
						<flux:modal.trigger name="delete-note">
						<flux:button variant="ghost" icon="trash" aria-label="{{ __('Delete :title', ['title' => $note->note_title]) }}">
								{{ __('Delete') }}
							</flux:button>
						</flux:modal.trigger>
					</div>
				</div>

				<flux:separator />

				<dl>
					<div>
						<dt><flux:subheading>{{ __('Description') }}</flux:subheading></dt>
						<dd><flux:heading size="sm" class="mt-1 whitespace-pre-wrap">{{ $note->note_description ?? '-' }}</flux:heading></dd>
					</div>
				</dl>
			</div>
		</flux:card>

		<flux:modal
			name="delete-note"
			aria-labelledby="delete-note-title"
			aria-describedby="delete-note-desc"
			class="md:w-96"
		>
			<div class="space-y-6">
				<div>
					<flux:heading id="delete-note-title" size="lg">{{ __('Delete Note') }}</flux:heading>
					<flux:subheading id="delete-note-desc" class="mt-2">
						{{ __('Are you sure you want to delete :title? This action cannot be undone.', ['title' => $note->note_title]) }}
					</flux:subheading>
				</div>
				<div class="flex gap-3">
					<flux:spacer />
					<flux:modal.close>
						<flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
					</flux:modal.close>
					<form method="POST" action="{{ route('notes.destroy', $note) }}">
						@csrf
						@method('DELETE')
						<flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
					</form>
				</div>
			</div>
		</flux:modal>
	</main>
</x-layouts::app>