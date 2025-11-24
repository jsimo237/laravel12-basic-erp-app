<?php

namespace App\Modules\SalesManagement\Models;


use App\Modules\UsesUuidV6;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Modules\BaseModel;

/**
 * @property string|int $tax_id
 * @property string|int $tax_group_id
 * @property float $seq_number
 */
class TaxHasGroup  extends BaseModel
{
    use UsesUuidV6;
    protected $table = "sales_mgt__tax_has_groups";
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'seq_number',
        'tax_id',
        'tax_group_id',
    ];



    public function getObjectName(): string
    {
        return $this->name;
    }


}
