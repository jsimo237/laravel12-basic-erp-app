<?php

namespace App\Modules\SecurityManagement\Observers;

use App\Modules\SecurityManagement\Models\Role;
use App\Modules\SecurityManagement\Models\User;

class UserObserver{



    /** Se produit lorsque la resource est créee
     * @param User $user
     * @return void
     */
    public function creating(User $user){
      //  $attributes = $user->getAttributes();

        $isManager = $user->is_manager ?? false;

        if ($isManager){
          //  $user->setAttribute("manager_id",$user->id);
        }

        if ($isManager) {
          //  $user->assignRole(Role::MANAGER);
        }
    }
}
