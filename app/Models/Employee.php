<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone_number
 * @property string|null $work_in
 * @property string|null $work_out
 * @property int|null $pay_day
 * @property string $pay_amount
 * @property string|null $pay_salary
 * @property string|null $job_title
 * @property int|null $department_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string $full_name
 * @property-read User $user
 * @property-read Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Task> $tasks
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Attendance> $attendances
 */
class Employee extends Model
{
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'work_in',
        'work_out',
        'pay_day',
        'pay_amount',
        'job_title',
        'department_id',
    ];

    /**
     * Get the user that owns the employee.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department assigned to the employee.
     *
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the tasks for the employee.
     *
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the attendance entries for the employee.
     *
     * @return HasMany<Attendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the employee's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => Str::of("{$this->first_name} {$this->last_name}")
                ->squish()
                ->toString(),
        );
    }

    /**
     * Keep yearly salary in sync with monthly pay amount.
     */
    protected function payAmount(): Attribute
    {
        return Attribute::make(
            set: function ($value): array {
                if ($value === null || $value === '') {
                    return [
                        'pay_amount' => null,
                        'pay_salary' => null,
                    ];
                }

                $monthly = round((float) $value, 2);
                $annual = round($monthly * 12, 2);

                return [
                    'pay_amount' => number_format($monthly, 2, '.', ''),
                    'pay_salary' => number_format($annual, 2, '.', ''),
                ];
            },
        );
    }

    /**
     * Scope a query to search employees by name or email.
     *
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
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
            foreach ($words as $word) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $word);

                $subQuery->where(fn (Builder $q): Builder => $q
                    ->where('first_name', 'like', "%{$escaped}%")
                    ->orWhere('last_name', 'like', "%{$escaped}%")
                    ->orWhere('email', 'like', "%{$escaped}%")
                    ->orWhereHas('department', fn (Builder $departmentQuery): Builder => $departmentQuery
                        ->where('name', 'like', "%{$escaped}%")
                    )
                );
            }

            $escapedFullName = str_replace(['%', '_'], ['\\%', '\\_'], mb_strtolower($searchTerm));

            $isSqlite = $subQuery->getConnection()->getDriverName() === 'sqlite';
            $concat = $isSqlite
                ? "first_name || ' ' || last_name"
                : "CONCAT(first_name, ' ', last_name)";

            $subQuery->orWhereRaw(
                "LOWER($concat) LIKE ?",
                ["%{$escapedFullName}%"]
            );
        });
    }

    /**
     * Scope a query to order employees by name.
     *
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
     */
    public function scopeOrderByName(Builder $query): Builder
    {
        return $query->orderBy('first_name')->orderBy('last_name');
    }

    /**
     * Scope a query to employees with a pay day set.
     *
     * @param  Builder<Employee>  $query
     * @return Builder<Employee>
     */
    public function scopeWithPayDay(Builder $query): Builder
    {
        return $query->whereNotNull('pay_day');
    }

    /**
     * Get the next pay date based on the employee's pay_day.
     */
    public function getNextPayDateAttribute(): ?Carbon
    {
        if ($this->pay_day === null) {
            return null;
        }

        $today = Carbon::today();
        $payDay = min($this->pay_day, $today->daysInMonth);

        $nextPayDate = $today->copy()->day($payDay);

        if ($nextPayDate->lt($today)) {
            $nextPayDate->addMonth();
            $payDay = min($this->pay_day, $nextPayDate->daysInMonth);
            $nextPayDate->day($payDay);
        }

        return $nextPayDate;
    }

    /**
     * Get the number of days until the next pay date.
     */
    public function getDaysUntilPayAttribute(): ?int
    {
        $nextPayDate = $this->next_pay_date;

        if ($nextPayDate === null) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($nextPayDate, false);
    }

    /**
     * Get the urgency level based on days until pay.
     */
    public function getPayUrgencyAttribute(): string
    {
        $days = $this->days_until_pay;

        if ($days === null) {
            return 'none';
        }

        if ($days <= 1) {
            return 'urgent';
        }

        if ($days <= 3) {
            return 'soon';
        }

        if ($days <= 7) {
            return 'upcoming';
        }

        return 'scheduled';
    }

    /**
     * Get the color for the current pay urgency level.
     */
    public function getPayUrgencyColorAttribute(): string
    {
        return match ($this->pay_urgency) {
            'urgent' => 'red',
            'soon' => 'orange',
            'upcoming' => 'yellow',
            'scheduled' => 'green',
            default => 'zinc',
        };
    }
}
