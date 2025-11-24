<?php

use App\Modules\SalesManagement\Models\Payment;
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

        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "payer_type",function (Blueprint $table)  {
            /// Relation polymorphique vers le propriétaire du paiement (Member, Staff, etc.)
            $table->string('payer_type')->nullable()
                ->comment('Type du modèle propriétaire (ex: member, customer)');
        });
        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "payer_id",function (Blueprint $table)  {
            $table->uuid('payer_id')->nullable()
                ->comment('ID du modèle propriétaire');
            $table->index(['payer_type', 'payer_id'],uniqid("POLY_INDEX_"));
        });

        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "remaining_amount",function (Blueprint $table)  {
            $table->decimal('remaining_amount',20,4)
                ->default(0)
                ->comment("Montant restant disponible pour utilisation");
        });
        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "currency",function (Blueprint $table)  {
            $table->string('currency', 3)
                ->default('XAF')
                ->comment('Devise du paiement');

        });

        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "currency",function (Blueprint $table)  {
            $table->timestamp('expires_at')
                ->nullable()
                ->comment('Date d expiration du paiement si applicable');

        });
        Schema::table((new Payment)->getTable(), function (Blueprint $table) {
            // Index pour performances
            $table->index('status',uniqid("IDX_"));
            $table->index('source_reference',uniqid("IDX_"));
            $table->index('created_at',uniqid("IDX_"));
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
