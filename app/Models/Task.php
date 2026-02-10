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
 * @property int|null $employee_id
 * @property string $title
 * @property string $status
 * @property string $priority
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read Employee|null $employee
 */
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    /**
     * Allowed task statuses.
     *
     * @var list<string>
     */
    public const STATUSES = [
        'pending',
        'in_progress',
        'completed',
    ];

    /**
     * Allowed task priorities.
     *
     * @var list<string>
     */
    public const PRIORITIES = [
        'urgent',
        'high',
        'medium',
        'low',
        'none',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'title',
        'status',
        'priority',
        'description',
        'due_date',
    ];

    /**
     * Get the employee that owns the task.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user that owns the task.
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
            'due_date' => 'date',
        ];
    }

    /**
     * Scope a query to search tasks by title or employee name.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
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

        return $query->where(
            fn (Builder $subQuery): Builder => $subQuery
                ->where('title', 'like', "%{$searchTerm}%")
                ->orWhereHas('employee', function (Builder $employeeQuery) use ($words, $searchTerm): void {
                    foreach ($words as $word) {
                        $employeeQuery->where(fn (Builder $q): Builder => $q
                            ->where('first_name', 'like', "%{$word}%")
                            ->orWhere('last_name', 'like', "%{$word}%")
                        );
                    }

                    $employeeQuery->orWhereRaw(
                        "LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?",
                        ['%'.mb_strtolower($searchTerm).'%']
                    );
                })
        );
    }

    /**
     * Scope a query to tasks owned by the given user.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to urgent tasks.
     *
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', 'urgent');
    }
}
