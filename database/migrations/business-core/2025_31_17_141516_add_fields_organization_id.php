<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\CoresManagement\Models\Media;
use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\OrganizationManagement\Models\Setting;
use App\Modules\OrganizationManagement\Models\Staff;
use App\Modules\OrganizationManagement\Models\ContactForm;
use App\Modules\SalesManagement\Models\Customer;
use App\Modules\SalesManagement\Models\Invoice;
use App\Modules\SalesManagement\Models\Order;
use App\Modules\SalesManagement\Models\Payment;
use App\Modules\SalesManagement\Models\Product;
use App\Modules\SalesManagement\Models\Tax;
use App\Modules\SalesManagement\Models\TaxGroup;
use App\Modules\SecurityManagement\Models\Role;
use App\Modules\SecurityManagement\Models\User;
use App\Modules\SubscriptionsManagement\Models\Package;
use App\Modules\SubscriptionsManagement\Models\Plan;
use App\Modules\SubscriptionsManagement\Models\Subscription;

return new class extends Migration {

    public function up(){

        $classes = [
            User::class, Role::class,
            Order::class,Invoice::class, Customer::class,
            Payment::class,Product::class,
            Setting::class, ContactForm::class, Staff::class,
            Tax::class, TaxGroup::class, Product::class,

            Media::class,
            Plan::class, Package::class, Subscription::class,
            config('activitylog.activity_model') ?? null,
        ];

        $column = "organization_id";

        foreach ($classes as $class) {

            if($class){

                $model = (new $class);

                if(Schema::hasTable($model->getTable())){
                    Schema::whenTableDoesntHaveColumn($model->getTable(), $column,function (Blueprint $table) use ($column) {

                        $table->foreignIdFor(Organization::class,$column)->nullable()
                            ->constrained((new Organization)->getTable(), (new Organization)->getKeyName(), uniqid("FK_"))
                            ->cascadeOnUpdate()->cascadeOnDelete()
                            ->comment("[FK] l'organisation");

                    });
                }
            }
        }
    }

};
