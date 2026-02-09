<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property string|null $job_title
 * @property string|null $department
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read string $full_name
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Task> $tasks
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
        'department',
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
     * Get the tasks for the employee.
     *
     * @return HasMany<Task, $this>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
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
            // Match each word against first_name, last_name, or email
            foreach ($words as $word) {
                $subQuery->where(fn (Builder $q): Builder => $q
                    ->where('first_name', 'like', "%{$word}%")
                    ->orWhere('last_name', 'like', "%{$word}%")
                    ->orWhere('email', 'like', "%{$word}%")
                );
            }

            // Also allow exact full name match via concatenation (database-agnostic)
            $subQuery->orWhereRaw(
                "LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?",
                ['%'.mb_strtolower($searchTerm).'%']
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
}
