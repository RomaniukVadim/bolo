<?php namespace Bolo\Bolo\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class CreateGeoRegions extends Migration
{

    public function up()
    {
        Schema::create('bolo_orders', function(Blueprint $table)
        {
            $table->bigInteger('id')->unique();
            $table->integer('user_id')->index();
            $table->enum('status', ['created', 'pending', 'canceled', 'confirmed', 'processed', 'refunded'])->index();
            $table->decimal('amount');
            $table->string('currency', 6);
            $table->longText('message');
            $table->string('gateway_name')->index();
            $table->longText('gateway_options')->nullable();
            $table->longText('gateway_result')->nullable();
            $table->string('gateway_transaction_id', 127)->unique()->nullable();
            $table->integer('processed_by_admin_id')->nullable();
            $table->timestamps();
            $table->timestamp('pending_at')->nullable()->index();
            $table->timestamp('canceled_at')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('refunded_at')->nullable()->index();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bolo_orders');
    }

}

