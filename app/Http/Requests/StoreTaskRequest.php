<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Task::class);
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
                'nullable',
                'integer',
                Rule::exists(Employee::class, 'id')->where('user_id', $this->user()->id),
            ],
            'title' => ['bail', 'required', 'string', 'min:3', 'max:255'],
            'status' => ['bail', 'required', 'string', Rule::in(Task::STATUSES)],
            'priority' => ['bail', 'required', 'string', Rule::in($this->allowedPriorities())],
            'description' => ['bail', 'nullable', 'string', 'max:2000'],
            'due_date' => ['bail', 'nullable', 'date', 'date_format:Y-m-d'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $status = $this->input('status');

        $this->merge([
            'title' => Str::of((string) $this->input('title'))
                ->squish()
                ->toString(),
            'description' => $this->input('description')
                ? Str::of((string) $this->input('description'))->squish()->toString()
                : null,
            'due_date' => $this->input('due_date') ?: null,
            'priority' => $status === 'completed' ? 'none' : $this->input('priority'),
        ]);
    }

    /**
     * Get the allowed priorities based on the selected status.
     *
     * @return list<string>
     */
    private function allowedPriorities(): array
    {
        return $this->input('status') === 'completed'
            ? ['none']
            : array_values(array_diff(Task::PRIORITIES, ['none']));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employee_id.exists' => 'The selected employee is invalid.',
            'title.required' => 'The task title is required.',
            'title.min' => 'The task title must be at least 3 characters.',
            'title.max' => 'The task title may not be greater than 255 characters.',
            'status.required' => 'Please choose a task status.',
            'status.in' => 'The selected task status is invalid.',
            'priority.required' => 'Please choose a task priority.',
            'priority.in' => 'The selected task priority is invalid.',
            'description.max' => 'The description may not be greater than 2000 characters.',
            'due_date.date' => 'Please provide a valid due date.',
            'due_date.date_format' => 'The due date must be in YYYY-MM-DD format.',
        ];
    }
}
