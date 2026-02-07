<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Employee::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:255'],
            'last_name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(Employee::class)],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'work_in' => ['nullable', 'date_format:H:i'],
            'work_out' => ['nullable', 'date_format:H:i'],
            'pay_day' => ['nullable', 'integer', 'min:1', 'max:31'],
            'pay_amount' => ['nullable', 'numeric', 'min:0'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => Str::of((string) $this->input('first_name'))
                ->squish()
                ->toString(),
            'last_name' => Str::of((string) $this->input('last_name'))
                ->squish()
                ->toString(),
            'email' => Str::of((string) $this->input('email'))
                ->trim()
                ->lower()
                ->toString(),
            'phone_number' => $this->input('phone_number')
                ? Str::of((string) $this->input('phone_number'))->squish()->toString()
                : null,
            'job_title' => $this->input('job_title')
                ? Str::of((string) $this->input('job_title'))->squish()->toString()
                : null,
            'department' => $this->input('department')
                ? Str::of((string) $this->input('department'))->squish()->toString()
                : null,
        ]);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            'first_name.min' => 'The first name must be at least 2 characters.',
            'first_name.max' => 'The first name may not be greater than 255 characters.',
            'last_name.required' => 'The last name is required.',
            'last_name.min' => 'The last name must be at least 2 characters.',
            'last_name.max' => 'The last name may not be greater than 255 characters.',
            'email.required' => 'The email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'The email address may not be greater than 255 characters.',
            'email.lowercase' => 'The email address must be lowercase.',
            'email.unique' => 'This email address is already in use.',
            'phone_number.max' => 'The phone number may not be greater than 50 characters.',
            'work_in.date_format' => 'The work in time must be in HH:MM format.',
            'work_out.date_format' => 'The work out time must be in HH:MM format.',
            'pay_day.integer' => 'The pay day must be a whole number (day of month).',
            'pay_day.min' => 'The pay day must be at least 1 (day of month).',
            'pay_day.max' => 'The pay day may not be greater than 31 (day of month).',
            'pay_amount.numeric' => 'The pay amount must be a number.',
            'pay_amount.min' => 'The pay amount must be at least 0.',
            'job_title.max' => 'The job title may not be greater than 255 characters.',
            'department.max' => 'The department may not be greater than 255 characters.',
        ];
    }
}
