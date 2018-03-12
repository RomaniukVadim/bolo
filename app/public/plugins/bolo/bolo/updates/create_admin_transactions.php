<?php namespace Bolo\Bolo\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use October\Rain\Support\Facades\Schema;

class CreateAdminTransactions extends Migration
{

    public function up()
    {
        Schema::create('admin_transactions', function(Blueprint $table)
        {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->integer('admin_id')->index();
            $table->string('type')->default('deposit');
            $table->string('currency');
            $table->float('balance');
            $table->float('amount');
            $table->integer('bookmaker_id');
            $table->longText('description');
            $table->timestamp('log_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_transactions');
    }

}