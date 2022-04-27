<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_limits', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->default(0);
            $table->bigInteger('product_variant_id')->default(0);
            $table->bigInteger('customer_group_id')->default(0);
            $table->bigInteger('customer_id')->default(0);
            $table->unsignedSmallInteger('period')->nullable();
            $table->unsignedSmallInteger('max_quantity')->nullable();
            $table->unsignedInteger('max_total')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_id', 'product_variant_id', 'customer_id', 'customer_group_id'], 'uq_ref_ids');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_limits');
    }
};
