<?php
namespace Bolo\Bolo\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class CreateBookmakersTable extends Migration
{

    public function up()
    {
        Schema::create('bookmakers', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->text('name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bookmakers');
    }
}