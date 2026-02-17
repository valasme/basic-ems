<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $employee_id
 * @property \Illuminate\Support\Carbon $attendance_date
 * @property string $work_in
 * @property string|null $work_out
 * @property string|null $note
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read Employee $employee
 */
class Attendance extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'attendance_date',
        'work_in',
        'work_out',
        'note',
    ];

    /**
     * Get the employee that owns the attendance entry.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user that owns the attendance entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    /**
     * Scope a query to attendance entries owned by the given user.
     *
     * @param  Builder<Attendance>  $query
     * @return Builder<Attendance>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('attendances.user_id', $user->id);
    }

    /**
     * Scope a query to search attendance by employee name or note.
     *
     * @param  Builder<Attendance>  $query
     * @return Builder<Attendance>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $searchTerm = Str::of((string) $search)
            ->squish()
            ->toString();

        if ($searchTerm === '') {
            return $query;
        }

        $words = explode(' ', $searchTerm);

        return $query->where(function (Builder $subQuery) use ($words, $searchTerm): void {
            $escapedTerm = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);

            $subQuery->where('attendances.note', 'like', "%{$escapedTerm}%")
                ->orWhereHas('employee', function (Builder $employeeQuery) use ($words, $searchTerm): void {
                    foreach ($words as $word) {
                        $escaped = str_replace(['%', '_'], ['\%', '\_'], $word);

                        $employeeQuery->where(fn (Builder $q): Builder => $q
                            ->where('first_name', 'like', "%{$escaped}%")
                            ->orWhere('last_name', 'like', "%{$escaped}%")
                        );
                    }

                    $escapedFullName = str_replace(['%', '_'], ['\%', '\_'], mb_strtolower($searchTerm));

                    $employeeQuery->orWhereRaw(
                        "LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?",
                        ["%{$escapedFullName}%"]
                    );
                });
        });
    }
}
