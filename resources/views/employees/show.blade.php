<x-layouts::app :title="__(':name - BasicEMS', ['name' => $employee->full_name])">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center gap-4">
            <flux:button
                href="{{ route('employees.index') }}"
                variant="ghost"
                icon="arrow-left"
                aria-label="{{ __('Back to employees') }}"
                wire:navigate
            />
            <flux:heading size="xl">{{ $employee->full_name }}</flux:heading>
        </div>

        @if (session('error'))
            <flux:callout variant="danger" role="alert" aria-live="assertive">
                <flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
                <flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
            </flux:callout>
        @endif

        <flux:card class="flex-1">
            <div class="space-y-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <flux:avatar size="lg" :name="$employee->full_name" :initials="substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1)" />
                        <div>
                            <flux:heading size="lg">{{ $employee->full_name }}</flux:heading>
                            <flux:subheading>{{ $employee->email }}</flux:subheading>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <flux:button href="{{ route('employees.edit', $employee) }}" variant="ghost" icon="pencil" wire:navigate>
                            {{ __('Edit') }}
                        </flux:button>
                        <flux:modal.trigger name="delete-employee">
                            <flux:button variant="ghost" icon="trash">
                                {{ __('Delete') }}
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>

                <flux:separator />

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <flux:subheading>{{ __('First Name') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->first_name }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Last Name') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->last_name }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Email Address') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->email }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Phone Number') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->phone_number ?? '-' }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Department') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->department ?? '-' }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Job Title') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->job_title ?? '-' }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Work In') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->work_in ?? '-' }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Work Out') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->work_out ?? '-' }}</flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Pay Day') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">
                            {{ $employee->pay_day ? __('Every month on day :day', ['day' => $employee->pay_day]) : '-' }}
                        </flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Pay Amount (Monthly)') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">
                            {{ number_format((float) $employee->pay_amount, 2) }}
                        </flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Salary (Yearly)') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">
                            {{ $employee->pay_salary !== null ? number_format((float) $employee->pay_salary, 2) : '-' }}
                        </flux:heading>
                    </div>
                    <div>
                        <flux:subheading>{{ __('Created') }}</flux:subheading>
                        <flux:heading size="sm" class="mt-1">{{ $employee->created_at->format('M d, Y \a\t g:i A') }}</flux:heading>
                    </div>
                </div>
            </div>
        </flux:card>

        <flux:modal
            name="delete-employee"
            aria-labelledby="delete-employee-title"
            aria-describedby="delete-employee-desc"
            class="md:w-96"
        >
            <div class="space-y-6">
                <div>
                    <flux:heading id="delete-employee-title" size="lg">{{ __('Delete Employee') }}</flux:heading>
                    <flux:subheading id="delete-employee-desc" class="mt-2">
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
    </div>
</x-layouts::app>
