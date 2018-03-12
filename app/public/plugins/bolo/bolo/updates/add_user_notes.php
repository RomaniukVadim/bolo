<?php namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserNotes extends Migration
{

    public function up()
    {
        if (Schema::hasColumns('users', [
            'notes'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->string('notes', 1023)->nullable();
        });
    }

    public function down()
    {
        if (!Schema::hasColumns('users', [
            'notes'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->dropColumn('notes');
        });
    }

}


