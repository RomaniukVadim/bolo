<?php namespace Bolo\Bolo\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class ModifyUserChatFields extends Migration
{

    public function up()
    {
        if (Schema::hasColumns('users', [
            'chat_username'
        ])) {
            return;
        }

        Schema::table('users', function($table)
        {
            $table->dropColumn('skype');
            $table->dropColumn('wechat');
            $table->dropColumn('qq');
            $table->string('chat_username', 255)->default('');
            $table->string('chat_type', 10)->default('');
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->string('skype', 255)->default('None');
            $table->string('wechat', 255)->default('None');
            $table->string('qq', 255)->default('None');
            $table->dropColumn('chat_username');
            $table->dropColumn('chat_type');
        });
    }

}


