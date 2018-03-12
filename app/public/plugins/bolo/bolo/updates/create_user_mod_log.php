<?php namespace Bolo\Bolo\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class CreateUserModLog extends Migration
{

    public function up()
    {
        Schema::create('user_mod_log', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->integer('admin_id')->index()->nullable();
            $table->bigInteger('action_id')->index()->nullable();
            $table->string('field', 127);
            $table->string('old_value', 255);
            $table->string('new_value', 255);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_mod_log');
    }

}

