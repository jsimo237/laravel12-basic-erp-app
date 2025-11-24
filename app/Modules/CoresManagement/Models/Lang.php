<?php

namespace App\Modules\CoresManagement\Models;

use App\Modules\BaseModel;
use App\Modules\HasCustomPrimaryKey;

/**
 * @property string code
 * @property string label
 * @property string description
 */
class Lang extends BaseModel {

    use HasCustomPrimaryKey;

    protected $table = "cores_mgt__langs";

    public function getObjectName(): string
    {
        return $this->label;
    }
}
