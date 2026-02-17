<?php

namespace App\Http\Requests;

use App\Models\Department;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Department::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                Rule::unique(Department::class)
                    ->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Str::of((string) $this->input('name'))
                ->squish()
                ->toString(),
            'description' => $this->input('description')
                ? Str::of((string) $this->input('description'))->squish()->toString()
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
            'name.required' => 'The department name is required.',
            'name.min' => 'The department name must be at least 2 characters.',
            'name.max' => 'The department name may not be greater than 255 characters.',
            'name.unique' => 'You already have a department with this name.',
            'description.max' => 'The description may not be greater than 2000 characters.',
        ];
    }
}
