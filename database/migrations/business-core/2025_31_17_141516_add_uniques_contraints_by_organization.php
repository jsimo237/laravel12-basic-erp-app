<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\OrganizationManagement\Models\Setting;
use App\Modules\OrganizationManagement\Models\Staff;
use App\Modules\SalesManagement\Models\Customer;
use App\Modules\SalesManagement\Models\Invoice;
use App\Modules\SalesManagement\Models\Order;
use App\Modules\SalesManagement\Models\Product;
use App\Modules\SecurityManagement\Models\User;

return new class extends Migration {

    public function up(){

        $classes = [
           Customer::class => [
                "columns" => ['email',"phone","username"]
            ],
            Staff::class => [
                "columns" => ['email',"phone","username"]
            ],

            Product::class => [
                "columns" => ['sku']
            ],

            Order::class => [
                "columns" => ['code']
            ],
            Invoice::class => [
                "columns" => ['code']
            ],

           Setting::class => [
                "columns" => ['key']
            ],

            User::class => [
                "columns" => ['email',"phone","username"]
            ],
        ];

        foreach ($classes as $class => $options) {
            $model = (new $class);

            if ($columns = $options['columns'] ?? []){
                $columns[] = "organization_id";

                if(Schema::hasTable($model->getTable())){
                    Schema::table($model->getTable(),function (Blueprint $table) use ($columns) {
                        $table->unique($columns,uniqid("UQ_"));
                    });
                }
            }
        }
    }

};
