<?php
namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class MakeBookmakerNullable extends Migration
{
    public function up()
    {
        \DB::statement('ALTER TABLE `admin_transactions` MODIFY `bookmaker_id` INTEGER NULL');
    }

    public function down()
    {
    }
}