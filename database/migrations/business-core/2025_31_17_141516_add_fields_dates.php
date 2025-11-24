<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\CoresManagement\Models\Media;
use App\Modules\LocalizationManagement\Models\Address;
use App\Modules\LocalizationManagement\Models\City;
use App\Modules\LocalizationManagement\Models\Country;
use App\Modules\LocalizationManagement\Models\Quarter;
use App\Modules\LocalizationManagement\Models\State;
use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\OrganizationManagement\Models\Staff;
use App\Modules\SalesManagement\Models\Customer;
use App\Modules\SalesManagement\Models\Invoice;
use App\Modules\SalesManagement\Models\InvoiceItem;
use App\Modules\SalesManagement\Models\Order;
use App\Modules\SalesManagement\Models\OrderItem;
use App\Modules\SalesManagement\Models\Product;
use App\Modules\SecurityManagement\Models\Permission;
use App\Modules\SecurityManagement\Models\Role;
use App\Modules\SecurityManagement\Models\User;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){

        $classes = config("business-core.models_has_authors");

        if ($classes){
            foreach ($classes as $class) {

                if (class_exists($class)){
                    $model = (new $class);
                    $tableName = $model->getTable();

                    if(Schema::hasTable($tableName)){
                        Schema::whenTableDoesntHaveColumn($tableName, "created_at",function (Blueprint $table)  {
                            $table->timestamp('created_at')->nullable();
                        });
                        Schema::whenTableDoesntHaveColumn($tableName, "updated_at",function (Blueprint $table)  {
                            $table->timestamp('updated_at')->nullable();
                        });

                        Schema::whenTableDoesntHaveColumn($tableName, "deleted_at",function (Blueprint $table)  {
                            $table->timestamp('deleted_at')->nullable();
                        });

                    }
                }
            }
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(){
        //
    }
};
