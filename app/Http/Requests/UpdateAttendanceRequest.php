<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('attendance'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Attendance $attendance */
        $attendance = $this->route('attendance');

        return [
            'employee_id' => [
                'bail',
                'required',
                'integer',
                Rule::exists(Employee::class, 'id')->where('user_id', $this->user()->id),
            ],
            'attendance_date' => [
                'bail',
                'required',
                'date',
                'date_format:Y-m-d',
            ],
            'work_in' => ['bail', 'required', 'date_format:H:i'],
            'work_out' => ['bail', 'nullable', 'date_format:H:i', 'after:work_in'],
            'note' => ['bail', 'nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'note' => $this->input('note')
                ? Str::of((string) $this->input('note'))->squish()->toString()
                : null,
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        /** @var Attendance $attendance */
        $attendance = $this->route('attendance');

        $validator->after(function (Validator $validator) use ($attendance): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $employeeId = (int) $this->input('employee_id');
            $attendanceDate = (string) $this->input('attendance_date');

            $exists = Attendance::query()
                ->where('employee_id', $employeeId)
                ->whereDate('attendance_date', $attendanceDate)
                ->whereKeyNot($attendance->id)
                ->exists();

            if ($exists) {
                $validator->errors()->add('attendance_date', 'This employee already has attendance recorded for that date.');
            }
        });
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
            'attendance_date.required' => 'The attendance date is required.',
            'attendance_date.date' => 'Please provide a valid attendance date.',
            'attendance_date.date_format' => 'The attendance date must be in YYYY-MM-DD format.',
            'work_in.required' => 'The work in time is required.',
            'work_in.date_format' => 'The work in time must be in HH:MM format.',
            'work_out.date_format' => 'The work out time must be in HH:MM format.',
            'work_out.after' => 'The work out time must be after the work in time.',
            'note.max' => 'The note may not be greater than 1000 characters.',
        ];
    }
}
