<?php namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserKyc extends Migration
{

    public function up()
    {
        if (Schema::hasColumns('users', [
            'kyc'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->boolean('kyc')->default(0);
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('kyc');
        });
    }

}


