<x-layouts::app :title="__('Employees - BasicEMS')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Employees') }}</flux:heading>
            <flux:button href="{{ route('employees.create') }}" variant="primary" wire:navigate>
                {{ __('Add Employee') }}
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

        <form method="GET" action="{{ route('employees.index') }}" role="search" class="flex items-center gap-2">
            <flux:input
                type="search"
                name="search"
                placeholder="{{ __('Search employees...') }}"
                value="{{ $search }}"
                icon="magnifying-glass"
                aria-label="{{ __('Search employees') }}"
                class="max-w-xs"
            />
            <flux:button type="submit">{{ __('Search') }}</flux:button>
            @if ($search)
                <flux:button href="{{ route('employees.index') }}" variant="ghost" wire:navigate>
                    {{ __('Clear') }}
                </flux:button>
            @endif
        </form>

        @if ($employees->isEmpty())
            <flux:card class="text-center">
                <flux:icon name="users" class="mx-auto size-12 text-zinc-400" />
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
            <flux:table>
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
                            <flux:table.cell>{{ $employee->department ?? '-' }}</flux:table.cell>
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
                                    <flux:modal.trigger :name="'delete-employee-' . $employee->id">
                                        <flux:button
                                            variant="ghost"
                                            size="sm"
                                            icon="trash"
                                            aria-label="{{ __('Delete :name', ['name' => $employee->full_name]) }}"
                                            title="{{ __('Delete') }}"
                                            x-data=""
                                            x-on:click.prevent="$dispatch('open-modal', 'delete-employee-{{ $employee->id }}')"
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
                    :name="'delete-employee-' . $employee->id"
                    aria-labelledby="delete-employee-{{ $employee->id }}-title"
                    aria-describedby="delete-employee-{{ $employee->id }}-desc"
                    class="md:w-96"
                >
                    <div class="space-y-6">
                        <div>
                            <flux:heading id="delete-employee-{{ $employee->id }}-title" size="lg">{{ __('Delete Employee') }}</flux:heading>
                            <flux:subheading id="delete-employee-{{ $employee->id }}-desc" class="mt-2">
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
    </div>
</x-layouts::app>
