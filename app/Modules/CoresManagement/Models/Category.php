<?php

namespace App\Modules\CoresManagement\Models;

use App\Modules\BaseModel;
use App\Modules\UserManagement\Traits\Auditable;
use App\Support\Models\Bootables\Sluglable;

class Category extends BaseModel {

    protected $guarded = ['id'];
    protected $table = "cores_mgt__categories";

    const FK_iD = "category_id";
    const MORPH_ID_COLUMN = "model_id";
    const MORPH_TYPE_COLUMN = "model_type";
    const SLUG_ATTRIBUTS = ["id",'name'];



    //Functions
//    public function getSlugOptions() : SlugOptions{
//        return SlugOptions::create()
//              ->generateSlugsFrom(['name','id'])
//              ->saveSlugsTo('slug')
//           // ->usingSeparator('_')
//        ;
//    }
    public function getObjectName(): string
    {
        // TODO: Implement getObjectName() method.
    }
}
