<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int $employee_id
 * @property string $amount
 * @property string $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $pay_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 * @property-read Employee $employee
 * @property-read int $days_until_due
 * @property-read string $urgency
 */
class DuePayment extends Model
{
    /** @use HasFactory<\Database\Factories\DuePaymentFactory> */
    use HasFactory;

    /**
     * Allowed payment statuses.
     *
     * @var list<string>
     */
    public const STATUSES = [
        'pending',
        'paid',
    ];

    /**
     * Urgency levels based on days until due.
     *
     * @var array<string, array{min: int|null, max: int|null, color: string}>
     */
    public const URGENCY_LEVELS = [
        'overdue' => ['min' => null, 'max' => -1, 'color' => 'red'],
        'urgent' => ['min' => 0, 'max' => 1, 'color' => 'red'],
        'soon' => ['min' => 2, 'max' => 3, 'color' => 'orange'],
        'upcoming' => ['min' => 4, 'max' => 7, 'color' => 'yellow'],
        'scheduled' => ['min' => 8, 'max' => null, 'color' => 'green'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'employee_id',
        'amount',
        'status',
        'notes',
        'pay_date',
    ];

    /**
     * Get the user that owns the payment.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee this payment is for.
     *
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pay_date' => 'date',
        ];
    }

    /**
     * Get the number of days until the payment is due.
     */
    public function getDaysUntilDueAttribute(): int
    {
        return (int) Carbon::today()->diffInDays($this->pay_date, false);
    }

    /**
     * Get the urgency level based on days until due.
     */
    public function getUrgencyAttribute(): string
    {
        $days = $this->days_until_due;

        foreach (self::URGENCY_LEVELS as $level => $range) {
            $min = $range['min'];
            $max = $range['max'];

            if (($min === null || $days >= $min) && ($max === null || $days <= $max)) {
                return $level;
            }
        }

        return 'scheduled';
    }

    /**
     * Get the color for the current urgency level.
     */
    public function getUrgencyColorAttribute(): string
    {
        return self::URGENCY_LEVELS[$this->urgency]['color'] ?? 'zinc';
    }

    /**
     * Scope a query to search payments by employee name or notes.
     *
     * @param  Builder<DuePayment>  $query
     * @return Builder<DuePayment>
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
        $escapedTerm = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);

        return $query->where(
            fn (Builder $subQuery): Builder => $subQuery
                ->where('notes', 'like', "%{$escapedTerm}%")
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
                })
        );
    }

    /**
     * Scope a query to payments owned by the given user.
     *
     * @param  Builder<DuePayment>  $query
     * @return Builder<DuePayment>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to pending payments only.
     *
     * @param  Builder<DuePayment>  $query
     * @return Builder<DuePayment>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to order by pay date urgency (soonest first).
     *
     * @param  Builder<DuePayment>  $query
     * @return Builder<DuePayment>
     */
    public function scopeOrderByUrgency(Builder $query): Builder
    {
        return $query->orderBy('pay_date');
    }
}
