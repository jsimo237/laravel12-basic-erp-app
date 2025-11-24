<?php

namespace App\Modules\SalesManagement\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Modules\SalesManagement\Models\TaxGroup;

trait HasTaxGroup
{

    public function taxGroups(): MorphToMany
    {
        return $this->morphToMany(
            TaxGroup::class,
            'model',
            'model_tax_groups',
            'model_id',
            'tax_group_id'
        );
    }
}