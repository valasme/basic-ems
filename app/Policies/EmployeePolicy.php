<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        return $this->isOwner($user, $employee);
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    /**
     * Determine if the user owns the employee.
     */
    private function isOwner(User $user, Employee $employee): bool
    {
        return $user->id === $employee->user_id;
    }
}
