<?php

use App\Modules\LocalizationManagement\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\SalesManagement\Models\TaxGroup;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create((new TaxGroup)->getTable(), function (Blueprint $table) {
           $table->uuid('id')->primary();

            $table->string('name')->nullable(false);

            $table->text('description')->nullable();

            $table->foreignIdFor(Country::class,'country_code')
                ->constrained((new Country)->getTable(), (new Country)->getKeyName(), uniqid("FK_"))
                ->cascadeOnUpdate()->cascadeOnDelete()
                ->comment("[FK] le pays");

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists((new TaxGroup)->getTable());
    }
};
