<?php

namespace App\Modules\SecurityManagement\Policies;


use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Modules\SecurityManagement\Models\User;

class EditAccountPolicy{

    use HandlesAuthorization;

    /** Autorise la modification des infos du compte connecté
     * @param Authenticatable $authenticatable
     * @param User $user
     * @return bool
     */
    public function editAccount(Authenticatable $authenticatable,User $user): bool
    {
        return $user->is($authenticatable);
    }
}
