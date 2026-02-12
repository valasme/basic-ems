<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendance entries.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the attendance entry.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return $this->isOwner($user, $attendance);
    }

    /**
     * Determine whether the user can create attendance entries.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the attendance entry.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        return $this->isOwner($user, $attendance);
    }

    /**
     * Determine whether the user can delete the attendance entry.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        return $this->isOwner($user, $attendance);
    }

    /**
     * Determine if the user owns the attendance entry.
     */
    private function isOwner(User $user, Attendance $attendance): bool
    {
        return $user->id === $attendance->user_id;
    }
}
