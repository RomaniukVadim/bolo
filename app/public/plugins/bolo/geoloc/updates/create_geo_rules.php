<?php namespace Bolo\Geoloc\Updates;

use Illuminate\Database\Schema\Blueprint;
use Schema;
use October\Rain\Database\Updates\Migration;

class CreateGeoRules extends Migration
{

    public function up()
    {
        Schema::create('geo_rules', function(Blueprint $table){
            $table->increments('id');
            $table->string('name')->default('');
            $table->integer('priority')->default(100)->index();
            $table->boolean('active')->default(0);
            $table->boolean('blocked', 1)->default(0);
            $table->longText('general');
            $table->timestamps();
        });

        \DB::table('geo_rules')->insert([
            'name' => 'Everywhere',
            'priority' => 1,
            'active' => 1,
            'general' => '',
        ]);

        Schema::create('geo_rule_items', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('rule_id')->index();
            $table->string('region_id', 8)->index();
            $table->boolean('exclude');
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('geo_rule_items');
        Schema::dropIfExists('geo_rules');
    }

}


