<x-layouts::app :title="__('Employees - BasicEMS')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center justify-between">
            <flux:heading size="xl">{{ __('Employees') }}</flux:heading>
            <flux:button href="{{ route('employees.create') }}" variant="primary" wire:navigate>
                {{ __('Add Employee') }}
            </flux:button>
        </div>

        @if (session('success'))
            <flux:callout variant="success" icon="check-circle" dismissible>
                {{ session('success') }}
            </flux:callout>
        @endif

        <form method="GET" action="{{ route('employees.index') }}" class="flex items-center gap-2">
            <flux:input
                type="search"
                name="search"
                placeholder="{{ __('Search employees...') }}"
                value="{{ $search }}"
                icon="magnifying-glass"
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
                    <flux:table.column>{{ __('Created') }}</flux:table.column>
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
                            <flux:table.cell class="whitespace-nowrap">
                                {{ $employee->created_at->format('M d, Y') }}
                            </flux:table.cell>
                            <flux:table.cell align="end">
                                <div class="flex items-center justify-end gap-1">
                                    <flux:button href="{{ route('employees.show', $employee) }}" variant="ghost" size="sm" icon="eye" wire:navigate />
                                    <flux:button href="{{ route('employees.edit', $employee) }}" variant="ghost" size="sm" icon="pencil" wire:navigate />
                                    <flux:modal.trigger :name="'delete-employee-' . $employee->id">
                                        <flux:button variant="ghost" size="sm" icon="trash" />
                                    </flux:modal.trigger>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>

                        <flux:modal :name="'delete-employee-' . $employee->id" class="md:w-96">
                            <div class="space-y-6">
                                <div>
                                    <flux:heading size="lg">{{ __('Delete Employee') }}</flux:heading>
                                    <flux:subheading class="mt-2">
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
                </flux:table.rows>
            </flux:table>
        @endif
    </div>
</x-layouts::app>
