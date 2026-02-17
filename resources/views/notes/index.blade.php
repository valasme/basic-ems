<x-layouts::app :title="__('Notes - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center justify-between">
			<flux:heading id="page-title" size="xl">{{ __('Notes') }}</flux:heading>
			<flux:button href="{{ route('notes.create') }}" variant="primary" wire:navigate>
				{{ __('Add Note') }}
			</flux:button>
		</div>

		@if (session('success'))
			<div x-data="{ open: true }" x-show="open">
				<flux:callout variant="success" role="status" aria-live="polite">
					<div class="flex items-start gap-4">
						<div class="mt-0.5 flex size-8 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-600" aria-hidden="true">
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

		<form method="GET" action="{{ route('notes.index') }}" role="search" aria-describedby="notes-search-help" class="flex flex-col gap-2 sm:flex-row sm:items-center">
			<p id="notes-search-help" class="sr-only">
				{{ __('Search notes by text and sort the list using the filter dropdown.') }}
			</p>
			<div class="flex flex-col gap-2 sm:flex-row sm:items-center">
				<label for="notes-search" class="sr-only">{{ __('Search notes') }}</label>
				<flux:input
					id="notes-search"
					type="search"
					name="search"
					placeholder="{{ __('Search notes...') }}"
					value="{{ $search }}"
					icon="magnifying-glass"
					class="w-full max-w-xs"
				/>
				<flux:button type="submit">{{ __('Search') }}</flux:button>
			</div>
			<label for="notes-filter" class="sr-only">{{ __('Sort notes') }}</label>
			<flux:select id="notes-filter" name="filter" aria-label="{{ __('Sort notes') }}" class="min-w-56">
				<option value="created_newest" @selected($filter === 'created_newest')>{{ __('Created Date (Newest)') }}</option>
				<option value="created_oldest" @selected($filter === 'created_oldest')>{{ __('Created Date (Oldest)') }}</option>
				<option value="updated_newest" @selected($filter === 'updated_newest')>{{ __('Updated Date (Newest)') }}</option>
				<option value="updated_oldest" @selected($filter === 'updated_oldest')>{{ __('Updated Date (Oldest)') }}</option>
				<option value="title_alpha" @selected($filter === 'title_alpha')>{{ __('Title (A-Z)') }}</option>
				<option value="title_reverse" @selected($filter === 'title_reverse')>{{ __('Title (Z-A)') }}</option>
				<option value="description_alpha" @selected($filter === 'description_alpha')>{{ __('Description (A-Z)') }}</option>
				<option value="description_reverse" @selected($filter === 'description_reverse')>{{ __('Description (Z-A)') }}</option>
			</flux:select>
			@if ($search || $filter !== 'created_newest')
				<flux:button href="{{ route('notes.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear Filters') }}
				</flux:button>
			@endif
		</form>

		<p class="sr-only" aria-live="polite">
			{{ trans_choice('{0} No notes found|{1} :count note found|[2,*] :count notes found', $notes->total(), ['count' => $notes->total()]) }}
		</p>

		@if ($notes->isEmpty())
			<flux:card class="text-center" role="status" aria-live="polite">
				<flux:icon name="document-text" class="mx-auto size-12 text-zinc-400" aria-hidden="true" />
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
			<flux:table aria-label="{{ __('Notes list') }}" aria-describedby="notes-table-caption">
				<caption id="notes-table-caption" class="sr-only">
					{{ __('Notes list showing title, description, created date, and actions.') }}
				</caption>
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
								{{ $note->note_description ? Str::limit($note->note_description, 50) : '-' }}
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
								<flux:modal.trigger :name="'delete-note-'.$note->id">
									<flux:button
										variant="ghost"
										size="sm"
										icon="trash"
										aria-label="{{ __('Delete :title', ['title' => $note->note_title]) }}"
										title="{{ __('Delete') }}"
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
					:name="'delete-note-'.$note->id"
					:aria-labelledby="'delete-note-title-'.$note->id"
					:aria-describedby="'delete-note-desc-'.$note->id"
					class="md:w-96"
				>
					<div class="space-y-6">
						<div>
							<flux:heading :id="'delete-note-title-'.$note->id" size="lg">{{ __('Delete Note') }}</flux:heading>
							<flux:subheading :id="'delete-note-desc-'.$note->id" class="mt-2">
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
	</main>
</x-layouts::app>