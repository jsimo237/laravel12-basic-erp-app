<?php

namespace App\Modules\CoresManagement\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use App\Modules\CoresManagement\Repositories\MediaDeletionRepository;

trait FlushTempMediasDeletions
{


    public static function bootFlushTempMediasDeletions(){

        static::saved(function (Model $model){
            (new MediaDeletionRepository)->processToDeleteMedias($model);
        });
    }
}