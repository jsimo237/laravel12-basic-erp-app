<?php

namespace App\Modules\OrganizationManagement\Models;

use App\Modules\BaseModel;

/**
 * @property string fullname
 */
class ContactForm extends BaseModel {

    protected $table = "organization__mgt_contacts_form";


    public function getObjectName(): string
    {
        return $this->fullname;
    }
}
