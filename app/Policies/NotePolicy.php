<?php

namespace App\Policies;

use App\Models\Note;
use App\Models\User;

class NotePolicy
{
    /**
     * Determine whether the user can view any notes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the note.
     */
    public function view(User $user, Note $note): bool
    {
        return $this->isOwner($user, $note);
    }

    /**
     * Determine whether the user can create notes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the note.
     */
    public function update(User $user, Note $note): bool
    {
        return $this->isOwner($user, $note);
    }

    /**
     * Determine whether the user can delete the note.
     */
    public function delete(User $user, Note $note): bool
    {
        return $this->isOwner($user, $note);
    }

    /**
     * Determine if the user owns the note.
     */
    private function isOwner(User $user, Note $note): bool
    {
        return $user->id === $note->user_id;
    }
}
