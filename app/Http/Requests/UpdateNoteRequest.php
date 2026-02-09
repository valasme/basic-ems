<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateNoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('note'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'note_title' => ['bail', 'required', 'string', 'min:3', 'max:255'],
            'note_description' => ['bail', 'nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'note_title' => Str::of((string) $this->input('note_title'))
                ->squish()
                ->toString(),
            'note_description' => $this->input('note_description')
                ? Str::of((string) $this->input('note_description'))->squish()->toString()
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
            'note_title.required' => 'The note title is required.',
            'note_title.min' => 'The note title must be at least 3 characters.',
            'note_title.max' => 'The note title may not be greater than 255 characters.',
            'note_description.max' => 'The note description may not be greater than 5000 characters.',
        ];
    }
}
