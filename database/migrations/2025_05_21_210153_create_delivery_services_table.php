<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryServicesTable extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('logo_url')->nullable();
            $table->decimal('shipping_fee', 10, 2)->default(0.00);
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_services');
    }
}
