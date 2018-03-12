<?php
namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
class UpdateOrders extends Migration
{
    public function up()
    {
        if (Schema::hasColumns('bolo_orders', [
            'type'
        ])) {
            return;
        }

        Schema::table('bolo_orders', function($table)
        {
            $table->string('type')->default('deposit');
            $table->bigInteger('admin_transaction')->nullable();
            \DB::statement('ALTER TABLE `bolo_orders` MODIFY `gateway_name` VARCHAR(255) NULL');
        });
    }

    public function down()
    {
        if (!Schema::hasColumns('bolo_orders', [
            'type'
        ])) {
            return;
        }

        Schema::table('bolo_orders', function($table)
        {
            $table->dropColumn('type');
            $table->dropColumn('admin_transaction');
        });
    }
}