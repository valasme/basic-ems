<?php

namespace App\Http\Requests;

use App\Models\DuePayment;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDuePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', DuePayment::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists(Employee::class, 'id')->where('user_id', $this->user()->id),
            ],
            'amount' => ['bail', 'required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'status' => ['bail', 'required', 'string', Rule::in(DuePayment::STATUSES)],
            'notes' => ['bail', 'nullable', 'string', 'max:2000'],
            'pay_date' => ['bail', 'required', 'date', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'notes' => $this->input('notes')
                ? Str::of((string) $this->input('notes'))->squish()->toString()
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
            'employee_id.required' => 'Please select an employee.',
            'employee_id.exists' => 'The selected employee is invalid.',
            'amount.required' => 'The payment amount is required.',
            'amount.numeric' => 'The payment amount must be a valid number.',
            'amount.min' => 'The payment amount must be at least 0.01.',
            'amount.max' => 'The payment amount may not be greater than 9,999,999.99.',
            'status.required' => 'Please choose a payment status.',
            'status.in' => 'The selected payment status is invalid.',
            'notes.max' => 'The notes may not be greater than 2000 characters.',
            'pay_date.required' => 'The pay date is required.',
            'pay_date.date' => 'Please provide a valid pay date.',
        ];
    }
}
