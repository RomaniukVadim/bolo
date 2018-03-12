<?php
namespace Bolo\Bolo\Updates;
use Schema;
use October\Rain\Database\Updates\Migration;

class DropLogTime extends Migration
{
    public function up()
    {
        if (!Schema::hasColumns('admin_transactions', [
            'log_time'
        ])) {
            return;
        }

        Schema::table('admin_transactions', function($table)
        {
            $table->dropColumn('log_time');
        });
    }

    public function down()
    {
    }
}