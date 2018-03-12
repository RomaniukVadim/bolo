<?php namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddBackUserFields extends Migration
{

    public function up()
    {
        if (Schema::hasColumns('users', [
            'title'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->string('title', 32)->default('');
            $table->string('addr', 255)->default('');
            $table->string('city', 255)->default('');
            $table->string('zip', 64)->default('');
            $table->string('phone', 64)->default('');
            $table->string('email2', 255)->default('');
            $table->string('sec_question2', 255)->default('');
            $table->string('sec_answer2', 255)->default('');
        });
    }

    public function down()
    {
        if (!Schema::hasColumns('users', [
            'title'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('addr');
            $table->dropColumn('city');
            $table->dropColumn('zip');
            $table->dropColumn('phone');
            $table->dropColumn('email2');
            $table->dropColumn('sec_question2');
            $table->dropColumn('sec_answer2');

        });
    }

}


