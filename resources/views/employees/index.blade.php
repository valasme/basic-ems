<x-layouts::app :title="__('Employees - BasicEMS')">
    <main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
        <div class="flex items-center justify-between">
            <flux:heading id="page-title" size="xl">{{ __('Employees') }}</flux:heading>
            <flux:button href="{{ route('employees.create') }}" variant="primary" wire:navigate>
                {{ __('Add Employee') }}
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

        <form method="GET" action="{{ route('employees.index') }}" role="search" aria-describedby="employees-search-help" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <p id="employees-search-help" class="sr-only">
                {{ __('Search employees by text and sort the list using the filter dropdown.') }}
            </p>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <label for="employees-search" class="sr-only">{{ __('Search employees') }}</label>
                <flux:input
                    id="employees-search"
                    type="search"
                    name="search"
                    placeholder="{{ __('Search employees...') }}"
                    value="{{ $search }}"
                    icon="magnifying-glass"
                    class="w-full max-w-xs"
                />
                <flux:button type="submit">{{ __('Search') }}</flux:button>
            </div>
            <label for="employees-filter" class="sr-only">{{ __('Sort employees') }}</label>
            <flux:select id="employees-filter" name="filter" aria-label="{{ __('Sort employees') }}" class="min-w-56">
                <option value="name_alpha" @selected($filter === 'name_alpha')>{{ __('Name (A-Z)') }}</option>
                <option value="name_reverse" @selected($filter === 'name_reverse')>{{ __('Name (Z-A)') }}</option>
                <option value="email_alpha" @selected($filter === 'email_alpha')>{{ __('Email (A-Z)') }}</option>
                <option value="email_reverse" @selected($filter === 'email_reverse')>{{ __('Email (Z-A)') }}</option>
                <option value="department_alpha" @selected($filter === 'department_alpha')>{{ __('Department (A-Z)') }}</option>
                <option value="department_reverse" @selected($filter === 'department_reverse')>{{ __('Department (Z-A)') }}</option>
                <option value="job_title_alpha" @selected($filter === 'job_title_alpha')>{{ __('Job Title (A-Z)') }}</option>
                <option value="job_title_reverse" @selected($filter === 'job_title_reverse')>{{ __('Job Title (Z-A)') }}</option>
                <option value="created_newest" @selected($filter === 'created_newest')>{{ __('Created Date (Newest)') }}</option>
                <option value="created_oldest" @selected($filter === 'created_oldest')>{{ __('Created Date (Oldest)') }}</option>
                <option value="salary_highest" @selected($filter === 'salary_highest')>{{ __('Calculated Salary (Highest)') }}</option>
                <option value="salary_lowest" @selected($filter === 'salary_lowest')>{{ __('Calculated Salary (Lowest)') }}</option>
            </flux:select>
            @if ($search || $filter !== 'name_alpha')
                <flux:button href="{{ route('employees.index') }}" variant="ghost" wire:navigate>
                    {{ __('Clear Filters') }}
                </flux:button>
            @endif
        </form>

        <p class="sr-only" aria-live="polite">
            {{ trans_choice('{0} No employees found|{1} :count employee found|[2,*] :count employees found', $employees->total(), ['count' => $employees->total()]) }}
        </p>

        @if ($employees->isEmpty())
            <flux:card class="text-center" role="status" aria-live="polite">
                <flux:icon name="users" class="mx-auto size-12 text-zinc-400" aria-hidden="true" />
                <flux:heading size="lg" class="mt-4">{{ __('No employees found') }}</flux:heading>
                <flux:subheading class="mt-2">
                    @if ($search)
                        {{ __('No employees match your search criteria. Try adjusting your search.') }}
                    @else
                        {{ __('Get started by adding your first employee.') }}
                    @endif
                </flux:subheading>
                @unless ($search)
                    <div class="mt-6">
                        <flux:button href="{{ route('employees.create') }}" variant="primary" wire:navigate>
                            {{ __('Add Employee') }}
                        </flux:button>
                    </div>
                @endunless
            </flux:card>
        @else
            <flux:table aria-label="{{ __('Employees list') }}" aria-describedby="employees-table-caption">
                <caption id="employees-table-caption" class="sr-only">
                    {{ __('Employee directory showing name, email, department, job title, and available actions.') }}
                </caption>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Email') }}</flux:table.column>
                    <flux:table.column>{{ __('Department') }}</flux:table.column>
                    <flux:table.column>{{ __('Job Title') }}</flux:table.column>
                    <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($employees as $employee)
                        <flux:table.row :key="$employee->id">
                            <flux:table.cell variant="strong">
                                <a href="{{ route('employees.show', $employee) }}" class="hover:underline" wire:navigate>
                                    {{ $employee->full_name }}
                                </a>
                            </flux:table.cell>
                            <flux:table.cell>{{ $employee->email }}</flux:table.cell>
                            <flux:table.cell>{{ $employee->department?->name ?? '-' }}</flux:table.cell>
                            <flux:table.cell>{{ $employee->job_title ?? '-' }}</flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button
                                        href="{{ route('employees.show', $employee) }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="eye"
                                        aria-label="{{ __('View :name', ['name' => $employee->full_name]) }}"
                                        title="{{ __('View') }}"
                                        wire:navigate
                                    />
                                    <flux:button
                                        href="{{ route('employees.edit', $employee) }}"
                                        variant="ghost"
                                        size="sm"
                                        icon="pencil"
                                        aria-label="{{ __('Edit :name', ['name' => $employee->full_name]) }}"
                                        title="{{ __('Edit') }}"
                                        wire:navigate
                                    />
                                    <flux:modal.trigger :name="'delete-employee-'.$employee->id">
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            aria-label="{{ __('Delete :name', ['name' => $employee->full_name]) }}"
                                            title="{{ __('Delete') }}"
                                        />
                                    </flux:modal.trigger>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>

            @foreach ($employees as $employee)
                <flux:modal
                    :name="'delete-employee-'.$employee->id"
                    :aria-labelledby="'delete-employee-title-'.$employee->id"
                    :aria-describedby="'delete-employee-desc-'.$employee->id"
                    class="md:w-96"
                >
                    <div class="space-y-6">
                        <div>
                            <flux:heading :id="'delete-employee-title-'.$employee->id" size="lg">{{ __('Delete Employee') }}</flux:heading>
                            <flux:subheading :id="'delete-employee-desc-'.$employee->id" class="mt-2">
                                {{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $employee->full_name]) }}
                            </flux:subheading>
                        </div>
                        <div class="flex gap-3">
                            <flux:spacer />
                            <flux:modal.close>
                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <form method="POST" action="{{ route('employees.destroy', $employee) }}">
                                @csrf
                                @method('DELETE')
                                <flux:button type="submit" variant="danger">{{ __('Delete') }}</flux:button>
                            </form>
                        </div>
                    </div>
                </flux:modal>
            @endforeach

            @if ($employees->hasPages())
                <div class="flex justify-end">
                    {{ $employees->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </main>
</x-layouts::app>
