<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\SalesManagement\Models\Invoice;
use App\Modules\SalesManagement\Models\Order;
use App\Modules\SalesManagement\Constants\BillingInformations;
use App\Modules\SalesManagement\Constants\InvoiceStatuses;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create((new Invoice)->getTable(), function (Blueprint $table) {
           $table->uuid('id')->primary();

            $table->string('code',60)
                ->unique(uniqid("UQ_"));

            $table->json('discounts')->nullable();

            $table->text('note')->nullable();

            $table->enum('billing_entity_type',BillingInformations::values())->default(BillingInformations::TYPE_INDIVIDUAL->value);
            $table->string('billing_company_name',60)->nullable();
            $table->string('billing_firstname',60)->nullable();
            $table->string('billing_lastname',60)->nullable();
            $table->string('billing_country',60)->nullable();
            $table->string('billing_state',60)->nullable();
            $table->string('billing_city',60)->nullable();
            $table->string('billing_zipcode',100)->nullable();
            $table->string('billing_address',100)->nullable();
            $table->string('billing_email',100)->nullable();

            $table->nullableUuidMorphs('recipient');

            $table->string("status",50)->default(InvoiceStatuses::DRAFT->value)
                  ->comment("Le statut");

            $table->timestamp('expired_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->foreignIdFor(Order::class,'order_id')->nullable()
                ->constrained((new Order)->getTable(), (new Order)->getKeyName(), uniqid("FK_"))
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment("[FK] la commande");

            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists((new Invoice)->getTable());
    }
};
