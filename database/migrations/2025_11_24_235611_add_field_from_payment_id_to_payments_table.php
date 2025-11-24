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
        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "transferred_from_payment_id",function (Blueprint $table)  {
            $table->foreignIdFor(Payment::class,"transferred_from_payment_id")->nullable()
                ->constrained((new Payment)->getTable(), (new Payment)->getKeyName(), uniqid("FK_"))
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment("[FK] le payement parent qui a été utilisé pour transféré généré");
        });

        Schema::whenTableDoesntHaveColumn((new Payment)->getTable(), "transferred_at",function (Blueprint $table)  {
            $table->timestamp("transferred_at")->nullable()
                ->comment("La date a laquelle le paiement a été transféré");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::whenTableHasColumn((new Payment)->getTable(), "transferred_from_payment_id",function (Blueprint $table)  {
            $table->dropColumn("transferred_from_payment_id");
        });
        Schema::whenTableHasColumn((new Payment)->getTable(), "transferred_at",function (Blueprint $table)  {
            $table->dropColumn("transferred_at");
        });
    }
};
