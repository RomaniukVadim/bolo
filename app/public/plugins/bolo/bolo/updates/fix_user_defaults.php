<?php namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class FixUserDefaults extends Migration
{

    public function up()
    {
        \DB::statement("
                ALTER TABLE `users`
                CHANGE COLUMN `title` `title` VARCHAR(32) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `is_superuser`,
                CHANGE COLUMN `nationality` `nationality` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `title`,
                CHANGE COLUMN `dob` `dob` DATE NULL AFTER `nationality`,
                CHANGE COLUMN `addr` `addr` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `dob`,
                CHANGE COLUMN `city` `city` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `addr`,
                CHANGE COLUMN `zip` `zip` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `city`,
                CHANGE COLUMN `country` `country` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `zip`,
                CHANGE COLUMN `phone` `phone` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `country`,
                CHANGE COLUMN `mobile` `mobile` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `phone`,
                CHANGE COLUMN `email2` `email2` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `mobile`,
                CHANGE COLUMN `currency` `currency` VARCHAR(3) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `email2`,
                CHANGE COLUMN `how_hear` `how_hear` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `currency`,
                CHANGE COLUMN `sec_question` `sec_question` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `promo`,
                CHANGE COLUMN `sec_answer` `sec_answer` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `sec_question`,
                CHANGE COLUMN `sec_question2` `sec_question2` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `sec_answer`,
                CHANGE COLUMN `sec_answer2` `sec_answer2` VARCHAR(255) NOT NULL DEFAULT '' COLLATE 'utf8_unicode_ci' AFTER `sec_question2`;
        ");
    }

    public function down()
    {
    }

}


