<x-layouts::app :title="__('Notes - BasicEMS')">
	<div class="flex h-full w-full flex-1 flex-col gap-6">
		<div class="flex items-center justify-between">
			<flux:heading size="xl">{{ __('Notes') }}</flux:heading>
			<flux:button href="{{ route('notes.create') }}" variant="primary" wire:navigate>
				{{ __('Add Note') }}
			</flux:button>
		</div>

		@if (session('success'))
			<div x-data="{ open: true }" x-show="open">
				<flux:callout variant="success" role="status" aria-live="polite">
					<div class="flex items-start gap-4">
						<div class="mt-0.5 flex size-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600">
							<flux:icon name="check-circle" class="size-4" />
						</div>
						<div class="min-w-0 flex-1">
							<flux:heading size="sm">{{ __('Success') }}</flux:heading>
							<flux:subheading class="mt-1">
								{{ session('success') }}
							</flux:subheading>
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

		<form method="GET" action="{{ route('notes.index') }}" role="search" class="flex items-center gap-2">
			<flux:input
				type="search"
				name="search"
				placeholder="{{ __('Search notes...') }}"
				value="{{ $search }}"
				icon="magnifying-glass"
				aria-label="{{ __('Search notes') }}"
				class="max-w-xs"
			/>
			<flux:button type="submit">{{ __('Search') }}</flux:button>
			@if ($search)
				<flux:button href="{{ route('notes.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear') }}
				</flux:button>
			@endif
		</form>

		@if ($notes->isEmpty())
			<flux:card class="text-center">
				<flux:icon name="document-text" class="mx-auto size-12 text-zinc-400" />
				<flux:heading size="lg" class="mt-4">{{ __('No notes found') }}</flux:heading>
				<flux:subheading class="mt-2">
					@if ($search)
						{{ __('No notes match your search criteria. Try adjusting your search.') }}
					@else
						{{ __('Get started by adding your first note.') }}
					@endif
				</flux:subheading>
				@unless ($search)
					<div class="mt-6">
						<flux:button href="{{ route('notes.create') }}" variant="primary" wire:navigate>
							{{ __('Add Note') }}
						</flux:button>
					</div>
				@endunless
			</flux:card>
		@else
			<flux:table>
				<flux:table.columns>
					<flux:table.column>{{ __('Title') }}</flux:table.column>
					<flux:table.column>{{ __('Description') }}</flux:table.column>
					<flux:table.column>{{ __('Created') }}</flux:table.column>
					<flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
				</flux:table.columns>

				<flux:table.rows>
					@foreach ($notes as $note)
						<flux:table.row :key="$note->id">
							<flux:table.cell variant="strong">
								<a href="{{ route('notes.show', $note) }}" class="hover:underline" wire:navigate>
									{{ $note->note_title }}
								</a>
							</flux:table.cell>
							<flux:table.cell class="max-w-xs truncate">
								{{ Str::limit($note->note_description, 50) ?? '-' }}
							</flux:table.cell>
							<flux:table.cell>{{ $note->created_at->format('M d, Y') }}</flux:table.cell>
							<flux:table.cell align="end">
								<div class="flex items-center justify-end gap-1">
									<flux:button
										href="{{ route('notes.show', $note) }}"
										variant="ghost"
										size="sm"
										icon="eye"
										aria-label="{{ __('View :title', ['title' => $note->note_title]) }}"
										title="{{ __('View') }}"
										wire:navigate
									/>
									<flux:button
										href="{{ route('notes.edit', $note) }}"
										variant="ghost"
										size="sm"
										icon="pencil"
										aria-label="{{ __('Edit :title', ['title' => $note->note_title]) }}"
										title="{{ __('Edit') }}"
										wire:navigate
									/>
									<flux:modal.trigger :name="'delete-note-' . $note->id">
										<flux:button
											variant="ghost"
											size="sm"
											icon="trash"
											aria-label="{{ __('Delete :title', ['title' => $note->note_title]) }}"
											title="{{ __('Delete') }}"
											x-data=""
											x-on:click.prevent="$dispatch('open-modal', 'delete-note-{{ $note->id }}')"
										/>
									</flux:modal.trigger>
								</div>
							</flux:table.cell>
						</flux:table.row>
					@endforeach
				</flux:table.rows>
			</flux:table>

			@foreach ($notes as $note)
				<flux:modal
					:name="'delete-note-' . $note->id"
					aria-labelledby="delete-note-{{ $note->id }}-title"
					aria-describedby="delete-note-{{ $note->id }}-desc"
					class="md:w-96"
				>
					<div class="space-y-6">
						<div>
							<flux:heading id="delete-note-{{ $note->id }}-title" size="lg">{{ __('Delete Note') }}</flux:heading>
							<flux:subheading id="delete-note-{{ $note->id }}-desc" class="mt-2">
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
			@endforeach

			@if ($notes->hasPages())
				<div class="flex justify-end">
					{{ $notes->onEachSide(1)->links() }}
				</div>
			@endif
		@endif
	</div>
</x-layouts::app>