<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function manageSuppliers(User $user)
    {
        return $user->canManageSuppliers();
    }

    public function manageCustomers(User $user)
    {
        return $user->canManageCustomers();
    }

    public function manageLots(User $user)
    {
        return $user->canManageLots();
    }

    public function manageSales(User $user)
    {
        return $user->canManageSales();
    }

    public function managePayments(User $user)
    {
        return $user->canManagePayments();
    }

    public function manageUsers(User $user)
    {
        return $user->canManageUsers();
    }
}
