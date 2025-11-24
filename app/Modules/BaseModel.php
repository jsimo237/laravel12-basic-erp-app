<?php

namespace App\Modules;

use Axn\EloquentAuthorable\AuthorableTrait;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Modules\CoresManagement\Models\Traits\Auditable;
use App\Modules\CoresManagement\Models\Traits\InteractWithCommonsScopeFilter;
use App\Modules\OrganizationManagement\Interfaces\BelongsToOrganization;
use App\Modules\OrganizationManagement\Models\Traits\HasOrganization;
use App\Modules\SecurityManagement\Models\User;
use App\Support\Contracts\EventNotifiableContract;


/**
 * @property string|int id
 * @property string|DateTime|null created_at
 * @property string|DateTime|null updated_at
 * @property string|DateTime|null deleted_at
 * @property int|string|null created_by
 * @property int|string|null updated_by
 * @property int|string|null deleted_by
 * @property User|null createdBy
 * @property User|null updatedBy
 * @property User|null deletedBy
 */
abstract class BaseModel extends Model
    implements EventNotifiableContract,
            BelongsToOrganization
{

    use HasFactory,
        SoftDeletes,
        Authorable,
        Auditable,
        HasOrganization,
        InteractWithCommonsScopeFilter;

    protected $guarded = [];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool $timestamps
     */
    public $timestamps = true;

}
