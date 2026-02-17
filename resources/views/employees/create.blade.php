<x-layouts::app :title="__('Add Employee - BasicEMS')">
    <main class="flex h-full w-full flex-1 flex-col gap-6" role="main" aria-labelledby="page-title">
        <div class="flex items-center gap-4">
            <flux:button
                href="{{ route('employees.index') }}"
                variant="ghost"
                icon="arrow-left"
                aria-label="{{ __('Back to employees') }}"
                wire:navigate
            />
            <flux:heading id="page-title" size="xl">{{ __('Add Employee') }}</flux:heading>
        </div>

        @if (session('error'))
            <flux:callout variant="danger" role="alert" aria-live="assertive">
                <flux:heading size="sm">{{ __('Something went wrong') }}</flux:heading>
                <flux:subheading class="mt-1">{{ session('error') }}</flux:subheading>
            </flux:callout>
        @endif

        @if ($errors->any())
            <flux:callout variant="danger" role="alert" aria-live="assertive">
                <flux:heading size="sm">{{ __('Please fix the following errors') }}</flux:heading>
                <ul class="mt-2 list-disc ps-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </flux:callout>
        @endif

        <flux:card class="flex-1">
            <form method="POST" action="{{ route('employees.store') }}" class="flex h-full flex-col gap-6" aria-describedby="employee-form-help">
                @csrf

                <p id="employee-form-help" class="sr-only">
                    {{ __('All fields marked as required must be completed before creating the employee.') }}
                </p>

                <fieldset class="grid flex-1 content-start auto-rows-min gap-6 sm:grid-cols-2">
                    <legend class="sr-only">{{ __('Employee information') }}</legend>
                    <flux:field>
                        <flux:label>{{ __('First Name') }}</flux:label>
                        <flux:input
                            type="text"
                            name="first_name"
                            value="{{ old('first_name') }}"
                            placeholder="{{ __('Enter first name') }}"
                            autocomplete="given-name"
                            required
                            aria-required="true"
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
                            autocomplete="family-name"
                            required
                            aria-required="true"
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
                            autocomplete="email"
                            required
                            aria-required="true"
                        />
                        <flux:error name="email" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Phone Number') }}</flux:label>
                        <flux:input
                            type="tel"
                            name="phone_number"
                            value="{{ old('phone_number') }}"
                            placeholder="{{ __('Enter phone number') }}"
                            autocomplete="tel"
                            inputmode="tel"
                        />
                        <flux:error name="phone_number" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Department') }}</flux:label>
                        <flux:select name="department_id" aria-label="{{ __('Department') }}">
                            <option value="">{{ __('Select department') }}</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="department_id" />
                    </flux:field>

                    <flux:field class="sm:col-span-2">
                        <flux:label>{{ __('Job Title') }}</flux:label>
                        <flux:input
                            type="text"
                            name="job_title"
                            value="{{ old('job_title') }}"
                            placeholder="{{ __('Enter job title') }}"
                            autocomplete="organization-title"
                        />
                        <flux:error name="job_title" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Work In') }}</flux:label>
                        <flux:input
                            type="time"
                            name="work_in"
                            value="{{ old('work_in') }}"
                        />
                        <flux:error name="work_in" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Work Out') }}</flux:label>
                        <flux:input
                            type="time"
                            name="work_out"
                            value="{{ old('work_out') }}"
                        />
                        <flux:error name="work_out" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Pay Day (Monthly)') }}</flux:label>
                        <flux:input
                            type="number"
                            name="pay_day"
                            min="1"
                            max="31"
                            value="{{ old('pay_day') }}"
                            placeholder="{{ __('Day of month (1-31)') }}"
                        />
                        <flux:error name="pay_day" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Pay Amount (Monthly)') }}</flux:label>
                        <flux:input
                            type="number"
                            name="pay_amount"
                            step="0.01"
                            min="0"
                            value="{{ old('pay_amount') }}"
                            placeholder="{{ __('Enter monthly pay amount') }}"
                        />
                        <flux:error name="pay_amount" />
                    </flux:field>
                </fieldset>

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
    </main>
</x-layouts::app>
