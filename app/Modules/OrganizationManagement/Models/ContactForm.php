<?php

namespace App\Modules\OrganizationManagement\Models;

use App\Modules\BaseModel;
use App\Modules\UsesUuidV6;

/**
 * @property string fullname
 */
class ContactForm extends BaseModel {

    use UsesUuidV6;

    protected $table = "organization__mgt_contacts_form";
    protected $keyType = 'string';
    public $incrementing = false;


    public function getObjectName(): string
    {
        return $this->fullname;
    }
}
