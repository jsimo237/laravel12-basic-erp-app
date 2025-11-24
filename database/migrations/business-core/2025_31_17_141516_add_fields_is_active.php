<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\OrganizationManagement\Models\Setting;
use App\Modules\SalesManagement\Models\Product;
use App\Modules\SecurityManagement\Models\User;

return new class extends Migration {

    public function up(){

        $classes = [
            Organization::class,User::class, Product::class,
            Setting::class
        ];

        $column = "is_active";
        foreach ($classes as $class) {
            $model = (new $class);

            if(Schema::hasTable($model->getTable())){
                Schema::whenTableDoesntHaveColumn($model->getTable(), $column,function (Blueprint $table) use ($column) {
                    $table->boolean($column)->default(true)
                        ->comment("Détermine si la ligne est active(visible par le front-ent)");
                });
            }


        }
    }

};
