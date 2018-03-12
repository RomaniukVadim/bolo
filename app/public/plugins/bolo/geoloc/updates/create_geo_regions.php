<?php namespace Bolo\Geoloc\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGeoRegions extends Migration
{

    public function up()
    {
        Schema::create('geo_regions', function($table)
        {
            $table->string('id', 8)->unique();
            $table->string('name', 128);
            $table->string('full_name', 128);
            $table->string('iso', 4);
            $table->tinyInteger('level');
            $table->boolean('active')->default(0);
            $table->timestamps();
            $table->index(['iso', 'level'], 'iso_level');
        });
    }

    public function down()
    {
        Schema::dropIfExists('geo_regions');
    }

}


