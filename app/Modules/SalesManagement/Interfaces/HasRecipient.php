<?php

namespace App\Modules\SalesManagement\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property MorphTo recipient
 */
interface HasRecipient
{

    public function recipient() : MorphTo;
}