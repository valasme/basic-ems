<x-layouts::app :title="__('Departments - BasicEMS')">
	<main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
		<div class="flex items-center justify-between">
			<flux:heading id="page-title" size="xl">{{ __('Departments') }}</flux:heading>
			<flux:button href="{{ route('departments.create') }}" variant="primary" wire:navigate>
				{{ __('Add Department') }}
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
							<flux:subheading class="mt-1">
								{{ session('error') }}
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

		<form method="GET" action="{{ route('departments.index') }}" role="search" aria-describedby="departments-search-help" class="flex flex-col gap-2 sm:flex-row sm:items-center">
			<p id="departments-search-help" class="sr-only">
				{{ __('Search departments by text and sort the list using the filter dropdown.') }}
			</p>
			<div class="flex flex-col gap-2 sm:flex-row sm:items-center">
				<label for="departments-search" class="sr-only">{{ __('Search departments') }}</label>
				<flux:input
					id="departments-search"
					type="search"
					name="search"
					placeholder="{{ __('Search departments...') }}"
					value="{{ $search }}"
					icon="magnifying-glass"
					class="w-full max-w-xs"
				/>
				<flux:button type="submit">{{ __('Search') }}</flux:button>
			</div>
			<label for="departments-filter" class="sr-only">{{ __('Sort departments') }}</label>
			<flux:select id="departments-filter" name="filter" aria-label="{{ __('Sort departments') }}" class="min-w-56">
				<option value="name_alpha" @selected($filter === 'name_alpha')>{{ __('Name (A-Z)') }}</option>
				<option value="name_reverse" @selected($filter === 'name_reverse')>{{ __('Name (Z-A)') }}</option>
				<option value="description_alpha" @selected($filter === 'description_alpha')>{{ __('Description (A-Z)') }}</option>
				<option value="description_reverse" @selected($filter === 'description_reverse')>{{ __('Description (Z-A)') }}</option>
				<option value="created_newest" @selected($filter === 'created_newest')>{{ __('Created Date (Newest)') }}</option>
				<option value="created_oldest" @selected($filter === 'created_oldest')>{{ __('Created Date (Oldest)') }}</option>
			</flux:select>
			@if ($search || $filter !== 'name_alpha')
				<flux:button href="{{ route('departments.index') }}" variant="ghost" wire:navigate>
					{{ __('Clear Filters') }}
				</flux:button>
			@endif
		</form>

		<p class="sr-only" aria-live="polite">
			{{ trans_choice('{0} No departments found|{1} :count department found|[2,*] :count departments found', $departments->total(), ['count' => $departments->total()]) }}
		</p>

		@if ($departments->isEmpty())
			<flux:card class="text-center" role="status" aria-live="polite">
				<flux:icon name="building-office-2" class="mx-auto size-12 text-zinc-400" aria-hidden="true" />
				<flux:heading size="lg" class="mt-4">{{ __('No departments found') }}</flux:heading>
				<flux:subheading class="mt-2">
					@if ($search)
						{{ __('No departments match your search criteria. Try adjusting your search.') }}
					@else
						{{ __('Get started by adding your first department.') }}
					@endif
				</flux:subheading>
				@unless ($search)
					<div class="mt-6">
						<flux:button href="{{ route('departments.create') }}" variant="primary" wire:navigate>
							{{ __('Add Department') }}
						</flux:button>
					</div>
				@endunless
			</flux:card>
		@else
			<flux:table aria-label="{{ __('Departments list') }}" aria-describedby="departments-table-caption">
				<caption id="departments-table-caption" class="sr-only">
					{{ __('Department directory showing name, description, employees count, and available actions.') }}
				</caption>
				<flux:table.columns>
					<flux:table.column>{{ __('Name') }}</flux:table.column>
					<flux:table.column>{{ __('Description') }}</flux:table.column>
					<flux:table.column>{{ __('Employees') }}</flux:table.column>
					<flux:table.column>{{ __('Created') }}</flux:table.column>
					<flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
				</flux:table.columns>

				<flux:table.rows>
					@foreach ($departments as $department)
						<flux:table.row :key="$department->id">
							<flux:table.cell variant="strong">
								<a href="{{ route('departments.show', $department) }}" class="hover:underline" wire:navigate>
									{{ $department->name }}
								</a>
							</flux:table.cell>
							<flux:table.cell>{{ $department->description ?? '-' }}</flux:table.cell>
							<flux:table.cell>{{ $department->employees_count }}</flux:table.cell>
							<flux:table.cell>{{ $department->created_at->format('M d, Y') }}</flux:table.cell>
							<flux:table.cell align="end">
								<div class="flex items-center justify-end gap-1">
									<flux:button
										href="{{ route('departments.show', $department) }}"
										variant="ghost"
										size="sm"
										icon="eye"
										aria-label="{{ __('View :name', ['name' => $department->name]) }}"
										title="{{ __('View') }}"
										wire:navigate
									/>
									<flux:button
										href="{{ route('departments.edit', $department) }}"
										variant="ghost"
										size="sm"
										icon="pencil"
										aria-label="{{ __('Edit :name', ['name' => $department->name]) }}"
										title="{{ __('Edit') }}"
										wire:navigate
									/>
									<flux:modal.trigger :name="'delete-department-'.$department->id">
										<flux:button
											variant="ghost"
											size="sm"
											icon="trash"
											aria-label="{{ __('Delete :name', ['name' => $department->name]) }}"
											title="{{ __('Delete') }}"
										/>
									</flux:modal.trigger>
								</div>
							</flux:table.cell>
						</flux:table.row>
					@endforeach
				</flux:table.rows>
			</flux:table>

			@foreach ($departments as $department)
				<flux:modal
					:name="'delete-department-'.$department->id"
					:aria-labelledby="'delete-department-title-'.$department->id"
					:aria-describedby="'delete-department-desc-'.$department->id"
					class="md:w-96"
				>
					<div class="space-y-6">
						<div>
							<flux:heading :id="'delete-department-title-'.$department->id" size="lg">{{ __('Delete Department') }}</flux:heading>
							<flux:subheading :id="'delete-department-desc-'.$department->id" class="mt-2">
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
			@endforeach

			@if ($departments->hasPages())
				<div class="flex justify-end">
					{{ $departments->onEachSide(1)->links() }}
				</div>
			@endif
		@endif
	</main>
</x-layouts::app>
