<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSerssionsAddThemesTopics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sessions',function(Blueprint $table){
            $table->unsignedInteger('topic_id')->nullable();
            $table->unsignedInteger('theme_id')->nullable();
            $table->foreign('topic_id')->references('id')->on('topics');
            $table->foreign('theme_id')->references('id')->on('themes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sessions',function(Blueprint $table){
            $table->dropForeign('sessions_topic_id_foreign');
            $table->dropForeign('sessions_theme_id_foreign');
            $table->dropColumn('topic_id');
            $table->dropColumn('theme_id');
        });
    }
}

