<?php
namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
class UpdateUsers extends Migration
{
    public function up()
    {
        if (Schema::hasColumns('users', [
            'balance'
        ])) {
            return;
        }
        Schema::table('users', function($table)
        {
            $table->float('balance')->default(0);
            \DB::statement('ALTER TABLE `users` MODIFY `chat_username` VARCHAR(255) NULL');
        });

        Schema::table('bolo_orders', function($table)
        {
            $table->float('balance')->default(0);
        });
    }

    public function down()
    {
        if (!Schema::hasColumns('users', [
            'balance'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->dropColumn('balance');
        });

        Schema::table('bolo_orders', function($table)
        {
            $table->dropColumn('balance');
        });
    }
}