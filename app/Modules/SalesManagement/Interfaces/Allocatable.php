<?php

namespace App\Modules\SalesManagement\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Allocatable
{

    public function paymentsAllocations() : MorphMany;
}
