<?php

use App\Modules\OrganizationManagement\Models\Organization;
use App\Modules\SalesManagement\Constants\PaymentTransactionAction;
use App\Modules\SalesManagement\Models\PaymentTransaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create((new PaymentTransaction)->getTable(), function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->nullableUuidMorphs('payer',uniqid("POLY_INDEX_"));

            $table->nullableUuidMorphs('entity',uniqid("POLY_INDEX_"));

            $table->decimal('amount',14,4)
                    ->nullable()
                    ->comment("Le montant traité pour cette opération");

            $table->text('reason')->nullable();

            $table->enum('action', PaymentTransactionAction::cases())->nullable()
                    ->comment("Type d'action sur le solde");

            $table->decimal('payer_balance_before',14,4)->nullable()
                ->comment("Solde du payeur avant modification");

            $table->decimal('payer_balance_after',14,4)
                ->nullable()
                ->comment("Solde du payeur avant modification");

            $table->foreignIdFor(Organization::class,"organization_id")
                ->constrained((new Organization)->getTable(), (new Organization)->getKeyName(), uniqid("FK_"))
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment("[FK] l'organisation");

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists((new PaymentTransaction)->getTable());
    }
};
