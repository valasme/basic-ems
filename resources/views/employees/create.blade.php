<x-layouts::app :title="__('Add Employee - BasicEMS')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div class="flex items-center gap-4">
            <flux:button href="{{ route('employees.index') }}" variant="ghost" icon="arrow-left" wire:navigate />
            <flux:heading size="xl">{{ __('Add Employee') }}</flux:heading>
        </div>

        <flux:card class="flex-1">
            <form method="POST" action="{{ route('employees.store') }}" class="flex h-full flex-col gap-6">
                @csrf

                <div class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('First Name') }}</flux:label>
                        <flux:input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="{{ __('Enter first name') }}"
                            required
                            autofocus
                        />
                        <flux:error name="first_name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Last Name') }}</flux:label>
                        <flux:input
                            type="text"
                            name="last_name"
                            value="{{ old('last_name') }}"
                            placeholder="{{ __('Enter last name') }}"
                            required
                        />
                        <flux:error name="last_name" />
                    </flux:field>

                    <flux:field class="sm:col-span-2">
                        <flux:label>{{ __('Email Address') }}</flux:label>
                        <flux:input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="{{ __('Enter email address') }}"
                            required
                        />
                        <flux:error name="email" />
                    </flux:field>
                </div>

                <div class="mt-6 flex items-center gap-3">
                    <flux:spacer />
                    <flux:button href="{{ route('employees.index') }}" variant="ghost" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ __('Create Employee') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>
