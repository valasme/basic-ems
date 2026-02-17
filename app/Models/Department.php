<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Employee> $employees
 */
class Department extends Model
{
    /** @use HasFactory<\Database\Factories\DepartmentFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'description',
    ];

    /**
     * Get the user that owns the department.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employees in this department.
     *
     * @return HasMany<Employee, $this>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope a query to search departments by name or description.
     *
     * @param  Builder<Department>  $query
     * @return Builder<Department>
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

        return $query->where(function (Builder $subQuery) use ($words): void {
            foreach ($words as $word) {
                $escaped = str_replace(['%', '_'], ['\%', '\_'], $word);

                $subQuery->where(fn (Builder $q): Builder => $q
                    ->where('name', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%")
                );
            }
        });
    }
}
