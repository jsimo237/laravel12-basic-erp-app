<?php

namespace App\Modules\SecurityManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Authorable;
use App\Modules\CoresManagement\Models\Traits\Activable;
use App\Modules\CoresManagement\Models\Traits\Auditable;
use App\Modules\OrganizationManagement\Interfaces\BelongsToOrganization;
use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\OrganizationManagement\Models\Traits\HasOrganization;
use App\Modules\SecurityManagement\Models\Observers\RoleObserver;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int id
 * @property string name
 * @property string guard_name
 * @property string description
 * @property bool editable
 * @property Permission[] permissions
 * @property Organization oganization
 */
class Role extends SpatieRole implements BelongsToOrganization {

    use HasFactory,
        SoftDeletes,
        Authorable,
        Auditable,
        Activable,
        HasOrganization;

    protected $hidden = ['pivot'];
    // protected $dispatchesEvents = [];
    const TABLE_NAME = "security_mgt__roles";

    protected $table = self::TABLE_NAME;


    const SUPER_ADMIN = "Super-Admin";
    const MANAGER = "Main-Manager";
    const ADMIN_RESSELER = "Admin-Resseller";


    protected $casts = [
        'editable' => 'boolean',
    ];

    protected $appends = [
        "permissions_ids"
    ];

    public function getRouteKeyName(){
        return "id";
    }

    protected static function booted(){
      // static::addGlobalScope(new RoleGlobalScope);

      static::observe([RoleObserver::class]);
    }

    //GETTERS
    public function getPermissionsIdsAttribute(){
        return $this->permissions->pluck('id')->toArray();
    }
}
