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
        ];
    }
}
