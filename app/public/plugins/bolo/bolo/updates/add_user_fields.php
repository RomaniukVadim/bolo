<?php namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddUserFields extends Migration
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
            $table->string('title', 32)->default('Mr.');
            $table->string('nationality', 64)->default('None');
            $table->date('dob')->default('1900-01-01');
            $table->string('addr', 255)->default('None');
            $table->string('city', 255)->default('None');
            $table->string('zip', 64)->default('None');
            $table->string('country', 255)->default('NA');
            $table->string('phone', 64)->default('None');
            $table->string('mobile', 64)->default('None');
            $table->string('email2', 255)->default('None');
            $table->string('skype', 255)->default('None');
            $table->string('wechat', 255)->default('None');
            $table->string('qq', 255)->default('None');
            $table->string('currency', 3)->default('NA');
            $table->string('how_hear', 255)->default('None');
            $table->boolean('tos')->default(0);
            $table->string('promo', 255)->default('');
            $table->string('sec_question', 255)->default('None');
            $table->string('sec_answer', 255)->default('None');
            $table->string('sec_question2', 255)->default('None');
            $table->string('sec_answer2', 255)->default('None');
            $table->string('lang', 8)->default('en');
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('nationality');
            $table->dropColumn('dob');
            $table->dropColumn('addr');
            $table->dropColumn('city');
            $table->dropColumn('zip');
            $table->dropColumn('country');
            $table->dropColumn('phone');
            $table->dropColumn('mobile');
            $table->dropColumn('email2');
            $table->dropColumn('skype');
            $table->dropColumn('wechat');
            $table->dropColumn('qq');
            $table->dropColumn('currency');
            $table->dropColumn('how_hear');
            $table->dropColumn('tos');
            $table->dropColumn('promo');
            $table->dropColumn('sec_question');
            $table->dropColumn('sec_answer');
            $table->dropColumn('sec_question2');
            $table->dropColumn('sec_answer2');
            $table->dropColumn('lang');
        });
    }

}


