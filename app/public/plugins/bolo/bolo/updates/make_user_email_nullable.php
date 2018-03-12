<?php
namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class MakeUserEmailNullable extends Migration
{
    public function up()
    {
        \DB::statement("ALTER TABLE `users` CHANGE COLUMN `email` `email` VARCHAR(255) NULL COLLATE 'utf8_unicode_ci' AFTER `name`");
    }

    public function down()
    {

    }
}
