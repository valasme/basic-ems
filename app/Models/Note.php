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
 * @property string $note_title
 * @property string|null $note_description
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read User $user
 */
class Note extends Model
{
    /** @use HasFactory<\Database\Factories\NoteFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'note_title',
        'note_description',
    ];

    /**
     * Get the user that owns the note.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to search notes by title or description.
     *
     * @param  Builder<Note>  $query
     * @return Builder<Note>
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $searchTerm = Str::of((string) $search)
            ->squish()
            ->toString();

        if ($searchTerm === '') {
            return $query;
        }

        return $query->where(
            fn (Builder $subQuery): Builder => $subQuery
                ->where('note_title', 'like', "%{$searchTerm}%")
                ->orWhere('note_description', 'like', "%{$searchTerm}%")
        );
    }

    /**
     * Scope a query to notes owned by the given user.
     *
     * @param  Builder<Note>  $query
     * @return Builder<Note>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
