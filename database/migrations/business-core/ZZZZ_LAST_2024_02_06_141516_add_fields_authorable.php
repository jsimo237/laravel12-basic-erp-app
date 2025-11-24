<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
use App\Modules\SalesManagement\Models\Tax;
use App\Modules\SalesManagement\Models\TaxGroup;
use App\Modules\SecurityManagement\Models\Role;
use App\Modules\SecurityManagement\Models\User;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){

        //class pour slug-column
        $classes = config("business-core.models_has_authors");

        $authorableOptions = config("eloquent-authorable");
        $createdByColumnName = $authorableOptions['created_by_column_name'] ?? "created_by";
        $updatedByColumnName = $authorableOptions['updated_by_column_name'] ?? "updated_by";

        foreach ($classes as $class) {
            $model = (new $class);
            $authorable = $model->authorable;
            //$createdByColumnName = $authorable['created_by_column_name'] ?? $createdByColumnName;
            $setUpdatedByColumnName = $authorable['created_by_column_name'] ?? true;
            $setCreatedByColumn = $authorable['updated_by_column_name'] ?? true;

            if ($setCreatedByColumn){
                Schema::whenTableDoesntHaveColumn($model->getTable(), $createdByColumnName,function (Blueprint $table) use ($createdByColumnName) {
//                $table->addAuthorableColumns(true, User::class)
//                    $table->unsignedBigInteger($createdByColumnName)->nullable()
//                        ->comment("[FK] l'auteur de l'enrengistrement");

                    $table->foreignIdFor(User::class,$createdByColumnName)->nullable()
                            ->constrained((new User)->getTable(), (new User)->getKeyName(), uniqid("FK_"))
                            ->cascadeOnUpdate()->cascadeOnDelete()
                            ->comment("[FK] l'auteur de l'enrengistrement");
                });
            }
            if ($setUpdatedByColumnName){
                Schema::whenTableDoesntHaveColumn($model->getTable(), $updatedByColumnName,function (Blueprint $table) use ($updatedByColumnName) {
//                    $table->unsignedBigInteger($updatedByColumnName)->nullable()
//                        ->comment("[FK] l'auteur de la dernière modification");

                    $table->foreignIdFor(User::class,$updatedByColumnName)->nullable()
                        ->constrained((new User)->getTable(), (new User)->getKeyName(), uniqid("FK_"))
                        ->cascadeOnUpdate()->cascadeOnDelete()
                        ->comment("[FK] l'auteur de la dernière modification");
                });
            }

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
