<?php

namespace App\Policies;

use App\Models\DuePayment;
use App\Models\User;

class DuePaymentPolicy
{
    /**
     * Determine whether the user can view any due payments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the due payment.
     */
    public function view(User $user, DuePayment $duePayment): bool
    {
        return $this->isOwner($user, $duePayment);
    }

    /**
     * Determine whether the user can create due payments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the due payment.
     */
    public function update(User $user, DuePayment $duePayment): bool
    {
        return $this->isOwner($user, $duePayment);
    }

    /**
     * Determine whether the user can delete the due payment.
     */
    public function delete(User $user, DuePayment $duePayment): bool
    {
        return $this->isOwner($user, $duePayment);
    }

    /**
     * Determine if the user owns the due payment.
     */
    private function isOwner(User $user, DuePayment $duePayment): bool
    {
        return $user->id === $duePayment->user_id;
    }
}
