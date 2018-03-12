<?php
namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
class OrderBookmaker extends Migration
{
    public function up()
    {
        if (Schema::hasColumns('bolo_orders', [
            'bookmaker_id'
        ])) {
            return;
        }

        Schema::table('bolo_orders', function($table)
        {
            $table->bigInteger('bookmaker_id')->nullable();
        });
    }

    public function down()
    {
        if (!Schema::hasColumns('bolo_orders', [
            'bookmaker_id'
        ])) {
            return;
        }

        Schema::table('bolo_orders', function($table)
        {
            $table->dropColumn('bookmaker_id');
        });
    }
}