<?php

use App\Modules\SubscriptionsManagement\Constants\SubscriptionStatuses;
use App\Modules\SubscriptionsManagement\Models\Subscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void{
        Schema::create((new Subscription)->getTable(), function (Blueprint $table) {
           $table->uuid('id')->primary();

            $table->string("reference",100)
                ->unique(uniqid("UQ_"))
                ->comment("La reference unique de la souscription");

            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();

            $table->decimal('amount',20,4)->default(0);

            $table->boolean('active')->default(true)
               // ->storedAs('start_at <= CURRENT_TIMESTAMP AND end_at >= CURRENT_TIMESTAMP')
            ;

            $table->string("status",50)->default(SubscriptionStatuses::INITIATED->value)
                ->comment("Le statut");

            $table->timestamp('initiated_at')->nullable()
                ->comment("La date a la quelle ça été initée");

            $table->timestamp('completed_at')->nullable()
                ->comment("La date de confirmation");

            $table->nullableUuidMorphs('subscriber',uniqid("POLY_INDEX_"));
            $table->nullableUuidMorphs('item',uniqid("POLY_INDEX_"));

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists((new Subscription)->getTable());
    }
};
